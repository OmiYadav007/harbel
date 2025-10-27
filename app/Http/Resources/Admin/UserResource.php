<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'name'                  => (string)$this->name??'',
            'email'                 => (string)$this->email??'',
            'phone'                 => (string)$this->phone??'',
            'status'                => (string)$this->status??'',
            'address'               => (string)$this->address??'',
            'country'               => (string)$this->country??'',
            'state'                 => (string)$this->state??'',
            'city'                  => (string)$this->city??'',
            'zip_code'              => (string)$this->zip??'',
            'profile_image'         => (string)$this->image??'',
            'role_id'               => $this->roles[0]->id??'',
            'role'                  => $this->roles[0]->title??'',
        ];
    }
}
