<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attachment extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['attachable_id',"attachable_type",'file_path','file_name'];


    protected $dates = ['deleted_at'];

    public function task()
    {
        return $this->morphTo();
    }
}
