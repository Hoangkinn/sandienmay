<?php

namespace App\DTOs;

use App\Models\SettingField;

class SettingFieldDto extends BaseDto
{
    public readonly string $key;
    public readonly string $value;
    public readonly string $name;
    public readonly string $type;
    public function __construct(SettingField $field)
    {
        $this->key = $field->field_key;
        $this->value = $field->field_value;
        $this->name = $field->field_name;
        $this->type = $field->field_type->value;
    }
}
