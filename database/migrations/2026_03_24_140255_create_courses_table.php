<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->string('course_name');
            $table->string('class_group');
            $table->integer('target_semester');

            // Perbaikan: ID User adalah string(20)
            $table->string('dosen_id', 20);
            $table->foreign('dosen_id')->references('id')->on('users');

            $table->string('laboran_id', 20);
            $table->foreign('laboran_id')->references('id')->on('users');

            $table->string('aslab_id', 20);
            $table->foreign('aslab_id')->references('id')->on('users');

            $table->string('enrollment_code')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
