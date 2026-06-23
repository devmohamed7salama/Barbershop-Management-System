<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->string('barber_name');
            $table->string('barber_phone')->unique();
            $table->string('barber_nid')->unique();
            $table->decimal('salary');
            $table->enum('barber_status', ['available', 'unavailable'])->default('available');
            $table->timestamps();
        });
    }
};
