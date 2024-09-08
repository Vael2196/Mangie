<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Column extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'board_id', 'position'];

    // Column belongs to Board
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    // Column has many Tasks (one to many)
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
