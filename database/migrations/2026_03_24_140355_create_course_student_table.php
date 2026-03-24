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
            $table->string('course_name'); // Kecerdasan Buatan
            $table->string('class_group'); // IK-1
            $table->integer('target_semester'); // Semester 5
            $table->foreignId('dosen_id')->constrained('users');
            $table->foreignId('laboran_id')->constrained('users');
            $table->foreignId('aslab_id')->constrained('users');
            $table->string('enrollment_code')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_student');
    }
};
