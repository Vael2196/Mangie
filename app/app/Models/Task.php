<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'column_id', 'position'];

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
