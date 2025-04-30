<?php

namespace Database\Factories;

use App\Models\RevisionRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class RevisionRequestFactory extends Factory
{
    protected $model = RevisionRequest::class;

    public function definition()
    {
        return [
            'reason'     => $this->faker->sentence(),
            'status'     => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
