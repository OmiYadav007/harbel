<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notification;
use Illuminate\Http\Request;
use App\Setting;
use App\User;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    //

    public function index(Request $request)
    {
        # code...
        $data['title'] = trans('global.notification-management');

        $notificationStatus = Setting::where('key', 'notification_status')->first();
        if($notificationStatus && $notificationStatus->value){
            $data['notificationStatus'] = $notificationStatus->value;
        } else {
            $data['notificationStatus'] = false;
        }

        if ($request->ajax()) {
            $data = Notification::orderBy('id', 'DESC');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function($row){
                    return Carbon::parse($row->created_at)->format('d-M-Y H:i');
                })
                ->rawColumns(['title', 'description', 'created_at'])
                ->make(true);
        }

        return view('admin.notification-management.index', $data);
    }

    // sendNotification
    public function sendNotification(Request $request)
    {
        # code...
        $title = $request->title;
        $description = $request->description;
        $firebaseToken = User::groupBy('fcm_token')->pluck('fcm_token');
        $notificationStatus = Setting::where('key', 'notification_status')->first();
        if($notificationStatus->value == 'true') {
            $response = $this->sendPushNotifications($firebaseToken, $title, $description, $url = '');

            $notification = new Notification();
            $notification->title = $title;
            $notification->description = $description;
            $notification->save();

            return response()->json(['status' => $response['status'], 'response'=>$response['response'], 'msg' => 'Notifications Sent Successfully!']);
        } else {
            return response()->json(['status' => true, 'response'=>'', 'msg' => 'Notifications Sent Not Allowed!']);
        }

        
    }


    // notificationStatusUpdate

    public function notificationStatusUpdate(Request $request)
    {
        # code...
        // return $request->checked; 
        $notificationStatus = Setting::updateOrCreate(['key' => 'notification_status'],['value' => $request->checked]);
        return response()->json(['status' => true, 'msg' => 'Status Updated Successfully!']);
    }
}
