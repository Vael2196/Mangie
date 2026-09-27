<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskChecklistItem extends Model
{
    protected $fillable = [
        'task_checklist_id',
        'content',
        'is_complete',
        'completed_at',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_complete' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function checklist()
    {
        return $this->belongsTo(TaskChecklist::class, 'task_checklist_id');
    }
}
