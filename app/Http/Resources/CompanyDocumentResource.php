<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyDocumentResource extends JsonResource
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
            'documents_title'   => $this->documents_title,
            "document_file"     => url('uploads/companydocument',$this->document_file),
            'who_uploaded'      => $this->who_uploaded,
            'date_uploaded'     => $this->date_uploaded,
            'validity'          => $this->validity,
            'end_date'          => $this->end_date,
        ];
    }
}
