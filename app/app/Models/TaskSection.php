<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskSection extends Model
{
    protected $fillable = [
        'task_id',
        'title',
        'content',
        'position',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
