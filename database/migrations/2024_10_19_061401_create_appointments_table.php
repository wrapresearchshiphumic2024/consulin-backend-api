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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['waiting', 'ongoing', 'completed', 'canceled'])->default('waiting');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->foreignId('patient_id')->references('id')->on('patients');
            $table->foreignId('psychologist_id')->references('id')->on('psychologists');
            $table->string('channel_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
