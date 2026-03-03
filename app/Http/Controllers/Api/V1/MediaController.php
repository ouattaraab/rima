<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadPhotoRequest;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function upload(UploadPhotoRequest $request): JsonResponse
    {
        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        if ($request->user()->isAgentCidec() && $vehicle->collected_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INSUFFICIENT_PERMISSIONS', 'message' => 'Acces non autorise.'],
            ], 403);
        }

        if (!$vehicle->isEditable()) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'MODIFICATION_NOT_ALLOWED', 'message' => 'Photos uniquement pour les fiches en brouillon ou rejetees.'],
            ], 422);
        }

        $file = $request->file('file');
        $path = $file->store("vehicles/{$vehicle->id}", 'public');

        // Get image dimensions
        $imageSize = getimagesize($file->getRealPath());
        $width = $imageSize[0] ?? null;
        $height = $imageSize[1] ?? null;

        $photo = VehiclePhoto::create([
            'vehicle_id' => $vehicle->id,
            'photo_type' => $request->photo_type,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size_bytes' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'photo_id' => $photo->id,
                'vehicle_id' => $photo->vehicle_id,
                'photo_type' => $photo->photo_type,
                'url' => Storage::disk('public')->url($path),
                'size_bytes' => $photo->size_bytes,
                'width' => $photo->width,
                'height' => $photo->height,
                'uploaded_at' => $photo->created_at,
            ],
            'message' => 'Photo uploadee avec succes',
        ]);
    }

    public function show(string $photo): JsonResponse
    {
        $p = VehiclePhoto::findOrFail($photo);

        $url = Storage::disk('public')->url($p->file_path);

        return response()->json([
            'success' => true,
            'data' => [
                'photo_id' => $p->id,
                'vehicle_id' => $p->vehicle_id,
                'photo_type' => $p->photo_type,
                'url' => $url,
                'thumbnail_url' => $p->thumbnail_path ? Storage::disk('public')->url($p->thumbnail_path) : null,
                'original_name' => $p->original_name,
                'size_bytes' => $p->size_bytes,
                'width' => $p->width,
                'height' => $p->height,
                'captured_at' => $p->captured_at,
                'uploaded_at' => $p->created_at,
            ],
        ]);
    }

    public function destroy(Request $request, string $photo): JsonResponse
    {
        $p = VehiclePhoto::findOrFail($photo);
        $vehicle = $p->vehicle;

        if ($request->user()->isAgentCidec() && $vehicle->collected_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INSUFFICIENT_PERMISSIONS', 'message' => 'Acces non autorise.'],
            ], 403);
        }

        if (!$vehicle->isDraft()) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'DELETION_NOT_ALLOWED', 'message' => 'Suppression uniquement pour les fiches en brouillon.'],
            ], 403);
        }

        Storage::disk('public')->delete($p->file_path);
        if ($p->thumbnail_path) {
            Storage::disk('public')->delete($p->thumbnail_path);
        }
        $p->delete();

        return response()->json(null, 204);
    }
}
