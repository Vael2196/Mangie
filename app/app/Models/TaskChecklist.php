<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskChecklist extends Model
{
    protected $fillable = ['task_id', 'title', 'position'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function items()
    {
        return $this->hasMany(TaskChecklistItem::class)
            ->orderBy('position');
    }
}
