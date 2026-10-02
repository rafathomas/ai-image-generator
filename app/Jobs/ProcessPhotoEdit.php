<?php

namespace App\Jobs;

use App\Models\PhotoEdit;
use App\Services\GeminiImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessPhotoEdit implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public int $photoEditId) {}

    public function handle(GeminiImageService $gemini): void
    {
        $edit = PhotoEdit::findOrFail($this->photoEditId);
        $edit->update(['status' => 'processing', 'error_message' => null]);
        try {
            $edit->update(['status' => 'completed', 'result_path' => $gemini->edit($edit)]);
        } catch (Throwable $exception) {
            report($exception);
            $edit->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
        }
    }
}
