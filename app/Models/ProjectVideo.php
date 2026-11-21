<?php

namespace App\Models;

class ProjectVideo extends BaseModel
{
    protected $table = 'm_project_videos';

    protected $fillable = [
        'link',
        'project_id',
    ];
}
