<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory()
            ->count(4)
            ->sequence(
                ['name' => 'Alex Johnson', 'email' => 'alex.johnson@feedback.com'],
                ['name' => 'Maria Garcia', 'email' => 'maria.garcia@feedback.com'],
                ['name' => 'James Wilson', 'email' => 'james.wilson@feedback.com'],
                ['name' => 'Sarah Lee', 'email' => 'sarah.lee@feedback.com']
            )
            ->create();

        Post::factory()
            ->count(7)
            ->sequence(
                ['title' => 'Dark Mode Request'],
                ['title' => 'Mobile App Performance'],
                ['title' => 'Search Functionality'],
                ['title' => 'Notification Settings'],
                ['title' => 'Export Feedback Data'],
                ['title' => 'User Profile Improvements'],
                ['title' => 'Keyboard Shortcuts']
            )
            ->state(function (array $attributes) use ($users) {
                return [
                    'user_id' => $users->random()->id,
                    'description' => match($attributes['title']) {
                        'Dark Mode Request' => 'Please add a dark mode option to reduce eye strain',
                        'Mobile App Performance' => 'The mobile app is slow when loading large feedback threads',
                        'Search Functionality' => 'We need better search capabilities to find old feedback',
                        'Notification Settings' => 'Allow users to customize notification preferences',
                        'Export Feedback Data' => 'Add option to export feedback as CSV/PDF',
                        'User Profile Improvements' => 'Profile pages need more detailed information',
                        'Keyboard Shortcuts' => 'Add keyboard shortcuts for common actions'
                    }
                ];
            })
            ->has(
                Comment::factory()
                    ->count(3)
                    ->state(function (array $attributes, Post $post) {
                        return [
                            'description' => match($post->title) {
                                'Dark Mode Request' => [
                                    'This would help with late-night work sessions!',
                                    'Could we get a toggle in the user settings?',
                                    'Blue light filtering would be a nice addition'
                                ][rand(0, 2)],
                                'Mobile App Performance' => [
                                    'I experience++ second load times regularly',
                                    'The web version works much better',
                                    'The issue seems worse on Android'
                                ][rand(0, 2)],
                                'Search Functionality' => [
                                    'The current search only looks at titles, not content',
                                    'Filters by date would be helpful too',
                                    'Search history/saved searches would help'
                                ][rand(0, 2)],
                                'Notification Settings' => [
                                    'Getting too many emails for minor updates',
                                    'I only want notifications for my posts',
                                    'Push notifications would be better than email'
                                ][rand(0, 2)],
                                'Export Feedback Data' => [
                                    'Needed for our monthly stakeholder reports',
                                    'CSV format would be most useful',
                                    'Include comment threads in the export'
                                ][rand(0, 2)],
                                'User Profile Improvements' => [
                                    'Would help identify subject matter experts',
                                    'Add badges for top contributors',
                                    'Activity timeline would be useful'
                                ][rand(0, 2)],
                                'Keyboard Shortcuts' => [
                                    'Power users would really appreciate this',
                                    'Should include shortcuts for navigation',
                                    'Can we see a list of proposed shortcuts?'
                                ][rand(0, 2)]
                            }
                        ];
                    })
            )
            ->create();
    }
}
