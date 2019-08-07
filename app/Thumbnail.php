<?php

namespace App;
use App\Video;
use Illuminate\Database\Eloquent\Model;

class Thumbnail extends Model
{
    protected $fillable = ['video_id', 'thumbnail'];


    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
