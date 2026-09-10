<?php

namespace Database\Factories;

use App\Models\Notice;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'notice_id' => Notice::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->randomNumber(4),
            'excerpt' => fake()->paragraph(),
            'content_html' => '<p>'.fake()->paragraph().'</p>',
            'status' => 'draft',
            'seo_title' => $title,
            'seo_description' => fake()->sentence(),
            'canonical_url' => 'https://suchak.test/recruitment/'.Str::slug($title),
            'published_at' => null,
        ];
    }
}
