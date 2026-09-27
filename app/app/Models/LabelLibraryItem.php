<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabelLibraryItem extends Model
{
    protected $fillable = [
        'label_library_id',
        'name',
        'color',
        'position',
    ];

    public function library()
    {
        return $this->belongsTo(LabelLibrary::class, 'label_library_id');
    }
}
