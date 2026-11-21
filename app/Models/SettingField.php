<?php

namespace App\Models;

use App\Enums\FieldType;

class SettingField extends BaseModel
{
    protected $table = 'm_setting_fields';

    protected $fillable = [
        'field_name',
        'field_key',
        'field_type',
        'field_value',
        'user_id',
    ];

    protected $casts = [
        'field_type' => FieldType::class,
    ];

}
