<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return false;
    }

    public function vote(User $user, Post $post): bool
    {
        return true;
    }

    public function addComment(User $user, Post $post): bool
    {
        return true;
    }

    public function updateStatus(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }

    public function updateBoard(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }

    public function subscribe(User $user, Post $post)
    {
        return $this->view($user, $post);
    }

    public function unsubscribe(User $user, Post $post)
    {
        return $this->view($user, $post);
    }
}
