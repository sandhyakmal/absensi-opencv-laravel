<?php

namespace App\Services;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FaceClient
{
    private string $baseUrl;
    private string $keyPlain;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.face_api.url'), '/');
        $this->keyPlain = (string) config('services.face_api.key_plain');
    }

    private function headers(): array
    {
        return [
            'X-API-KEY' => $this->keyPlain,
        ];
    }

    public function ping(): bool
    {
        try {
            $res = Http::timeout(2)->get($this->baseUrl.'/health');
            return $res->successful();
        } catch (\Throwable $e) {
            \Log::error('FaceAPI ping failed: '.$e->getMessage());
            return false;
        }
    }

    public function recognize(string $imagePath, float $threshold = 0.35, float $minDetScore = 0.5): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(60)
            ->attach('file', file_get_contents($imagePath), basename($imagePath))
            ->asMultipart()
            ->post($this->baseUrl . '/recognize', [
                'threshold' => (string) $threshold,
                'min_det_score' => (string) $minDetScore,
            ]);
    }

    public function enroll(string $id, string $name, string $imagePath, float $minDetScore = 0.5, bool $rejectMultiple = true): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(60)
            ->attach('file', file_get_contents($imagePath), basename($imagePath))
            ->asMultipart()
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
            ->asMultipart()
            ->post($this->baseUrl . '/delete', [
                'id' => $id,
            ]);
    }


}
