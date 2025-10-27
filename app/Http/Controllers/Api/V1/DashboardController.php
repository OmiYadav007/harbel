<?php

namespace App\Http\Controllers\Api\V1;

use App\Business;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Http\Resources\Admin\UserResource;
use App\MailTemplate;
use App\User;
use App\Company;
use App\Employees;
use App\Tournaments;
use Illuminate\Support\Facades\Auth;
use Exception;
use Hash;
use App\Attendance;
use Validator;
use Carbon\Carbon;
use App\Mail\SendMail;


class DashboardController extends Controller
{

    public function Employeslogin(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'passcode' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }

            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }

                $user = User::where('id' ,$request->id)->select('name','passcode')->get();
                  if($user){         
                        return ResponseBuilder::success('success');                 
                    }
                    else{
                        return ResponseBuilder::error( __("User Not Found"), $this->badRequest);
                    }

        } catch (Exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }
    
    public function checkiIn(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id'=>'required',
            'checkin_time'=>'required',
        ]);

        if ($validator->fails()) {
              return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
        }   
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
        try{
           $atte = Attendance::where('user_id',$request->id)->whereNull('checkout_time')->count();
            if($atte>0){
                return ResponseBuilder::error("Already checkin", $this->badRequest);
            }
             else{
                Attendance::create([
                    'user_id' => $request->id,
                    'checkin_time' => $request->checkin_time,
                    'checkout_time' => null,
                ]);
              return ResponseBuilder::success([], 'Check in Successfully');
             }
        }
        catch (Exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }


    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(),[
            // 'checkin_time'=>'required',
            'checkout_time'=>'required',
        ]);

        if ($validator->fails()) {
              return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
        }   
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            try{
                $atte = Attendance::where('user_id',$request->id)->whereNull('checkout_time');
                if($atte){
                $attendance = Attendance::where('checkout_time', null)->update([
                    'checkout_time' => $request->checkout_time
                    ]);
                    return ResponseBuilder::success($attendance, 'Check Out Successfully');
                }else{
                    return ResponseBuilder::error("Check In First", $this->badRequest);
            }
              
           }
           catch (Exception $e) {
               return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
           }
    }
   
    
}
