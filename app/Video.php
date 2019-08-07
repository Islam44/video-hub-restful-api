<?php

namespace App;

use App\Thumbnail;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Nicolaslopezj\Searchable\SearchableTrait;

class Video extends Model
{
    protected $table='videos';
    use SearchableTrait, Searchable;

    protected $dates = [
        'converted_for_downloading_at',
        'converted_for_streaming_at',
    ];
   // protected $fillable = ['title','tag_id'];

    protected $guarded = [];
    public function thumbnails()
    {
        return $this->hasMany(Thumbnail::class);
    }
    protected $searchable = [
        /**
         * Columns and their priority in search results.
         * Columns with higher values are more important.
         * Columns with equal values have equal importance.
         *
         * @var array
         */
        'columns' => [
            'videos.title' => 10,
            'videos.original_name' => 5,
        ],
    ];
    public function toSearchableArray()
    {
        $array = $this->toArray();

        $extraFields = [
            'tag' => $this->tag()->pluck('name')->toArray()
        ];

        return array_merge($array, $extraFields);
    }
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
