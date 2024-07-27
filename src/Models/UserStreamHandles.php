<?php

namespace NickKlein\Stream\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStreamHandles extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function streamHandle()
    {
        return $this->hasOne(StreamHandles::class, 'id', 'stream_handle_id');
    }

    public function scopeStreamHandleFilterPlatform($query, $platform)
    {
        return $query->whereHas('streamHandle', function ($q) use ($platform) {
            $q->where('platform', $platform);
        });
    }
}
