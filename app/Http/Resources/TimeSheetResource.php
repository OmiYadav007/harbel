<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeSheetResource extends JsonResource
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
            'start_time'        => $this->start_time,
            'end_time'          => $this->end_time,
            'status'            => $this->status,
            'employee'          => !empty($this->employee) ? new EmployeesResource($this->employee) :'',
            // 'project'          =>  new ProjectResource($this->project),
            'project' => !empty($this->project) ? new ProjectResource($this->project): '' ,
           
          
     
           
        ];
    }
}
