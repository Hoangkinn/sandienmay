<?php

namespace App\Models;

use App\Enums\Status;

class Video extends BaseModel
{
    protected $table = 'm_videos';

    protected $fillable = [
        'title',
        'slug',
        'url',
        'image',
        'content',
        'order',
        'status',
        'is_feature',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'seo_url_canonical',
        'seo_no_index',
        'product_id',
        'category_id',
        'created_id',
        'updated_id'
    ];

    // protected $casts = [
    //     'status' => Status::class,
    // ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'updated_id');
    }

    public function getYoutubeIdAttribute()
    {
       
        preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $this->url, $matches);

        return empty($matches[0]) ? '' : $matches[0];
    }

    public function getImageYoutubeAttribute()
    {
        return 'https://i.ytimg.com/vi/' . $this->youtubeId . '/0.jpg';
    }

    public function getImageYoutubeDefaultAttribute()
    {
        return 'https://i.ytimg.com/vi/' . $this->youtubeId . '/default.jpg';
    }
}
