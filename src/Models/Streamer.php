<?php

namespace NickKlein\Streams\Models;

use Illuminate\Database\Eloquent\Model;

class Streamer extends Model
{
    public $timestamps = false;

    public function streamHandles()
    {
        return $this->hasMany(StreamHandle::class, 'streamer_id', 'id');
    }
}
