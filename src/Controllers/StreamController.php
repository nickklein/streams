<?php

namespace NickKlein\Streams\Controllers;

use Illuminate\Http\Request;
use NickKlein\Streams\Services\StreamService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use NickKlein\Streams\Requests\StreamerRequest;

class StreamController extends Controller
{
    public function index(StreamService $streamService)
    {
        return Inertia::render('Stream/Index', [
            'profiles' => $streamService->getAllHandleIds(Auth::user()->id),
        ]);
    }

    public function create()
    {
        return Inertia::render('Stream/Add', []);
    }

    public function store(StreamerRequest $request, StreamService $streamService)
    {
        $fields = $request->validated();
        $response = $streamService->storeStreamer(Auth::user()->id, $fields['platform'], $fields['name'], $fields['channel_id'], $fields['channel_url']);
        if ($response) {
            return back()->with([
                'message' => 'Streamer added successfully',
            ], Response::HTTP_OK);
        }

        return back()->with([
            'message' => 'Streamer not added',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    public function getProfile(Request $request, StreamService $streamService)
    {
        $profile = $streamService->getProfile(Auth::user()->id, $request->id);

        return response()->json($profile);
    }
}
