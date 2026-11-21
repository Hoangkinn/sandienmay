<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
    Khu tài khoản khách hàng: m_orders.customer_id vốn là cột chết (int thường, không index,
    không khoá ngoại) -> đổi sang bigint unsigned để khớp m_customers.id, rồi mới gắn FK.
    MySQL/MariaDB từ chối FK lệch kiểu (errno 150) nên thứ tự 3 câu lệnh dưới là bắt buộc.
*/
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE m_orders MODIFY customer_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE m_orders ADD INDEX m_orders_customer_id_index (customer_id)');
        DB::statement('ALTER TABLE m_orders ADD CONSTRAINT m_orders_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES m_customers (id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE m_orders DROP FOREIGN KEY m_orders_customer_id_foreign');
        DB::statement('ALTER TABLE m_orders DROP INDEX m_orders_customer_id_index');
        DB::statement('ALTER TABLE m_orders MODIFY customer_id INT NULL');
    }
};
