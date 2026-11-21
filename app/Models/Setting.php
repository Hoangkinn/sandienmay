<?php

namespace App\Models;

class Setting extends BaseModel
{
    protected $table = 'm_settings';
    public $timestamps = false;
    protected $fillable = [
        'setting_name',
        'setting_value',
    ];
}
