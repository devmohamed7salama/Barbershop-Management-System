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

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('appointment_date');
            $table->time('appointment_time');

            $table->enum('source', [
                'online',
                'offline',
            ]);

            $table->enum('appointment_status', [
                'pending',
                'completed',
                'cancelled',
            ])->default('pending');

            $table->text('appointment_notes')->nullable();

            $table->timestamps();
        });
    }

};
 