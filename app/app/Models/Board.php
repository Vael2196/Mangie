<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'project_id',
        'user_id',
        'completed',
        'status',
        'start_date',
        'end_date',
        'duration',
        'sprint_goal',
        'total_story_points',
        'date_ended',
        'background_color',
        'background_image_path',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'status' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'date_ended' => 'date',
            'duration' => 'integer',
            'total_story_points' => 'integer',
            'version' => 'integer',
        ];
    }

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

    public function users()
    {
        return $this
            ->belongsToMany(User::class)
            ->withTimestamps();
    }

    public function scopeAccessibleTo(
        Builder $query,
        User $user
    ): Builder {
        return $query->where(
            function (Builder $query) use ($user) {
                $query
                    ->where('user_id', $user->id)
                    ->orWhereHas(
                        'users',
                        fn (Builder $members) =>
                            $members->whereKey($user->id)
                    );
            }
        );
    }

    public function getBackgroundImageUrlAttribute(): ?string
    {
        if (!$this->background_image_path) {
            return null;
        }

        return Storage::disk('public')->url(
            $this->background_image_path
        );
    }

}
