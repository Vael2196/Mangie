<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Board extends Model
{
    use HasFactory;
    protected $attributes = ['version' => 1];

    protected static function booted(): void
    {
        static::creating(function (Board $board) {
            if (!User::whereKey($board->user_id)->whereNotNull('email_verified_at')->lockForUpdate()->first()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'board' => 'A board must be created by a verified user.',
                ]);
            }
        });
        static::created(function (Board $board) {
            $board->users()->syncWithoutDetaching([
                $board->user_id => ['role' => 'admin'],
            ]);
        });
    }

    protected $fillable = [
        'name',
        'is_backlog',
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
            'is_backlog' => 'boolean',
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
            ->withPivot('role')
            ->withTimestamps();
    }

    public function roleFor(User $user): ?string
    {
        // The historical creator ID does not grant permanent access.
        return $this->users()->whereKey($user->id)->value('board_user.role');
    }

    public function invitations()
    {
        return $this->hasMany(BoardInvitation::class);
    }

    public function labels()
    {
        return $this->hasMany(BoardLabel::class)
            ->orderBy('position');
    }

    public function scopeAccessibleTo(
        Builder $query,
        User $user
    ): Builder {
        return $query->whereHas('users', fn (Builder $members) => $members->whereKey($user->id));
    }

    public function isBacklog(): bool
    {
        return (bool) $this->is_backlog;
    }

    public function scopeBacklogs(Builder $query): Builder
    {
        return $query->where('is_backlog', true);
    }

    public function scopeSprints(Builder $query): Builder
    {
        return $query->where('is_backlog', false);
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
