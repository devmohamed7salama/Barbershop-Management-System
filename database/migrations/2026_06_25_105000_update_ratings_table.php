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
        // 1. Drop existing ratings table if it exists
        Schema::dropIfExists('ratings');

        // 2. Create the new ratings table
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('barber_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('shop_rate');
            $table->unsignedTinyInteger('barber_rate');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // 3. Add rating_status to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'rating_status')) {
                $table->string('rating_status')->default('open')->after('total_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'rating_status')) {
                $table->dropColumn('rating_status');
            }
        });
    }
};
