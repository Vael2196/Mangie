<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Board extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'project_id'];

    // Board belongs to Project (one to one)
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Board has many Columns (one to many)
    public function columns()
    {
        return $this->hasMany(Column::class);
    }
}
