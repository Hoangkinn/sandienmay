<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $fields = [
        [
            'field_name' => 'Tác giả bài viết (Tên hiển thị)',
            'field_key' => 'post_author_name',
            'field_type' => 'text',
            'field_value' => 'CEO Trần Văn Lân',
        ],
        [
            'field_name' => 'Tác giả bài viết (Chức danh)',
            'field_key' => 'post_author_role',
            'field_type' => 'text',
            'field_value' => 'Chuyên gia thiết bị vệ sinh công nghiệp',
        ],
        [
            'field_name' => 'Tác giả bài viết (Giới thiệu)',
            'field_key' => 'post_author_bio',
            'field_type' => 'textarea',
            'field_value' => 'Chia sẻ kinh nghiệm chọn mua và bảo dưỡng thiết bị công nghiệp, giúp khách hàng chọn đúng model phù hợp nhu cầu và ngân sách.',
        ],
    ];

    public function up(): void
    {
        foreach ($this->fields as $field) {
            DB::table('m_setting_fields')->updateOrInsert(
                ['field_key' => $field['field_key']],
                $field + ['user_id' => 1, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        Cache::forget('allSettingFields');
    }

    public function down(): void
    {
        DB::table('m_setting_fields')->whereIn('field_key', array_column($this->fields, 'field_key'))->delete();

        Cache::forget('allSettingFields');
    }
};
