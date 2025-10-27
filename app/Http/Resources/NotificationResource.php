<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            "id"                => $this->id,
            'user_id'           => $this->user_id,
            'title'             => $this->title,
            'description'       => $this->description,
            'seen'              => $this->seen,
            
          
          
     
           
        ];
    }
}
