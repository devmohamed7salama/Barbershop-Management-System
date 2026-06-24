<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->unique()->after('id');
        });

        // Backfill existing records
        $invoices = DB::table('invoices')->get();
        foreach ($invoices as $invoice) {
            $year = date('Y', strtotime($invoice->created_at));
            $invoiceNumber = 'INV-' . $year . '-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT);
            DB::table('invoices')->where('id', $invoice->id)->update(['invoice_number' => $invoiceNumber]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_number');
        });
    }
};
