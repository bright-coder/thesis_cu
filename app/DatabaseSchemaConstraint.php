<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DatabaseSchemaConstraint extends Model
{
    protected $table = "DATABASE_SCHEMA_CONSTRAINT";

    public $timestamps = false;
}
