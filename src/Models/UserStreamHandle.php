<?php

namespace NickKlein\Streams\Models;

use Illuminate\Database\Eloquent\Model;

class UserStreamHandle extends Model
{
    public $timestamps = false;

    public $fillable = [
        'user_id',
        'streamer_id',
        'preferred_platform',
        'last_synced_at',
        'is_live',
        'queued',
    ];

    public function streamer()
    {
        return $this->belongsTo(Streamer::class, 'streamer_id', 'id');
    }
}
