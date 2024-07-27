<?php

namespace NickKlein\Stream\Controllers;

use NickKlein\Stream\Services\StreamService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class StreamController extends Controller
{
    //
    public function index(StreamService $streamService)
    {
        return Inertia::render('Packages/Stream/Index', [
            'profiles' => $streamService->getHandles(Auth::user()->id),
        ]);
    }
}
