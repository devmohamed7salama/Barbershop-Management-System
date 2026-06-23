<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->string('service_description');
            $table->string('service_image');
            $table->decimal('service_price');
            $table->integer('service_duration');
            $table->timestamps();
        });
    }
};
