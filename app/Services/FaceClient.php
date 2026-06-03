<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use InvalidArgumentException;

class FaceClient
{
    private string $baseUrl;
    private string $keyPlain;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.face_api.url'), '/');
        $this->keyPlain = (string) config('services.face_api.key_plain');

        if ($this->baseUrl === '') {
            throw new RuntimeException('Face API URL is not configured.');
        }

        if ($this->keyPlain === '') {
            throw new RuntimeException('Face API key is not configured.');
        }
    }

    private function headers(): array
    {
        return [
            'X-API-KEY' => $this->keyPlain,
            'Accept' => 'application/json',
        ];
    }

    private function readImage(string $imagePath): string
    {
        if (! file_exists($imagePath)) {
            throw new InvalidArgumentException("Image file not found: {$imagePath}");
        }

        if (! is_readable($imagePath)) {
            throw new InvalidArgumentException("Image file is not readable: {$imagePath}");
        }

        $contents = file_get_contents($imagePath);

        if ($contents === false) {
            throw new RuntimeException("Failed to read image file: {$imagePath}");
        }

        return $contents;
    }

    public function ping(): bool
    {
        try {
            $res = Http::withHeaders($this->headers())
                ->timeout(2)
                ->get($this->baseUrl . '/health');

            return $res->successful();
        } catch (\Throwable $e) {
            Log::error('FaceAPI ping failed', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function recognize(
        string $imagePath,
        float $threshold = 0.35,
        float $minDetScore = 0.5
    ): Response {
        return Http::withHeaders($this->headers())
            ->timeout(60)
            ->attach(
                'file',
                $this->readImage($imagePath),
                basename($imagePath)
            )
            ->post($this->baseUrl . '/recognize', [
                'threshold' => (string) $threshold,
                'min_det_score' => (string) $minDetScore,
            ]);
    }

    public function enroll(
        string $id,
        string $name,
        string $imagePath,
        float $minDetScore = 0.5,
        bool $rejectMultiple = true
    ): Response {
        return Http::withHeaders($this->headers())
            ->timeout(60)
            ->attach(
                'file',
                $this->readImage($imagePath),
                basename($imagePath)
            )
            ->post($this->baseUrl . '/enroll', [
                'id' => $id,
                'name' => $name,
                'min_det_score' => (string) $minDetScore,
                'reject_if_multiple_faces' => $rejectMultiple ? 'true' : 'false',
            ]);
    }

    public function delete(string $id): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(30)
            ->post($this->baseUrl . '/delete', [
                'id' => $id,
            ]);
    }
}