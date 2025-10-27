<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Http\Resources\Admin\UserResource;
use App\MailTemplate;
use App\Setting;
use App\User;
use App\Panels;
use App\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Exception;
use Hash;
use Validator;
use Carbon\Carbon;
use App\Panellnventory;

class AuthController extends Controller
{
    //
    // Login
    public function login(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'phone' => 'required',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
            $user = User::where('phone', $request->phone)->first();
            if($user){
                if($user->status) {
                    if(Auth::attempt(['phone' => request('phone'), 'password' => request('password')])) {
                        $user = Auth::user();
                        if(isset($request->fcm_token)) {
                            $user->fcm_token = $request->fcm_token;
                            $user->save();
                        }
                        $user->last_login = Carbon::now()->format('Y-m-d H:i:s');
                        $user->save();
                        $token = auth()->user()->createToken('API Token')->accessToken;
                        $this->setAuthResponse($user);
                        return ResponseBuilder::successWithToken($token, $user->pin, $this->response, 'Login Successfully');                            
                    }
                    else{
                        return ResponseBuilder::error( __("Your Phone or Password does not match."), $this->badRequest);
                    }
                } else {
                    return ResponseBuilder::successMessage( __("Account Not Found With This Phone Number!"), $this->badRequest);
                }
            }else{
                return ResponseBuilder::error( __("Account Not Found With This Phone Number!"), $this->badRequest);
            }
        } catch (Exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    // register
    public function register(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone'     => 'required|numeric|min:10',
                'email'     => 'email',
                'pin'     => 'required',
                'password' => 'required|min:8',
            ]);
            if ($validator->fails()) {   
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
            }  

            $otp = $this->generateOtp();
            $user = User::where('phone', $request->phone)->first();
            $welcomeBonus = Setting::where('key', 'welcome_bonus')->first();

            if(isset($request->refer_code)) {
                // 
                $panelUser = User::where('panel_code', strtoupper($request->refer_code))->first();
                if(!isset($panelUser->id)){
                    return ResponseBuilder::error(__("Refer Code Invalid!"), $this->badRequest);
                }
            }
            $panelComm = Panels::where('id', '2')->first();
            if($user) {
              
                if($user->status) {
                    return ResponseBuilder::error(__("User Already Exist with this Phone Number"), $this->badRequest);
                } else{
                    // 
                    $user = User::where('phone', $request->phone)->update([
                        'name'              => $request->name,
                        'phone'             => $request->phone,
                        'password'          => Hash::make($request->password),
                        'email'             => $request->email ??'',
                        'pin'               => $request->pin,
                        'status'            => '0',
                        'transfer_status'   => '1',
                        'betting_status'    => '1',
                        'allow_entry'       => '0',
                        'wallet'            => !empty($welcomeBonus)?$welcomeBonus->value:0,
                        'otp'               => $otp,
                        'otp'               => $otp,
                        'panel_id'          => 2,
                        'panel_com'          => $panelComm->commissions_rate,
                        'panel_code'          => $this->genPanelCode(),
                        'refer_code'        => isset($request->refer_code)?strtoupper($request->refer_code):null,
                    ]);
                }
            } else {
                $user = User::create([
                    'name'              => $request->name,
                    'phone'             => $request->phone,
                    'password'          => Hash::make($request->password),
                    'email'             => $request->email ??'',
                    'pin'               => $request->pin,
                    'status'            => '0',
                    'betting_status'    => '1',
                    'allow_entry'       => '0',
                    'otp'               => $otp,
                    'wallet'            => !empty($welcomeBonus)?$welcomeBonus->value:0,
                    'betting_status'    => '1',
                    'transfer_status'   => '1',
                    'refer_code'        => isset($request->refer_code)?strtoupper($request->refer_code):null,
                    'panel_id'          => 2 ,
                    'panel_com'          => $panelComm->commissions_rate,
                    'panel_code'          => $this->genPanelCode(),
                ]);

                if(!empty($user)) {
                    $user->roles()->sync(2);
                    $user->allow_entry = '1';
                    $user->save();
                    if(!empty($welcomeBonus) && $welcomeBonus->value > 0) {
                        $WalletTransaction = new WalletTransaction(); 
                        $WalletTransaction->user_id = $user->id;
                        $WalletTransaction->transaction_amount = $welcomeBonus->value;
                        $WalletTransaction->transaction_type = 'AddAmount';
                        $WalletTransaction->transaction_update_date = $this->CurrentDate;
                        $WalletTransaction->save();
            
                        $WalletTransaction->transaction_id = 'ORDAM'.rand('100','999').$WalletTransaction->id;
                        $WalletTransaction->save();

                    }
                }
                
                if(isset($request->refer_code) && isset($panelUser->id)) {
                    // 
                    $panelUser = User::where('panel_code', strtoupper($request->refer_code))->first();
                    $Panellnventory = new Panellnventory();
                    $Panellnventory->user_id = $user->id;
                    $Panellnventory->panel_code = strtoupper($request->refer_code);
                    $Panellnventory->panel_id = $panelUser->panel_id; 
                    $Panellnventory->save();
                }
            }

            // Assign OTP
            $this->response->otp = $otp;

            $sendMail = true;
            if($sendMail) {
                // 
                // mail to admin
                $mailData = MailTemplate::where('category', 'user-register')->first();
                $basicInfo = [
                    '{user_name}' => $request->name,
                    '{siteName}' => '',
                ];

                try {
                    //code...
                    $siteSettings = Setting::where('key', 'email')->first();
                    $email = ($siteSettings)?$siteSettings->value:'tapang786@gmail.com';
                    $mailStatus = $this->SendMail($email, $mailData, $basicInfo);
                    // if(!$mailStatus['status']) {
                    //     return ResponseBuilder::error(__($mailStatus['message']), $this->serverError);
                    // }
                } catch (\Throwable $th) {
                    //throw $th;
                }
                
            }
            $this->SendOtp($request->phone, $otp);
            return ResponseBuilder::success($this->response, 'Registered Successfully! Please verify your OTP');
            
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    public function verifyOtp(Request $request)
    {   
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|exists:users',
                'otp' => 'required|digits:4'
            ]);
           
            if ($validator->fails()) {   
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }   

            $user = User::where('phone', $request->phone)->first();

            if ($user->otp != $request->otp) {
                return ResponseBuilder::error(__('Invalid OTP'), $this->badRequest);
            }
            // if(strtotime($user->otp_created_at) < strtotime(now())) 
            // {
            //     return ResponseBuilder::error(__('Your OTP is Expired , Please Resend OTP'), $this->badRequest);    
            // }
            if($user->otp == $request->otp){
                $user->otp = null;
                $user->status = 1;
                $user->save();

                // login user
                $token = $user->createToken('Token')->accessToken;
                $this->setAuthResponse($user);
                
                return ResponseBuilder::successMessage('Your Account Successfully Verified',  $this->success);
                // return ResponseBuilder::successWithToken($token, $this->response, "Your Account Successfully Verified");
               
            }
        }catch (\Exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }

    }

    public function ChangePassword(Request $request)
    {
        # code...
        try {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'password' => 'required|different:old_password|min:6|confirmed',
                'password_confirmation' => 'required|min:6'
            ], [
                'password.different' => 'New Password and Old Password must be Different.',
                'password.confirmed' => 'Confirm Password not matched!'
            ]);
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }

            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }

            if (Hash::check($request->old_password, $user->password)) { 
                $user->update([
                    'password'  => Hash::make($request->password)
                ]);

                return ResponseBuilder::successMessage('Password Changed Successfully!',  $this->success);

            } else {
                return ResponseBuilder::error(__("Old Password Not Match!"), $this->badRequest);
            }

        } catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    public function changeForgetPassword(Request $request)
    {
        # code...
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:6|confirmed',
                'password_confirmation' => 'required|min:6'
            ]);
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
            $parameters = $request->all();
            extract($parameters);

            $user = User::where('phone', $phone)->first();
            if(!$user) {
                return ResponseBuilder::error(__("Error: Account not found!"), $this->badRequest);
            } 
            $user->update([
                'password'  => Hash::make($password)
            ]);

            return ResponseBuilder::successMessage('Password Changed Successfully!',  $this->success);

        } catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    public function ResendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|exists:users',
            ]);

            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
            $parameters = $request->all();
            extract($parameters);

            $user = User::where('phone', $phone)->first();
            $otp = $this->generateOtp();

            if($user){
                $user->update([
                    'otp' => $otp,
                    'deleted_at' => null
                ]);
            }
            
            $this->response->phone = $phone;
            $this->response->otp = $otp;

            $this->SendOtp($phone, $otp);

            return ResponseBuilder::success($this->response, 'OTP Sent Successfully. OTP is '.$otp);
        } catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    public function setAuthResponse($user)
    {
        $this->response->user = new UserResource($user);
    }
    
}
