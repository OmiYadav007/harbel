<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use stdClass;
use App\Mail\SendMail;
use Exception;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $serverError = 500;
    protected $success = 200;
    protected $badRequest = 400;
    protected $unauthorized = 401;
    protected $notFound = 404;
    protected $forbidden = 403;
    protected $upgradeRequired = 426;

    protected $response;

    public function __construct()
    {
        $this->response = new stdClass();
    }

    public function UpdateImage($file, $path = '')
    {
        # code...    
        $filename = $file->getClientOriginalName();
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $imageName = time().uniqid().str_replace(' ','-',$filename).'.'.$file->extension();
        // Public Folder
        $file->move(public_path($path), $imageName);
        // $file->storeAs($path, $imageName);
        return $imageName; 
    }
    
    static public function MailCategories($category = '', $status = false)
    {
        # code...
        $categories = [
            'sign-up-admin'=>'SignUp Mail To Admin',
            'signup-otp'=>'Sign OTP',
            'resend-otp'=>'Resend OTP',
            'forgot-password' => 'Forget Password',
            'contact-us' => 'Contact Us',
        ];
        if(isset($category) && !empty($category) && array_key_exists($category, $categories)) {
            if($status)
                return $categories[$category];
            else
                return [$category => $categories[$category]];
        } else {
            return $categories;
        }
    }

    static public function getCountry($id = '')
    {
        # code...
        if($id)
        return DB::table('countries')->where('id', $id)->pluck('name', 'id')->toArray();
        else 
        return DB::table('countries')->pluck('name', 'id')->toArray();
    }

    static public function getStates($country_id = '', $state_id = '')
    {
        # code...
        if($state_id) {
            return DB::table('states')->where('id', $state_id)->where('country_id',$country_id)->pluck('name', 'id')->toArray();
        } else {
            return DB::table('states')->where('country_id', $country_id)->pluck('name', 'id')->toArray();
        }
    }

    // Generate OTP
    public function generateOtp($digits = 4)
    {
        # code...
        return rand(pow(10, $digits-1), pow(10, $digits)-1);
    }

    // Send Mail
    public function SendMail($mailTo, $mailData, $basicInfo)
    {
        # code...
        try {
            //code...
            $message = $mailData->message;
            foreach($basicInfo as $key=> $info){
                $message = str_replace($key, $info, $message);
            }
            
            $config = [
                'from_email' => $mailData->mail_from,
                "reply_email" => $mailData->reply_email,
                'subject' => $mailData->subject, 
                'name' => $mailData->name,
                'message' => $message,
            ];
            Mail::to($mailTo)->send(new SendMail($config));

            return ['status'=>true, 'message'=> 'Mail Sent'];
        } catch (Exception $e) {
            // $e
            return ['status'=>false, 'message'=> $e->getMessage()];
        }

    }

    static public function truncateString($str, $words, $replacement="...") 
    {
        return Str::words($str, $words, $replacement);
    }
}
