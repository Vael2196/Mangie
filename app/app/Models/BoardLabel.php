<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'name',
        'color',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'board_id' => 'integer',
            'position' => 'integer',
        ];
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }
}
