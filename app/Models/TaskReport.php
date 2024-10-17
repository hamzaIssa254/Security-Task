<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskReport extends Model
{
    use HasFactory;

    protected $fillable = ['report'];

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_report_task');
    }
}
