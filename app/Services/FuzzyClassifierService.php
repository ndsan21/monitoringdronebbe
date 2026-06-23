<?php

namespace App\Services;

use App\Models\FlightLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FuzzyClassifierService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.fuzzy_api.base_url', 'http://127.0.0.1:5000');
    }

    public function extractInputFromFlightLog(FlightLog $flightLog): ?array
{
    $suhu = $flightLog->battery_temp ?? $flightLog->temperature_c;
    $baterai = $flightLog->drone_battery_finish;
    $durasiDetik = $flightLog->duration;

    if ($suhu === null || $baterai === null || $durasiDetik === null) {
        return null;
    }

    return [
        'suhu' => (float) $suhu,
        'baterai' => (float) $baterai,
        'jam_terbang' => round(((float) $durasiDetik) / 3600, 2),
    ];
}
    public function classify(array $input): ?array
{
    try {
        $response = Http::timeout(5)
            ->post("{$this->baseUrl}/predict-severity", $input);

        if ($response->failed()) {
            Log::warning('FuzzyClassifierService: Flask API mengembalikan error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();

        return [
            'label' => $data['label'] ?? null,
            'score' => isset($data['score']) ? (float) $data['score'] : null,
            'explanation' => $data['explanation'] ?? null,
        ];
    } catch (\Throwable $e) {
        Log::error('FuzzyClassifierService: Gagal menghubungi Flask API', [
            'message' => $e->getMessage(),
        ]);
        return null;
    }
}

public function classifyFromFlightLog(FlightLog $flightLog): ?array
{
    $input = $this->extractInputFromFlightLog($flightLog);

    if ($input === null) {
        return null;
    }

    $result = $this->classify($input);

    if ($result === null) {
        return null;
    }

    return [
        'label' => $result['label'],
        'score' => $result['score'],
        'explanation' => $result['explanation'],
        'input' => $input,
    ];
}
}