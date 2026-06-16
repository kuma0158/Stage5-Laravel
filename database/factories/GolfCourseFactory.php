<?php

namespace Database\Factories;

use App\Models\GolfCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GolfCourse>
 */
class GolfCourseFactory extends Factory
{
    protected $model = GolfCourse::class;

    public function definition(): array
    {
        $prefectures = [
            '北海道', '東京都', '神奈川県', '千葉県', '埼玉県',
            '茨城県', '栃木県', '群馬県', '長野県', '静岡県',
            '愛知県', '大阪府', '兵庫県', '福岡県', '沖縄県',
        ];

        return [
            'locale'             => $this->faker->randomElement(['ja', 'en']),
            'country_code'       => 'JP',
            'state_prefecture'   => $this->faker->randomElement($prefectures),
            'course_name'        => $this->faker->city() . 'カントリークラブ',
            'kinds'              => $this->faker->numberBetween(1, 5),
            'web'                => $this->faker->url(),
            'phone'              => $this->faker->phoneNumber(),
            'address'            => $this->faker->address(),
            'indoor'             => $this->faker->boolean(20),
            'outdoor'            => $this->faker->boolean(80),
            'short_course'       => $this->faker->boolean(30),
            'long_course'        => $this->faker->boolean(60),
            'lat'                => $this->faker->randomFloat(6, 24, 46),
            'lng'                => $this->faker->randomFloat(6, 122, 146),
            'form_email'         => $this->faker->safeEmail(),
            'reservation'        => $this->faker->url(),
            'reservation_method' => $this->faker->randomElement(['電話', 'WEB', 'メール']),
            'remarks'            => $this->faker->optional(0.6)->realText(80),
            'image1'             => null,
            'image2'             => null,
            'image3'             => null,
        ];
    }
}
