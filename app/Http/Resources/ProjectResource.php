<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
                        "id" => $this->id,
                        "project_name" => $this->project_name,
                        "start_date" => $this->start_date,
                        "end_date" => $this->end_date,
                        "type_of_project "=> $this->type_of_project,
                        "budget "=> $this->budget,
                        "location" => $this->location,
                        "description" =>$this->description,
                        "region "=>$this->region,
                        "client_id" =>$this->client_id,
                        "client_manager" =>$this->client_manager,
                        "client_manager_name" =>isset($this->clientManager->employeeData->first_name) ? $this->clientManager->employeeData->first_name : '',
                        "project_director "=>$this->project_director,
                        "project_director_name" =>isset($this->projectDirector->employeeData->first_name) ? $this->projectDirector->employeeData->first_name : '',
                        "project_manager" =>$this->project_manager,
                        "project_manager_name" =>isset($this->projectManager->employeeData->first_name) ? $this->projectManager->employeeData->first_name : '',
                        "projectEmployees "=> !empty($this->projectEmployees) ? new EmployeesCollection($this->projectEmployees) : '',
                        "projectExpense"=> !empty($this->projectExpense) ? new ProjectExpenseCollection($this->projectExpense) : '',
                        "projectTask"=> !empty($this->projectTask) ? new ProjectTaskCollection($this->projectTask) :'',
                        "projectDocument"=> new ProjectDocumentCollection($this->projectDocument),
                        "sop"=>isset($this->Sop) ? $this->Sop : '',
                        

          
        ];
    }
}
