<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helper\Helper;
class WalletHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {   
        
        return [
            'id'  => $this->id,
            'transaction_id' => ($this->transaction_id)??'',
            'transaction_amount' => number_format(($this->transaction_amount ?? 0),2),
            'user_id' => $this->user_id,
            'transaction_type' => $this->transaction_type,
            'transaction_flag' => Helper::getTranFlag($this->transaction_type) ?? '',
            'type' => Helper::getTranType($this->transaction_type) ?? $this->transaction_type,
            'transferTo' => ($this->transferTo)??'',
            'transferFrom' => ($this->transferFrom)??'',
            'transaction_description' => ($this->transaction_description)??'',
            'market_name' => ($this->market_name)??'',
            'game_name' => ($this->game_name)??'',
            'bat_number' => ($this->bat_number)??'',
            'bet_type' => ($this->bet_type)??'',
            'withdrawal_status' => ($this->withdrawal_status)??'',
            'roulette_open_time' => ($this->roulette_open_time)??'',
            'roulette_close_time' => ($this->roulette_close_time)??'',
            'market_id' => ($this->market_id)??'',
            'notification_status' => ($this->notification_status)??'',
            'betting_time_type' => ($this->betting_time_type)??'',
            'market_id' => ($this->market_id)??'',
        ];
    }
}
