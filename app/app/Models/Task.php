<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

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
        'version',
    ];

    protected function casts(): array
    {
        return [
            'column_id' => 'integer',
            'position' => 'integer',
            'story_points' => 'integer',
            'time_log' => 'integer',
            'completed_at' => 'date',
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
}
