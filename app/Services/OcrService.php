<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrService
{
    /**
     * Recognize registration plate text from an image.
     */
    public function recognizeRegistration(string $imagePath): array
    {
        $rawText = $this->performOcr($imagePath);

        if (empty($rawText)) {
            return ['text' => null, 'confidence' => 0, 'raw' => ''];
        }

        // Clean: keep only alphanumeric, spaces, dashes
        $cleaned = preg_replace('/[^A-Z0-9\s\-]/i', '', strtoupper($rawText));
        $cleaned = preg_replace('/[\-]/', ' ', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));

        // Match Ivorian plate patterns: old "1234 AB 01", new "AA 716 XF", provisional "32255 CI 01"
        if (preg_match('/[A-Z0-9]{2,5}\s*[A-Z0-9]{1,4}\s*[A-Z0-9]{1,4}/', $cleaned, $match)) {
            $result = preg_replace('/\s+/', '', $match[0]); // Strip whitespace
            return ['text' => substr($result, 0, 20), 'confidence' => 0.85, 'raw' => $rawText];
        }

        // Fallback: return cleaned text without spaces
        $result = preg_replace('/\s+/', '', $cleaned);
        if (strlen($result) >= 4) {
            return ['text' => substr($result, 0, 20), 'confidence' => 0.5, 'raw' => $rawText];
        }

        return ['text' => null, 'confidence' => 0, 'raw' => $rawText];
    }

    /**
     * Recognize chassis/VIN number from an image.
     */
    public function recognizeChassis(string $imagePath): array
    {
        $rawText = $this->performOcr($imagePath);

        if (empty($rawText)) {
            return ['text' => null, 'confidence' => 0, 'raw' => ''];
        }

        // Clean: keep only alphanumeric
        $cleaned = preg_replace('/[^A-Z0-9]/i', '', strtoupper($rawText));

        // Common OCR corrections for VIN (I, O, Q not valid in VIN)
        $cleaned = str_replace(['O', 'I', 'Q'], ['0', '1', '0'], $cleaned);

        // VIN pattern: 10-17 alphanumeric characters
        if (preg_match('/[A-HJ-NPR-Z0-9]{10,17}/', $cleaned, $match)) {
            return ['text' => $match[0], 'confidence' => 0.85, 'raw' => $rawText];
        }

        // Fallback: return if long enough
        if (strlen($cleaned) >= 6) {
            return ['text' => substr($cleaned, 0, 17), 'confidence' => 0.4, 'raw' => $rawText];
        }

        return ['text' => null, 'confidence' => 0, 'raw' => $rawText];
    }

    /**
     * Perform OCR using configured provider.
     */
    private function performOcr(string $imagePath): string
    {
        $provider = config('services.ocr.provider', 'google_vision');

        return match ($provider) {
            'google_vision' => $this->googleVisionOcr($imagePath),
            'tesseract' => $this->tesseractOcr($imagePath),
            default => '',
        };
    }

    /**
     * Google Cloud Vision REST API OCR.
     * Requires GOOGLE_CLOUD_VISION_API_KEY in .env
     */
    private function googleVisionOcr(string $imagePath): string
    {
        $apiKey = config('services.ocr.google_api_key');

        if (empty($apiKey)) {
            Log::warning('OCR: Google Cloud Vision API key not configured');
            return '';
        }

        try {
            $imageContent = base64_encode(file_get_contents($imagePath));

            $response = Http::timeout(15)->post(
                "https://vision.googleapis.com/v1/images:annotate?key={$apiKey}",
                [
                    'requests' => [
                        [
                            'image' => ['content' => $imageContent],
                            'features' => [
                                ['type' => 'TEXT_DETECTION', 'maxResults' => 5],
                            ],
                        ],
                    ],
                ]
            );

            if ($response->successful()) {
                $data = $response->json();
                return $data['responses'][0]['textAnnotations'][0]['description'] ?? '';
            }

            Log::warning('OCR: Google Vision API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return '';
        } catch (\Exception $e) {
            Log::warning('OCR: Google Vision API exception', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Tesseract OCR (requires tesseract binary on server).
     */
    private function tesseractOcr(string $imagePath): string
    {
        try {
            $output = [];
            $returnCode = 0;
            exec(
                'tesseract ' . escapeshellarg($imagePath) . ' stdout --psm 7 2>/dev/null',
                $output,
                $returnCode,
            );

            if ($returnCode === 0) {
                return implode("\n", $output);
            }

            Log::warning('OCR: Tesseract failed', ['code' => $returnCode]);
            return '';
        } catch (\Exception $e) {
            Log::warning('OCR: Tesseract exception', ['error' => $e->getMessage()]);
            return '';
        }
    }
}
