<?php

namespace NickKlein\Streams\Models;

use Illuminate\Database\Eloquent\Model;

class UserStreamHandle extends Model
{
    public $timestamps = false;

    public $fillable = [
        'user_id',
        'streamer_id',
        'platform',
    ];

    public function streamer()
    {
        return $this->belongsTo(Streamer::class, 'streamer_id', 'id');
    }
}
