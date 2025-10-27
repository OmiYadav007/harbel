<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    public $table = 'wallet_transactions';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'transaction_id',
        'transaction_amount',
        'user_id',
        'transaction_update_date',
        'transaction_type',
        'transferTo',
        'transferFrom',
        'transaction_description',
        'market_name',
        'game_name',
        'bat_number',
        'bet_type',
        'withdrawal_status',
        'roulette_open_time',
        'roulette_close_time',
        'market_id',
        'notification_status',
        'betting_time_type',
        'admin_screen_shot'
    ];
    
    public function getCreatedAtAttribute($value)
    {
        # code...
        return Carbon::parse($value)->format('Y-M-d H:i:s');
    }
    public function user()
    {
        return $this->hasOne(User::class,  'id', 'user_id');
    }
    // public function getTransactionTypeAttribute($value)
    // {
    //     # code...
    //     if ($value == "Add" || $value == "AddAmt") {
    //         return "<span class='badge bg-success'>Money Added</span>";
    //     }elseif ($value == "Sub") {
    //         return "<span class='badge bg-danger'>Withdraw</span>";
    //     }elseif ($value == "WithdrawAmt") {
    //         return "<span class='badge bg-danger'>Withdraw</span>";
    //     }elseif ($value == "WinningBat") {
    //         return "<span class='badge bg-success'>Bet Winning</span>";
    //     }else{
    //         return "<span class='badge bg-danger'>Game Bet</span>";
    //     }
    // }
    
}
