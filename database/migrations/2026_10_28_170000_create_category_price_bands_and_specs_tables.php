<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
    Mức giá + thông số cấu hình RIÊNG cho từng danh mục cấp 1, thay cho kiểu tạo danh mục
    con display_type=price/spec (những trang không có sản phẩm nào để lọc).
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_category_price_bands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->index();
            $table->string('label', 100);
            $table->unsignedBigInteger('min_price')->nullable();
            $table->unsignedBigInteger('max_price')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('m_category_specs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->index();
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('option_id');
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->unique(['category_id', 'option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_category_specs');
        Schema::dropIfExists('m_category_price_bands');
    }
};
