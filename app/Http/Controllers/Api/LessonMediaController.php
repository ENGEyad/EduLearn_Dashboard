<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonMediaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB
        ]);

        $file = $request->file('file');
        $path = $file->store('lessons', 'public'); // storage/app/public/lessons/...

        $url  = asset('storage/' . $path);

        return response()->json([
            'success' => true,
            'media_path' => $path,
            'media_url'  => $url,
            'media_mime' => $file->getClientMimeType(),
            'media_size' => $file->getSize(), // bytes
        ]);
    }
}
