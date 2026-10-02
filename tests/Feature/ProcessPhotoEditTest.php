<?php

namespace Tests\Feature;

use App\Jobs\ProcessPhotoEdit;
use App\Models\PhotoEdit;
use App\Services\GeminiImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessPhotoEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_the_image_returned_by_gemini(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/source.png', 'fake-image');
        config(['gemini.key' => 'test-key']);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['inlineData' => ['mimeType' => 'image/png', 'data' => base64_encode('generated-image')]]]]]]])]);
        $edit = PhotoEdit::create(['source_path' => 'uploads/source.png', 'prompt' => 'Deixe o fundo azul.']);

        (new ProcessPhotoEdit($edit->id))->handle(app(GeminiImageService::class));

        $edit->refresh();
        $this->assertSame('completed', $edit->status);
        Storage::disk('public')->assertExists($edit->result_path);
    }

    public function test_it_marks_the_edit_as_failed_when_the_provider_fails(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/source.png', 'fake-image');
        config(['gemini.key' => null]);
        $edit = PhotoEdit::create(['source_path' => 'uploads/source.png', 'prompt' => 'Melhore a imagem.']);

        (new ProcessPhotoEdit($edit->id))->handle(app(GeminiImageService::class));

        $edit->refresh();
        $this->assertSame('failed', $edit->status);
        $this->assertStringContainsString('GEMINI_API_KEY', $edit->error_message);
    }
}
