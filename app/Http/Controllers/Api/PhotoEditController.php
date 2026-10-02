<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePhotoEditRequest;
use App\Jobs\ProcessPhotoEdit;
use App\Models\PhotoEdit;
use Illuminate\Http\JsonResponse;

class PhotoEditController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(PhotoEdit::latest()->limit(20)->get());
    }

    public function store(StorePhotoEditRequest $request): JsonResponse
    {
        $edit = PhotoEdit::create([
            'source_path' => $request->file('photo')->store('uploads', 'public'),
            'reference_path' => $request->file('reference_photo')?->store('references', 'public'),
            'prompt' => $request->string('prompt')->trim()->toString(),
        ]);

        ProcessPhotoEdit::dispatch($edit->id);

        return response()->json($edit->fresh(), 202);
    }

    public function show(PhotoEdit $photoEdit): JsonResponse
    {
        return response()->json($photoEdit);
    }
}
