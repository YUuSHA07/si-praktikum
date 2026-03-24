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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id(); // Ini menghasilkan BigInt Unsigned
            
            $table->string('student_id', 20); // Sesuaikan PK User Anda
            $table->foreign('student_id')->references('id')->on('users');
            
            $table->foreignId('meeting_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('final_task_id')->nullable()->constrained('final_tasks')->onDelete('cascade');
            $table->boolean('is_final')->default(false);
            
            $table->enum('aslab_status', ['Pending', 'Revisi', 'ACC'])->default('Pending');
            $table->enum('laboran_status', ['Pending', 'Revisi', 'ACC'])->default('Pending');
            $table->enum('dosen_status', ['N/A', 'Pending', 'Revisi', 'ACC'])->default('N/A');
            $table->boolean('is_completed')->default(false);

            $table->dateTime('first_upload_at')->nullable();
            $table->dateTime('last_upload_at')->nullable();
            $table->dateTime('aslab_acc_at')->nullable();
            $table->dateTime('laboran_acc_at')->nullable();
            $table->dateTime('dosen_acc_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
