<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\OcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OcrController extends Controller
{
    public function __construct(
        protected OcrService $ocrService,
    ) {}

    /**
     * POST /api/v1/ocr/registration
     * Recognize registration plate text from uploaded image.
     */
    public function recognizeRegistration(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $result = $this->ocrService->recognizeRegistration(
            $request->file('image')->getRealPath()
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * POST /api/v1/ocr/chassis
     * Recognize chassis/VIN number from uploaded image.
     */
    public function recognizeChassis(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $result = $this->ocrService->recognizeChassis(
            $request->file('image')->getRealPath()
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
