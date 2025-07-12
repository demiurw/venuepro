<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    public function definition()
    {
        return [
            'key' => $this->faker->unique()->slug,
            'value' => $this->faker->word,
            'type' => 'string',
            'is_public' => false,
        ];
    }
}
