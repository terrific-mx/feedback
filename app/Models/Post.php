<?php

namespace App\Models;

use App\Notifications\NewCommentNotification;
use App\Notifications\PostStatusChanged;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'image_paths' => 'array',
        ];
    }

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

    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'subscriptions');
    }

    public function notifySubscribers(Comment $comment)
    {
        $this->subscribers()
            ->where('user_id', '!=', $comment->user_id)
            ->each(fn ($user) => $user->notify(new NewCommentNotification($this, $comment)));
    }

    public function notifySubscribersAboutStatusChange($oldStatus, $newStatus)
    {
        $subscribers = $this->subscribers()->where('user_id', '!=', Auth::id())->get();

        foreach ($subscribers as $user) {
            $user->notify(new PostStatusChanged($this, $oldStatus, $newStatus));
        }
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

    #[Scope]
    protected function open(Builder $query)
    {
        $query->whereIn('status', ['pending', 'reviewing', 'planned', 'in progress']);
    }

    #[Scope]
    protected function closed(Builder $query)
    {
        $query->whereIn('status', ['completed', 'closed']);
    }

    #[Scope]
    protected function top(Builder $query): void
    {
        $query->withCount('votes')->orderByDesc('votes_count');
    }

    #[Scope]
    protected function byBoard(Builder $query, Board $board): void
    {
        $query->where('board_id', $board->id);
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

    protected function imageUrls(): Attribute
    {
        return Attribute::make(
            get: fn () => collect($this->image_paths)
                ->map(fn (string $path) => Storage::disk('public')->url($path))
                ->toArray(),
        );
    }
}
