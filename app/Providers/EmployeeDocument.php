<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDocument extends Model
{
    

    public $table = 'employees_documents';

    protected $dates = [
        'created_at',
        'updated_at',
       
    ];

    protected $fillable = [
        'documents_title',
        'document_file',
        'user_id',
        'created_at',
        'updated_at',
     
    ];
    
    public function employee()
    {
        return $this->hasOne(User::class, 'id', 'user_id' );
    }
    
}
