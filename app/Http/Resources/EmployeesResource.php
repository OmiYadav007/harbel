<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeesResource extends JsonResource
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
            "email"             => isset($this->email) ? $this->email : '',
            "employee_photo"    => url('uploads/employee',$this->employeeData->employee_photo),
            'company_id'        => $this->employeeData->company_id,
            'first_name'        => $this->employeeData->first_name,
            'surname'           => $this->employeeData->surname,
            'alias_name'        => $this->employeeData->alias_name,
            'suffix'            => $this->employeeData->suffix,
            'gender'            => $this->employeeData->gender,
            'age'               => $this->employeeData->age,
            'dob'               => $this->employeeData->dob,
            'id_number'         => $this->employeeData->id_number,
            'employee_number'   => $this->employeeData->employee_number,
            'hire_date'         => $this->employeeData->hire_date,
            'position'          => $this->employeeData->position,
            'department'        => isset($this->employeeData->DepartmentDetails->title) ? $this->employeeData->DepartmentDetails->title : '',
            'office_location'   => $this->employeeData->office_location,
            'desk'              => $this->employeeData->desk,
            'level_in_the_company' => $this->employeeData->level_in_the_company,
            'salary'               => $this->employeeData->salary,
            'last_date_od_raise'   => $this->employeeData->last_date_od_raise,
            'reporting_manager'    => $this->employeeData->reporting_manager,
            'next_of_kin_name'     => $this->employeeData->next_of_kin_name,
            'next_of_kin_contact_number' => $this->employeeData->next_of_kin_contact_number,
            'home_address'          => $this->employeeData->home_address,
            'telephone_number'      => $this->employeeData->telephone_number,
            'fax_number'            => $this->employeeData->fax_number,
        ];
    }
}
