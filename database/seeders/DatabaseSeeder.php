<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Feedback Admin',
                'email' => 'admin@feedback.com'
            ],
            [
                'name' => 'Product Manager',
                'email' => 'pm@feedback.com'
            ],
            [
                'name' => 'Developer Lead',
                'email' => 'dev@feedback.com'
            ],
            [
                'name' => 'UX Designer',
                'email' => 'ux@feedback.com'
            ]
        ];

        foreach ($users as $userData) {
            $user = User::factory()->create($userData);

            // Only create posts/comments for admin user
            if ($userData['email'] === 'admin@feedback.com') {
                $posts = [
                    [
                        'title' => 'Dark Mode Request',
                        'description' => 'Please add a dark mode option to reduce eye strain'
                    ],
                    [
                        'title' => 'Mobile App Performance',
                        'description' => 'The mobile app is slow when loading large feedback threads'
                    ],
                    [
                        'title' => 'Search Functionality',
                        'description' => 'We need better search capabilities to find old feedback'
                    ],
                    [
                        'title' => 'Notification Settings',
                        'description' => 'Allow users to customize notification preferences'
                    ],
                    [
                        'title' => 'Export Feedback Data',
                        'description' => 'Add option to export feedback as CSV/PDF'
                    ],
                    [
                        'title' => 'User Profile Improvements',
                        'description' => 'Profile pages need more detailed information'
                    ],
                    [
                        'title' => 'Keyboard Shortcuts',
                        'description' => 'Add keyboard shortcuts for common actions'
                    ]
                ];

                foreach ($posts as $postData) {
                    $post = \App\Models\Post::factory()->create([
                        'user_id' => $user->id,
                        'title' => $postData['title'],
                        'description' => $postData['description'],
                    ]);

                    $comments = [
                        [
                            'description' => match($postData['title']) {
                                'Dark Mode Request' => 'This would help with late-night work sessions!',
                                'Mobile App Performance' => 'I experience++ second load times regularly',
                                'Search Functionality' => 'The current search only looks at titles, not content',
                                'Notification Settings' => 'Getting too many emails for minor updates',
                                'Export Feedback Data' => 'Needed for our monthly stakeholder reports',
                                'User Profile Improvements' => 'Would help identify subject matter experts',
                                'Keyboard Shortcuts' => 'Power users would really appreciate this'
                            }
                        ],
                        [
                            'description' => match($postData['title']) {
                                'Dark Mode Request' => 'Could we get a toggle in the user settings?',
                                'Mobile App Performance' => 'The web version works much better',
                                'Search Functionality' => 'Filters by date would be helpful too',
                                'Notification Settings' => 'I only want notifications for my posts',
                                'Export Feedback Data' => 'CSV format would be most useful',
                                'User Profile Improvements' => 'Add badges for top contributors',
                                'Keyboard Shortcuts' => 'Should include shortcuts for navigation'
                            }
                        ],
                        [
                            'description' => match($postData['title']) {
                                'Dark Mode Request' => 'Blue light filtering would be a nice addition',
                                'Mobile App Performance' => 'The issue seems worse on Android',
                                'Search Functionality' => 'Search history/saved searches would help',
                                'Notification Settings' => 'Push notifications would be better than email',
                                'Export Feedback Data' => 'Include comment threads in the export',
                                'User Profile Improvements' => 'Activity timeline would be useful',
                                'Keyboard Shortcuts' => 'Can we see a list of proposed shortcuts?'
                            }
                        ]
                    ];

                    foreach ($comments as $commentData) {
                        \App\Models\Comment::factory()->create([
                            'post_id' => $post->id,
                            'description' => $commentData['description'],
                        ]);
                    }
                }
            }
        }
    }
}
