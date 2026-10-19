<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;
    protected $attributes = ['version' => 1];

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image_path
            ? route('tasks.cover', ['task' => $this->id, 'v' => $this->version])
            : null;
    }

    protected $fillable = [
        'title',
        'description',
        'column_id',
        'position',
        'labels',
        'priority',
        'story_points',
        'time_log',
        'completed_at',
        'start_at',
        'due_at',
        'due_complete',
        'version',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'column_id' => 'integer',
            'position' => 'integer',
            'story_points' => 'integer',
            'time_log' => 'integer',
            'completed_at' => 'datetime',
            'start_at' => 'datetime',
            'due_at' => 'datetime',
            'due_complete' => 'boolean',
            'version' => 'integer',
        ];
    }

    // Task belongs to Column
    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    // Task belongs to many Users
    public function users()
    {
        return $this->belongsToMany(User::class, 'task_users');
    }

    public function boardLabels()
    {
        return $this->belongsToMany(BoardLabel::class)
            ->orderBy('position');
    }

    public function sections()
    {
        return $this->hasMany(TaskSection::class)
            ->orderBy('position');
    }

    public function checklists()
    {
        return $this->hasMany(TaskChecklist::class)
            ->orderBy('position');
    }
}
