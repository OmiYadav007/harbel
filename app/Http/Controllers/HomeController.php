<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\MatchEntry;
use App\Teams;
use Auth;
use Validator;
use Hash;
use App\Mail\ContactFormMail;
use Mail;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
    public function index()
    {  
        return view('home');
    }
    public function contactSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15'
        ]);

        if ($validator->fails()) {
            return response()->json([
            'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'name' => $request->name,
            'mobile' => $request->phone
        ];

       Mail::to(env('MAIL_ADMIN_EMAIL'))->send(new ContactFormMail($data));

        

        return response()->json([
            'success' => 'Thank you for contacting us. We will get back to you soon.'
        ]);
    }
}
