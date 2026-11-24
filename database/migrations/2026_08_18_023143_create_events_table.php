<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {

            $table->id();

            $table->foreignId('room_id')
                  ->constrained('rooms')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->string('title');

            $table->text('description')->nullable();

            $table->date('event_date');

            $table->time('start_time');

            $table->time('end_time');

            $table->enum('status', [
                'Pending',
                'Approved',
                'Rejected',
                'Cancelled',
                'Completed'
            ])->default('Pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};