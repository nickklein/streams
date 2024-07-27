<?php

namespace NickKlein\Stream\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreamHandles extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function userStreamHandles()
    {
        return $this->belongsTo(UserStreamHandles::class, 'id', 'stream_handle_id');
    }
}
