<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function hasVoted(User $user): bool
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function addVote(User $user): void
    {
        $this->votes()->create(['user_id' => $user->id]);
    }

    public function removeVote(User $user): void
    {
        $this->votes()->where('user_id', $user->id)->delete();
    }

    public function toggleVote(User $user): void
    {
        $this->hasVoted($user) ? $this->removeVote($user) : $this->addVote($user);
    }

    #[Scope]
    protected function planned(Builder $query)
    {
        $query->where('status', 'planned');
    }

    #[Scope]
    protected function inProgress(Builder $query)
    {
        $query->where('status', 'in progress');
    }

    #[Scope]
    protected function completed(Builder $query)
    {
        $query->where('status', 'completed');
    }

    protected function formattedStatus(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => match ($attributes['status']) {
                'pending' => __('Pending'),
                'reviewing' => __('Reviewing'),
                'planned' => __('Planned'),
                'in progress' => __('In Progress'),
                'completed' => __('Completed'),
                'closed' => __('Closed'),
                default => __(ucfirst($attributes['status'])),
            },
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => match ($attributes['status']) {
                'pending' => 'yellow',
                'reviewing' => 'amber',
                'planned' => 'sky',
                'in progress' => 'purple',
                'closed' => 'zinc',
                'completed' => 'green',
                default => 'zinc',
            },
        );
    }
}
