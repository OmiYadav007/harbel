<?php

namespace App;

use Carbon\Carbon;
use Hash;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Http\Controllers\Controller as BaseController; 

class User extends Authenticatable
{
    use SoftDeletes, Notifiable, HasApiTokens;

    public $table = 'users';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at',
        'email_verified_at',
    ];

    protected $fillable = [
        'name',
        'email',
        'address',
        'phone',
        'is_model',
        'gems',
        'phone',
        'password',
        'dob',
        'image',
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'status',
        'email_verified_at',
        'company_email_verifed',
        'otp',
        'plan_id',
        'plan_start_date',
        'plan_end_date',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
    public function model()
    {
        return $this->hasOne(Models::class, 'user_id', 'id' );
    }
    public function plan()
    {
        return $this->hasOne(SubscriptionPlan::class, 'id', 'plan_id' );
    }
    public function language()
    {
        return $this->belongsToMany(Language::class, 'model_language', 'user_id', 'language_id');
    }
}
