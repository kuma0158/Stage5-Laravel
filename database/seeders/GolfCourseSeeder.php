<?php

namespace Database\Seeders;

use App\Models\GolfCourse;
use Illuminate\Database\Seeder;

class GolfCourseSeeder extends Seeder
{
    public function run(): void
    {
        // 開発・動作確認用のダミーデータを 50 件投入
        GolfCourse::factory()->count(50)->create();
    }
}
