<?php

namespace App\Http\Controllers\Api\V1;

use App\Business;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Http\Resources\Admin\UserResource;
use App\Http\Resources\ProjectTaskCollection;
use App\Http\Resources\ProjectTaskResource;
use App\Http\Resources\ProjectCollection;
use App\Http\Resources\ProjectResource;
use App\MailTemplate;
use App\User;
use App\Company;
use App\Employees;
use App\Tournaments;
use App\ProjectTask;
use App\Project;
use Illuminate\Support\Facades\Auth;
use Exception;
use Hash;
use Validator;
use DateTime;
use DateInterval;
use DatePeriod;
use Carbon\Carbon;
use App\Mail\SendMail;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
            $user = User::where('email', $request->email)->first();
            if($user){
                    if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
                        $user = Auth::user();
                        $user->load('roles');
                        $token = auth()->user()->createToken('API Token')->accessToken;
                        $this->setAuthResponse($user);
                        return ResponseBuilder::successWithToken($token, $this->response, 'Login Successfully');                            
                    }
                    else{
                        return ResponseBuilder::error( __("Password does not match."), $this->badRequest);
                    }
            }else{
                return ResponseBuilder::error( __("User not registered"), $this->badRequest);
            }
        } catch (Exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    // register
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'      => 'required',
                'email'     => 'required|email',
                'password'  => 'required|min:8',
                'phone'      => 'required',                                                                                                                                                                                                                                                                              
            ]);
            if ($validator->fails()) {   
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
            }

            $parameters = $request->all();
            extract($parameters);

            $otp = $this->generateOtp(4);
            $user = User::where('email', $email)->first();
            $userPhone = User::where('phone', $phone)->first();
            if($userPhone){
                return ResponseBuilder::error(__("User Already Exist with this Phone Number."), $this->badRequest);
            }

            if($user) {
                if($user->status) {
                    return ResponseBuilder::error(__("User Already Exist with this Email ID."), $this->badRequest);
                } else{
                    // 
                    $user = User::where('email', $email)->update([
                        'name'      => $name,
                        'password'  => Hash::make($password),
                        'phone' => $phone,
                        'address' => $address ?? '', 
                    ]);
                    $user->roles()->sync(2);
                }
            } else {
                $user = User::create([
                    'email'     => $email,
                    'name'      => $name,
                    'password'  => Hash::make($password),
                    'phone' => $phone,
                    'address' => $address ?? '',
                ]);
            }
            return ResponseBuilder::success($this->response, 'Registered Successfully!');
            
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }


   //Company Register Api 

    public function CompanyRegister(Request $request)
    {
        
            $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'alias_name' => 'required',
            'slogan' => 'required',
            'password' => 'required|min:8',
         
        ]);
        $exxitUser = User::where('email',$request->email)->first();
       
        if(!empty($exxitUser) && $exxitUser->company_email_verifed == true){
            return ResponseBuilder::error('The Email has been already taken',  $this->badRequest);
        }
      
        if ($validator->fails()) {   
            return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
        } 
       
        try{
           
            $path = 'uploads/company';
            $oldLogo = '';
        
            if ($request->hasFile('logo')) {
            
                $oldLogo = $company->logo ?? null;
        
                $request->validate([
                    'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);
        
                $logo = $this->UpdateImage($request->file('logo'), $path);
            } else {
                $logo = $request->old_logo;
            }
            $otp = rand(1000,9999);
            $userData = User::updateOrCreate([
                'email'=> $request->email,
            ],[
                
                'is_company'=> true,
                'otp'=> $otp,
                'password'  => Hash::make($request->password)
            ]);

            $data = [
                'company_name' => $request->company_name,
                'logo' => $logo,
                'alias_name' => $request->alias_name,
                'slogan' => $request->slogan,
                'full_address' => $request->full_address,
                'country' => $request->country,
                'telephone_number' => $request->telephone_number,
                'website' => $request->website,
                'subscription_level' => $request->subscription_level,
                'date_of_registration' => $request->date_of_registration,
                'contact_person_for_subscription' => $request->contact_person_for_subscription,
                'contact_email_for_subscription' => $request->contact_email_for_subscription,
                'contact_number_for_subscription' => $request->contact_number_for_subscription,
                'no_of_employee' => $request->no_of_employee,
                'industry' => $request->industry,
                'vat_number' => $request->vat_number,
                'tax_number' => $request->tax_number,
                'registration_number' => $request->registration_number,
                'b_bbee_level' => $request->b_bbee_level,
            ];
        
            $company = Company::updateOrCreate(['user_id' => $userData->id], $data);
            $mailTemplate = MailTemplate::where('category','verify-company-email')->first();
        
            if($mailTemplate){
                $array1 =  ['{otp}'];
                $array2 = [$otp];  
                $mailTemplate->message = str_replace($array1, $array2, $mailTemplate->message);
                \Mail::to($request->email)->send(new SendMail($mailTemplate));
            }
            return ResponseBuilder::successMessage('Verification code sent to your email. Please verify',  $this->success);
    }catch (\Exception $e) {
        return $e;
        return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
    }
}
    public function CompanyEmailVerify(Request $request)
    {
        
            $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'alias_name' => 'required',
            'slogan' => 'required',
            'email' => 'required',
            'otp' => 'required|Integer|digits:4',
        ]);
       
        if ($validator->fails()) {   
            return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
        } 
       
        try{
            $exitUser = User::where('email',$request->email)->first();
            if(!$exitUser){
                return ResponseBuilder::error('Invalid email address',  $this->badRequest);
            }
            if($exitUser->company_email_verifed==true){
                return ResponseBuilder::error('Email already verified',  $this->badRequest);
            }
            if($exitUser->otp != $request->otp){
                return ResponseBuilder::error('OTP does not match',  $this->badRequest);
            }

            $path = 'uploads/company';
            $oldLogo = '';
        
            if ($request->hasFile('logo')) {
            
                $oldLogo = $company->logo ?? null;
        
                $request->validate([
                    'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);
        
                $logo = $this->UpdateImage($request->file('logo'), $path);
            } else {
                $logo = $request->old_logo;
            }

            $userData = User::updateOrCreate([
                'email'=> $request->email,
            ],[
                'is_company'=> true,
                'company_email_verifed'=> true,
                'otp'=> null,
            ]);

            $data = [
                'company_name' => $request->company_name,
                'logo' => $logo,
                'alias_name' => $request->alias_name,
                'slogan' => $request->slogan,
                'full_address' => $request->full_address,
                'country' => $request->country,
                'telephone_number' => $request->telephone_number,
                'website' => $request->website,
                'subscription_level' => $request->subscription_level,
                'date_of_registration' => $request->date_of_registration,
                'contact_person_for_subscription' => $request->contact_person_for_subscription,
                'contact_email_for_subscription' => $request->contact_email_for_subscription,
                'contact_number_for_subscription' => $request->contact_number_for_subscription,
                'no_of_employee' => $request->no_of_employee,
                'industry' => $request->industry,
                'vat_number' => $request->vat_number,
                'tax_number' => $request->tax_number,
                'registration_number' => $request->registration_number,
                'b_bbee_level' => $request->b_bbee_level,
            ];
            $company = Company::updateOrCreate(['user_id' => $userData->id], $data);
            return ResponseBuilder::successMessage('Verification success',  $this->success);
        }catch (\Exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
}
    
public function ResendOtp(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
        }
        $parameters = $request->all();
        extract($parameters);

        $otp = rand(1000,9999);
        $user = User::where('email', $email)->first();

        if($user){
            $user->update([
                'otp' => $otp,
                'deleted_at' => null
            ]);
        }

        $mailTemplate = MailTemplate::where('category','verify-company-email')->first();
        
        if($mailTemplate){
            $array1 =  ['{otp}'];
            $array2 = [$otp];  
            $mailTemplate->message = str_replace($array1, $array2, $mailTemplate->message);
            \Mail::to($request->email)->send(new SendMail($mailTemplate));
        }
       
        return ResponseBuilder::success($this->response,'OTP Resend Successfully');
    } catch (exception $e) {
        return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
    }
}

              //Employee Register Api

        Public function EmployeeRegister(Request $request)

        {
            $validator = Validator::make($request->all(), [
            'company_id' => 'required', 
            'employee_photo'=> 'mimes:jpg,jpeg,png',  
            'age' => 'required|integer|min:18',  
            'employee_number' => 'required|integer|min:0', 
            'next_of_kin_contact_number' => 'required|integer|min:0',  
            'telephone_number' => 'required|integer|min:0',               
            'fax_number' => 'required|integer|min:0',               
            
        ]);

        if ($validator->fails()) {   
            return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
        } 
        
        if (!$request->id) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|unique:users',
            ]);

            if ($validator->fails()) {   
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            } 
        }
        


        try{
            $userData = User::updateOrCreate([
            'id'=> $request->id,
            ],[
                'email'=> $request->email,
                'is_employee'=> true
            ]);
        $path = 'uploads/employee';
        $oldLogo = '';

            

        $data = [
            'user_id' => $userData->id,
            'company_id' => $request->company_id,
            'first_name' => $request->first_name,
            'surname' => $request->surname,
            'alias_name' => $request->alias_name,
            'suffix' => $request->suffix,
            'gender' => $request->gender,
            'age' => $request->age,
            'dob' => $request->dob,
            'id_number' => $request->id_number,
            'employee_number' => $request->employee_number,
            'hire_date' => $request->hire_date,
            'position' => $request->position,
            'department' => $request->department,
            'office_location' => $request->office_location,
            'desk' => $request->desk,
            'level_in_the_company' => $request->level_in_the_company,
            'salary' => $request->salary,
            'last_date_od_raise' => $request->last_date_od_raise,
            'reporting_manager' => $request->reporting_manager,
            'next_of_kin_name' => $request->next_of_kin_name,
            'next_of_kin_contact_number' => $request->next_of_kin_contact_number,
            'home_address' => $request->home_address,
            'telephone_number' => $request->telephone_number,
            'fax_number' => $request->fax_number,
            'employee_photo'=> $this->UpdateImage($request->file('employee_photo'), $path) ?? null,
        ];

        $employee = Employees::updateOrCreate(['user_id' => $userData->id], $data);


        return ResponseBuilder::successMessage('Employees Register Successfully!',  $this->success);

    }    
    catch (\Exception $e) 
    {

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
    public function forgetPassWord(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users',
            ]);
    
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
            $parameters = $request->all();
            extract($parameters);
    
            $otp = rand(1000,9999);
            $user = User::where('email', $email)->first();
    
            if($user){
                $user->update([
                    'otp' => $otp,
                    'deleted_at' => null
                ]);
            }
    
            $mailTemplate = MailTemplate::where('category','verify-company-email')->first();
            
            if($mailTemplate){
                $array1 =  ['{otp}'];
                $array2 = [$otp];  
                $mailTemplate->message = str_replace($array1, $array2, $mailTemplate->message);
                \Mail::to($request->email)->send(new SendMail($mailTemplate));
            }
           
            return ResponseBuilder::success($this->response,'OTP send successfully');
        } catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }
    public function verifyForgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
        }
        $exitUser = User::where('email',$request->email)->first();
        if(!$exitUser){
            return ResponseBuilder::error('Invalid email address',  $this->badRequest);
        }
        if($exitUser->otp != $request->otp){
            return ResponseBuilder::error('OTP does not match',  $this->badRequest);
        }
        $exitUser->otp  = null;
        $exitUser->save();

        return ResponseBuilder::successMessage('OTP Verified Successfully!',  $this->success);
    }
    public function changeForgetPassword(Request $request)
    {
        # code...
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:6|confirmed',
                'password_confirmation' => 'required|min:6',
                'email' => 'required|email|exists:users',
            ]);
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
            $parameters = $request->all();
            extract($parameters);

            $user = User::where('email', $email)->first();
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

    public function setAuthResponse($user)
    {
        $this->response->user = new UserResource($user);
    }

    // Update User Profile
    public function updateProfile(Request $request)
    {
        # code...
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required' ,
                'email' => 'required|email', 
                'phone' => 'required',
            ]);
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            $parameters = $request->all();
            extract($parameters);
            $data = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'dob' => $dob ?? '',
                'address' => $address ?? '',
                'country' => $country ?? '',
                'state' => $state ?? '',
                'city' => $city ?? '',
                'zip' => $zip ?? '',
            ];

            $user = User::updateOrCreate(['id' => $user->id], $data);
            if($request->file('image')) {
                $file = $request->file('image');
                $imageName = $this->UpdateImage($file, 'assets/users/'.$user->id);
                $user->image = $imageName; 
                $user->save(); 
            }
            return ResponseBuilder::successMessage('Account Updated Successfully!',  $this->success);

        } catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    // Get User Data
    public function getUserData()
    {
        # code...
        try {

            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            $user = User::where('id', $user->id)->first();
            $this->response->user = new UserResource($user);
            return ResponseBuilder::success($this->response, 'User Data',  $this->success);

        } catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    public function Logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return ResponseBuilder::success($this->response, 'User Logout Successfully',  $this->success);
    }
    

//     public function Logout()
// {
//     Auth::logout();

//     // Returning a JSON response
//     return response()->json([
//         'message' => 'User Logout Successfully',
//         'status' => $this->success,
//     ]);
// }


// dashboard api

public function Dashboard()

{
   
    try {
            
        if(Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
        } else {
            return ResponseBuilder::error("User not found", $this->unauthorized);
        }
        $project = Project::where('company_id', $user->id)->take(10)->get();
        $projectAll = Project::where('company_id', $user->id)->get();
        $task = ProjectTask::whereIn('project_id',$project->pluck('id')->toArray())->get();
     
        // $returnData['overview']['projects'] = new ProjectCollection($project);
        $returnData['overview']['tasks'] = new ProjectTaskCollection($task);
        $dateRange = [];
            $begin = new DateTime(Carbon::now()->startOfWeek());
            $end = new DateTime(Carbon::now()->endOfWeek());
            $interval = new DateInterval('P1D'); // 1 Day interval
            $daterange = new DatePeriod($begin, $interval, $end);
            foreach ($daterange as $date) {
                $dateRange[] = $date->format('l');
               
                $companyProject = Project::where('company_id', $user->id)->whereDate('created_at',$date->format("Y-m-d"))->count();
                $projectTask = ProjectTask::whereIn('project_id',$project->pluck('id')->toArray())->whereDate('created_at',$date->format("Y-m-d"))->count();
                $totalProjects['project'][] = $companyProject ?? 0;
                $totalProjects['project_task'][] = $projectTask ?? 0;
            }
        $returnData['analytics']['task_in_7_days'] =  $totalProjects;
        $returnData['analytics']['project_status']['logged'] = $projectAll->where('status','logged')->count();
        $returnData['analytics']['project_status']['in_progress'] = $projectAll->where('status','in progress')->count();
        $returnData['analytics']['project_status']['awaiting_approval'] = $projectAll->where('status','awaiting approval')->count();
        $returnData['analytics']['project_status']['completed'] = $projectAll->where('status','completed')->count();

        return ResponseBuilder::success($returnData, 'Dashboard Data retrieved successfully');
    } catch (\Exception $e) {
        return ResponseBuilder::error($e->getMessage(), 500);
    }
}

   
    
}
