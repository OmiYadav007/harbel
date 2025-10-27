<?php

namespace App\Http\Controllers;

use App\Http\Resources\Admin\UserResource;
use App\Mail\SendMail;
use App\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use stdClass;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

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

    protected $CurrentDate; 
    protected $CurrentDateTime; 
    protected $CurrentTime; 
    protected $gameType; 
    
    public function __construct(Request $request)
    {
        $this->response = new stdClass();

        date_default_timezone_set('Asia/Kolkata');
        $this->CurrentDate = Carbon::now()->format('Y-m-d');
        $this->CurrentTime = Carbon::now()->format('H:i');
        $this->CurrentDateTime = Carbon::now()->format('Y-m-d H:i');

        $this->gameType = $request->segment(2);
        if($this->gameType == 'games') {
            $this->gameType = 'default';
        }
    }
    public function genPanelCode()
    {
        $this->panel_code = [
            'panel_code' => Str::random(4) . rand(10, 99),
        ];
        $rules = ['panel_code' => 'unique:users'];
        $validate = Validator::make($this->panel_code, $rules)->passes();
        return $validate ? strtoupper($this->panel_code['panel_code']) : $this->genUserCode();
    }
    public function UpdateImage($file, $path = '' , $fileCustomName = '')
    {
        # code...    
        $filename = $file->getClientOriginalName();
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        if(isset($fileCustomName) && $fileCustomName == '') {
            $imageName = time().uniqid().str_replace(' ','-',$filename).'.'.$file->extension();
        } else {
            $imageName = str_replace(' ','-',$fileCustomName).'.'.$file->extension();
        }
            
        // Public Folder
        $file->move(public_path($path), $imageName);
        return $path.'/'.$imageName; 
    }
    
    static public function MailCategories($category = '', $status = false)
    {
        # code...
        $categories = [
            'sign-up'=>'Sign Up',
            'forgot-password' => 'Forget Password',
            'withdrawal-request' => 'Withdrawal Request',
            'user-register' => 'New User Register'
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

    public function generateOtp()
    {
        # code...
        return rand(1000,9999);
    }

    function sendMessage($message, $recipients)
    {

        $apiKey = urlencode('NzM2Zjc1NDQ2ZDYxNjczMzZiNjYzNDU2NTI1NzYzNzc=');
	
        // Message details
        $numbers = array(918987654321);
        $sender = urlencode('TXTLCL');
        $message = rawurlencode('This is your message');
    
        $numbers = implode(',', $numbers);
    
        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
    
        // Send the POST request with cURL
        $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }


    function getPanaType($pana)
    {
        # code...
        $singlePana = ['127','136','145','190','235','280','370','479','460','569','389','578','128','137','146','236','245','290','380','470','489','560','678','579','129','138','147','156','237','246','345','390','480','570','679','589','120','139','148','157','238','247','256','346','490','580','670','689','130','149','158','167','239','248','257','347','356','590','680','789','140','159','168','230','249','258','267','348','357','456','690','780','123','150','169','178','240','259','268','349','358','457','367','790','124','160','179','250','269','278','340','359','368','458','467','890','125','134','170','189','260','279','350','369','378','459','567','468','126','135','180','234','270','289','360','379','450','469','568'];
        $doublePana = ['550','668','244','299','226','488','677','118','334','100','119','155','227','335','344','399','588','669','200','110','228','255','336','499','660','688','778','300','166','229','337','355','445','599','779','788','400','112','220','266','338','446','455','699','770','500','113','122','177','339','366','447','799','889','600','114','277','330','448','466','556','880','899','700','115','133','188','223','377','449','557','566','800','116','224','233','288','440','477','558','990','900','117','144','199','225','388','559','577','667'];
        $triplePana = ['000','111','222','333','444','555','666','777','888','999'];

        if(in_array($pana, $singlePana)){
            return 'single';
        } else if(in_array($pana, $doublePana)){
            return 'double';
        }else if(in_array($pana, $triplePana)){
            return 'triple';
        }
    }


    public function sendPushNotifications($firebaseToken = [], $title, $body, $imageUrl = '')
    {   
        try {
            $notificationStatus = Setting::where('key', 'notification_status')->first();
            if($notificationStatus->value == 'true') {
                //code...
                $data = [
                    "registration_ids" => $firebaseToken,
                    "notification" => [
                        "title" => $title,
                        "body"  => $body,
                        "image" => $imageUrl,
                        'badge' => "1",
                        'priority'=>'10',
                    ],
                    "content_available" => true,
                ];
                $dataString = json_encode($data);
        
                $FIREBASE_SERVER_KEY = config('panel.firebase_token');
                $headers = [
                    'Authorization: key=' . $FIREBASE_SERVER_KEY,
                    'Content-Type: application/json',
                ];
        
                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        
                $response = curl_exec($ch);
        
                return ['status' => true, 'response' => $response];
            } else {
                return ['status' => false, 'response' => 'Can not Send Notification'];
            }
        } catch (\Throwable $th) {
            //throw $th;
            return ['status' => false, 'response' => $th->getMessage()];
        }
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

    // Send OTP
    public function SendOtp($phoneNumber, $otp = '1234')
    {
        # code...
        try {
            //code...
            $send_otp = Setting::where('key', 'send_otp')->first();
            if($send_otp->value == 'yes') {
            // // if(0) {
                $phoneNumber = str_replace("+91", "", $phoneNumber);
                $curl = curl_init();

                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://2factor.in/API/V1/'.config('panel.two_factor_key').'/SMS/+91'.$phoneNumber.'/'.$otp.'/OTP',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                $response;

                return json_decode($response);

            }


                // $curl = curl_init();

                // curl_setopt_array($curl, array(
                //     CURLOPT_URL => 'http://sms.pushpaksms.com/api_v2/message/send',
                //     CURLOPT_RETURNTRANSFER => true,
                //     CURLOPT_ENCODING => '',
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 0,
                //     CURLOPT_FOLLOWLOCATION => true,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => 'POST',
                //     CURLOPT_POSTFIELDS => array('sender_id' => 'JODSMS','dlt_template_id' => '1207168680839278353','message' => $otp.' is the OTP verify your mobile number for '.$phoneNumber.'. NEVER SHARE YOUR OTP WITH ANYONE. 

                //     JSHPL','mobile_no' => $phoneNumber ),
                //     CURLOPT_HTTPHEADER => array(
                //         'Authorization: Bearer '.config('panel.SMS_OTP')
                //     ),
                // ));

                // $response = curl_exec($curl);

                // curl_close($curl);

                // return json_decode($response);
            // }
        } catch (Exception $e) {
            // $e
            return ['status'=>false, 'message'=> $e->getMessage()];
        }

    }

    static public function GetSum($number)
    {
        # code...
        $number = str_split($number);
        $sum = array_sum($number);
        return substr($sum , -1);
    }
    static public function RanDomNumber()
    {
        # code...
        return rand(0,9);
    }


    public function winningAmount($amount)
    {
        // code...
        $adminFee = Setting::where('key', 'admin_fee')->pluck('value')->first();
        $final = (($amount*2)*(100-$adminFee)/100);
        return $final;
    }
}
