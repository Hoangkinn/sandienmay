<?php

namespace App\Models;

class ProjectImage extends BaseModel
{
    protected $table = 'm_project_images';

    protected $fillable = [
        'name',
        'url',
        'thumb_url',
        'has_primary',
        'project_id'
    ];
}
