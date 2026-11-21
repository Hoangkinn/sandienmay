<?php

namespace App\Models;

class Project extends BaseModel
{
    protected $table = 'm_projects';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'order',
        'status',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'seo_url_canonical',
        'seo_no_index',
        'created_id',
        'updated_id',
    ];

    public function images()
    {
        return $this->hasMany(ProjectImage::class, 'project_id', 'id')->select('name', 'url', 'thumb_url', 'has_primary as primary');
    }

    public function imagePrimary()
    {
        return $this->hasMany(ProjectImage::class, 'project_id', 'id')->where('has_primary', 1);
    }

    public function imageFirst()
    {
        return $this->hasOne(ProjectImage::class, 'project_id', 'id')->first();
    }
}
