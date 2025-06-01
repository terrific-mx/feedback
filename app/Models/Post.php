<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
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
