<?php

namespace App\Enums;

enum AttributeType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case PRICE = 'price';
    case BOOLEAN = 'boolean';
    case SELECT = 'select';
    case MULTISELECT = 'multiselect';
    case DATETIME = 'datetime';
    case DATE = 'date';
    case IMAGE = 'image';
    case FILE = 'file';
    case CHECKBOX = 'checkbox';

    public function label(): string
    {
        return match ($this) {
            static::TEXT => 'Text',
            static::TEXTAREA => 'Textarea',
            static::PRICE => 'Price',
            static::BOOLEAN => 'Boolean',
            static::SELECT => 'Select',
            static::MULTISELECT => 'Multiselect',
            static::DATETIME => 'Datetime',
            static::DATE => 'Date',
            static::IMAGE => 'Image',
            static::FILE => 'File',
            static::CHECKBOX => 'Checkbox',
        };
    }
}
