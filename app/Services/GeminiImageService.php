<?php

namespace App\Services;

use App\Models\PhotoEdit;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GeminiImageService
{
    public function edit(PhotoEdit $edit): string
    {
        if (! config('gemini.key')) {
            throw new RuntimeException('GEMINI_API_KEY não foi configurada.');
        }

        $parts = [
            ['text' => "Edite a imagem principal conforme a instrução a seguir. Preserve identidade e proporções naturais quando houver uma pessoa. Instrução: {$edit->prompt}"],
            $this->imagePart($edit->source_path),
        ];

        if ($edit->reference_path) {
            $parts[] = ['text' => 'A segunda imagem é uma referência. Se solicitado, use a pessoa/elemento dela na cena principal de modo realista.'];
            $parts[] = $this->imagePart($edit->reference_path);
        }

        $response = $this->client()->post(config('gemini.base_url').'/models/'.config('gemini.model').':generateContent', [
            'contents' => [['role' => 'user', 'parts' => $parts]],
            'generationConfig' => ['responseModalities' => ['TEXT', 'IMAGE']],
        ])->throw()->json();

        $image = collect(data_get($response, 'candidates.0.content.parts', []))
            ->first(fn (array $part) => filled(data_get($part, 'inlineData.data')) || filled(data_get($part, 'inline_data.data')));
        $data = data_get($image, 'inlineData.data') ?? data_get($image, 'inline_data.data');

        if (! $data) {
            throw new RuntimeException('O Gemini não retornou uma imagem. Tente ajustar o prompt.');
        }

        $mime = data_get($image, 'inlineData.mimeType') ?? data_get($image, 'inline_data.mime_type') ?? 'image/png';
        $extension = str_contains($mime, 'webp') ? 'webp' : (str_contains($mime, 'jpeg') ? 'jpg' : 'png');
        $path = 'results/'.now()->format('Y/m').'/'.$edit->id.'-'.str()->uuid().'.'.$extension;
        Storage::disk('public')->put($path, base64_decode($data, true) ?: throw new RuntimeException('Resposta de imagem inválida.'));

        return $path;
    }

    private function imagePart(string $path): array
    {
        return ['inlineData' => [
            'mimeType' => Storage::disk('public')->mimeType($path) ?: 'image/jpeg',
            'data' => base64_encode(Storage::disk('public')->get($path)),
        ]];
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()->timeout(120)->withQueryParameters(['key' => config('gemini.key')]);
    }
}
