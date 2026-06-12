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
        Schema::create('detection_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detection_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->enum('progress_status', ['Unable to measure', 'Healed', 'Improving', 'Worsening', 'Stable']);
            $table->integer('progress_level');
            $table->string('confidence_level');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detection_progress');
    }
};
