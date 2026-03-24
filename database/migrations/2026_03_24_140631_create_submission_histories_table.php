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
        Schema::create('submission_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->onDelete('cascade');
            $table->string('drive_link');
            $table->integer('iteration')->default(1); // 1, 2, dst
            $table->text('feedback')->nullable();
            $table->enum('action_type', ['Upload', 'Revision', 'ACC']);
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps(); // created_at akan menjadi tanggal kejadian
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_histories');
    }
};
