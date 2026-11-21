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
        Schema::table('m_products', function (Blueprint $table) {
            $table->unsignedInteger('sold_quantity')->default(0)->after('sale_price');
            $table->unsignedInteger('brand_id')->default(0)->after('id');
            $table->integer('warranty')->nullable()->after('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_products', function (Blueprint $table) {
            $table->dropColumn(['sold_quantity', 'brand_id', 'warranty']);
        });
    }
};
