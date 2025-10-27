<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // return view('index');
    }

    public function getState(Request $request)
    {
        # code...
        $states = DB::table('states')->where('country_id', $request->country_id)->pluck('name', 'id');
        return response()->json([
            'status'=>true, 
            'states' => $states
        ]);
    }
}
