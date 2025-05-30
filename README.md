# Terrific Feedback

A Laravel-based feedback management system that allows users to submit, track, and manage product/service improvement ideas.

## Installation

1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env` and configure your database
4. Run `php artisan key:generate`
5. Run migrations with `php artisan migrate`

**Note:** This project requires Livewire Flux PRO components which require a paid license.

**Authentication:** This project uses [WorkOS](https://workos.com/) for authentication. For configuration details, refer to the [Laravel documentation](https://laravel.com/docs/12.x/starter-kits#workos).

## Features

Current features:
- User feedback post creation
- Listing all received feedback
- Commenting on feedback submissions

Planned features:
- Feedback categorization
- Voting system
- Admin moderation tools
