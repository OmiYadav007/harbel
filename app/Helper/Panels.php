<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Panels extends Model
{
    use HasFactory;


    public $table = 'panels';

    protected $dates = [
        'created_at',
        'updated_at',
      
       
    ];

    protected $fillable = [
        'id',
        'title',
        'amount',
        'commissions_rate',
        'image',
        'description',
    ];

}
