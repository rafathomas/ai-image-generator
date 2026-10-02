<?php

namespace Tests\Feature\Api;

use App\Jobs\ProcessPhotoEdit;
use App\Models\PhotoEdit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoEditControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_edit_and_dispatches_processing(): void
    {
        Storage::fake('public');
        Bus::fake();
        $response = $this->postJson('/api/photo-edits', [
            'photo' => UploadedFile::fake()->image('original.png'),
            'reference_photo' => UploadedFile::fake()->image('reference.png'),
            'prompt' => 'Insira a pessoa da referência na imagem principal.',
        ]);

        $response->assertAccepted()->assertJsonPath('status', 'queued');
        $this->assertDatabaseCount('photo_edits', 1);
        Storage::disk('public')->assertExists(PhotoEdit::firstOrFail()->source_path);
        Bus::assertDispatched(ProcessPhotoEdit::class);
    }

    public function test_it_rejects_missing_or_invalid_edit_data(): void
    {
        $this->postJson('/api/photo-edits', ['prompt' => 'oi', 'photo' => UploadedFile::fake()->create('texto.txt', 10)])
            ->assertUnprocessable()->assertJsonValidationErrors(['photo', 'prompt']);
    }

    public function test_it_lists_and_returns_a_single_edit(): void
    {
        $edit = PhotoEdit::create(['source_path' => 'uploads/a.png', 'prompt' => 'Ajuste a iluminação.']);
        $this->getJson('/api/photo-edits')->assertOk()->assertJsonCount(1);
        $this->getJson('/api/photo-edits/'.$edit->id)->assertOk()->assertJsonPath('id', $edit->id);
    }
}
