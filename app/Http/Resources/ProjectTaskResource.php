<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTaskResource extends JsonResource
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
            'project_id'        => $this->project_id,
            'ref_number'        => $this->ref_number,
            'description'        => $this->description,
            'start_date'        => $this->start_date,
            'end_date'          => $this->end_date,
            'task_manager'      => $this->task_manager,
            'phase'             => $this->phase,
            'category'          => $this->category,
            'status'            => $this->status,
            "taskEmployees "    => new EmployeesCollection($this->taskEmployees),
          
     
           
        ];
    }
}
