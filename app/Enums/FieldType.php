<?php

namespace App\Enums;

enum FieldType: String
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case EDITOR = 'editor';
    case IMAGE = 'image';
    case FILE = 'file';

    public function label(): string
    {
        return match ($this) {
            static::TEXT => 'Text',
            static::TEXTAREA => 'Textarea',
            static::EDITOR => 'Editor',
            static::IMAGE => 'Image',
            static::FILE => 'File',
        };
    }
}
