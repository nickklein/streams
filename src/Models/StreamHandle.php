<?php

namespace NickKlein\Streams\Models;

use Illuminate\Database\Eloquent\Model;

class StreamHandle extends Model
{
    public $timestamps = false;

    public $fillable = [
        'name',
        'channel_id',
        'channel_url',
        'platform', 
        'streamer_id',
    ];

    public function streamer()
    {
        return $this->belongsTo(Streamer::class, 'streamer_id', 'id');
    }
}
