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
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->string('queue_number');
            $table->string('nik');
            $table->string('kk');
            $table->enum('status', ['waiting', 'serving', 'done', 'cancelled'])->default('waiting');
            $table->uuid('qr_token')->unique();
            $table->date('queue_date');
            $table->timestamps();

            // Indexes
            $table->unique(['nik', 'kk', 'queue_date']);
            $table->index(['location_id', 'queue_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
