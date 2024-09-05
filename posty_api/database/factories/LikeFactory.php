<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /**
         * just ran a quick factory for Like model not considering any
         *  validation or anything
        *  */ 
        $users = collect(User::all()->modelKeys());
        $posts = collect(Post::all()->modelKeys());
        return [
            'user_id' => $users->random(),
            'post_id' => $posts->random(),
        ];
    }
}
