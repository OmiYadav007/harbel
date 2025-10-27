<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectExpenseResource extends JsonResource
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
            'project_id' => $this->project_id,
            'expense_type' => $this->expense_type,
            'date' => $this->date,
            'amount' => $this->amount,
            'status' => $this->status,
            'description' => $this->description,
            'receipt_file' => url('uploads/expense',$this->receipt_file),
     
           
        ];
    }
}
