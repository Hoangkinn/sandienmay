<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::select("update m_products set regular_price = 0 where regular_price is null");
        DB::select("update m_products set regular_price_3 = 0 where regular_price_3 is null");
        DB::select("update m_products set regular_price_5 = 0 where regular_price_5 is null");
        DB::select("update m_products set sale_price = 0 where sale_price is null");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
