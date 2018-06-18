<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PkRecord extends Model
{
    protected $table = "PK_RECORD";
    //protected $primaryKey = "id";
    
    //public $incrementing = "false";
    public $timestamps = false;
}
