<?php

namespace NickKlein\Streams\Models;

use Illuminate\Database\Eloquent\Model;

class StreamHandle extends Model
{
    public $timestamps = false;

    public function streamer()
    {
        return $this->belongsTo(Streamer::class, 'streamer_id', 'id');
    }
}
