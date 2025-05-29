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
                ['name' => 'Alex Johnson', 'email' => 'alex.johnson@feedback.com', 'avatar' => 'https://mighty.tools/mockmind-api/content/human/112.jpg'],
                ['name' => 'Maria Garcia', 'email' => 'maria.garcia@feedback.com', 'avatar' => 'https://mighty.tools/mockmind-api/content/human/106.jpg'],
                ['name' => 'James Wilson', 'email' => 'james.wilson@feedback.com', 'avatar' => 'https://mighty.tools/mockmind-api/content/human/104.jpg'],
                ['name' => 'Sarah Lee', 'email' => 'sarah.lee@feedback.com', 'avatar' => 'https://mighty.tools/mockmind-api/content/human/108.jpg'],
            )
            ->create();

        Post::factory()
            ->count(7)
            ->sequence(
                [
                    'title' => 'Export Feedback Data',
                    'user_id' => $users->firstWhere('name', 'Alex Johnson')->id,
                    'description' => 'Add option to export feedback as CSV/PDF.',
                    'created_at' => now()->subDays(5)
                ],
                [
                    'title' => 'Dark Mode Request',
                    'user_id' => $users->firstWhere('name', 'Alex Johnson')->id,
                    'description' => 'Please add a dark mode option to reduce eye strain.',
                    'created_at' => now()->subDays(7)
                ],
                [
                    'title' => 'User Profile Improvements',
                    'user_id' => $users->firstWhere('name', 'Maria Garcia')->id,
                    'description' => 'Profile pages need more detailed information.',
                    'created_at' => now()->subDays(10)
                ],
                [
                    'title' => 'Mobile App Performance',
                    'user_id' => $users->firstWhere('name', 'Maria Garcia')->id,
                    'description' => 'The mobile app is slow when loading large feedback threads.',
                    'created_at' => now()->subDays(14)
                ],
                [
                    'title' => 'Keyboard Shortcuts',
                    'user_id' => $users->firstWhere('name', 'James Wilson')->id,
                    'description' => 'Add keyboard shortcuts for common actions.',
                    'created_at' => now()->subDays(15)
                ],
                [
                    'title' => 'Search Functionality',
                    'user_id' => $users->firstWhere('name', 'James Wilson')->id,
                    'description' => 'We need better search capabilities to find old feedback.',
                    'created_at' => now()->subDays(21)
                ],
                [
                    'title' => 'Notification Settings',
                    'user_id' => $users->firstWhere('name', 'Sarah Lee')->id,
                    'description' => 'Allow users to customize notification preferences.',
                    'created_at' => now()->subDays(28)
                ],
            )
            ->has(
                Comment::factory()
                    ->count(3)
                    ->state(function (array $attributes, Post $post) use ($users) {
                        return [
                            'user_id' => $users->random()->id,
                            'created_at' => $post->created_at->addHours(rand(1, 168)),
                            'description' => match($post->title) {
                                'Dark Mode Request' => [
                                    'This would help with late-night work sessions!',
                                    'Could we get a toggle in the user settings?',
                                    'Blue light filtering would be a nice addition.'
                                ][rand(0, 2)],
                                'Mobile App Performance' => [
                                    'I experience++ second load times regularly,',
                                    'The web version works much better.',
                                    'The issue seems worse on Android.'
                                ][rand(0, 2)],
                                'Search Functionality' => [
                                    'The current search only looks at titles, not content.',
                                    'Filters by date would be helpful too.',
                                    'Search history/saved searches would help.'
                                ][rand(0, 2)],
                                'Notification Settings' => [
                                    'Getting too many emails for minor updates.',
                                    'I only want notifications for my posts.',
                                    'Push notifications would be better than email.'
                                ][rand(0, 2)],
                                'Export Feedback Data' => [
                                    'Needed for our monthly stakeholder reports.',
                                    'CSV format would be most useful.',
                                    'Include comment threads in the export.'
                                ][rand(0, 2)],
                                'User Profile Improvements' => [
                                    'Would help identify subject matter experts.',
                                    'Add badges for top contributors.',
                                    'Activity timeline would be useful.'
                                ][rand(0, 2)],
                                'Keyboard Shortcuts' => [
                                    'Power users would really appreciate this.',
                                    'Should include shortcuts for navigation.',
                                    'Can we see a list of proposed shortcuts?'
                                ][rand(0, 2)]
                            }
                        ];
                    })
            )
            ->create();
    }
}
