<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Project has many Boards (one-to-many)
    public function boards()
    {
        return $this->hasMany(Board::class);
    }
}
