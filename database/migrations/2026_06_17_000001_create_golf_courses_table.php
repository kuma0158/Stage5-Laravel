<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('golf_courses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('locale', 2);
            $table->string('country_code', 2);
            $table->string('state_prefecture', 255)->nullable();
            $table->string('course_name', 255);
            $table->integer('kinds')->nullable();
            $table->text('web')->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->boolean('indoor')->default(false);
            $table->boolean('outdoor')->default(false);
            $table->boolean('short_course')->default(false);
            $table->boolean('long_course')->default(false);
            $table->double('lat')->nullable();
            $table->double('lng')->nullable();
            $table->string('form_email', 255)->nullable();
            $table->string('reservation', 255)->nullable();
            $table->string('reservation_method', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->string('image1', 255)->nullable();
            $table->string('image2', 255)->nullable();
            $table->string('image3', 255)->nullable();
            $table->timestamps();

            $table->index('country_code');
            $table->index('locale');
            $table->index('state_prefecture');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('golf_courses');
    }
};
