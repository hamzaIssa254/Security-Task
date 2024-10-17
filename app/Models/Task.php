<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory,SoftDeletes;


    protected $fillable = [
        'title', 'description', 'type', 'status', 'priority', 'due_date', 'assigned_to',
    ];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function statusUpdates()
    {
        return $this->hasMany(TaskStatusUpdate::class);
    }

   // علاقة تبعية المهام (المهام التي تعتمد على هذه المهمة)
   public function dependentTasks()
   {
       return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
   }

   // علاقة المهام التي تعتمد عليها هذه المهمة
   public function dependencies()
   {
       return $this->hasMany(TaskDependency::class, 'task_id');
   }

   // المهام التي تعتمد على هذه المهمة
   public function tasksDependentOnThis()
   {
       return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id');
   }

   // المهام التي تعتمد عليها هذه المهمة
   public function tasksThisDependsOn()
   {
       return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id');
   }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reports()
    {
        return $this->belongsToMany(TaskReport::class, 'task_report_task'); // الجدول الوسيط
    }
}
