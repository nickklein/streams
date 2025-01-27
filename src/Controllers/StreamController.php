<?php

namespace NickKlein\Streams\Controllers;

use Illuminate\Http\Request;
use NickKlein\Streams\Services\StreamService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Routing\Controller;

class StreamController extends Controller
{
    public function index(StreamService $streamService)
    {
        return Inertia::render('Stream/Index', [
            'profiles' => $streamService->getAllHandleIds(Auth::user()->id),
        ]);
    }


    public function getProfile(Request $request, StreamService $streamService)
    {
        $profile = $streamService->getProfile(Auth::user()->id, $request->id);

        return response()->json($profile);
    }
}
