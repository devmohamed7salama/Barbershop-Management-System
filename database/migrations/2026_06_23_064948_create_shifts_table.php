<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->decimal('total_cash')->nullable();
            $table->decimal('total_revenue')->nullable();
            $table->decimal('total_orders')->nullable();
            $table->enum('shift_status', ['open', 'closed']);
            $table->timestamps();
        });
    }

    
};
