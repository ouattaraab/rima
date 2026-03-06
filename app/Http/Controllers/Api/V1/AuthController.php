<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private AuditService $auditService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->username)->first();

        if (!$user || !$user->is_active) {
            return $this->errorResponse('INVALID_CREDENTIALS', 'Identifiants incorrects.', 401);
        }

        if ($user->isLocked()) {
            $minutes = now()->diffInMinutes($user->locked_until);
            return $this->errorResponse('ACCOUNT_LOCKED', "Compte verrouille. Reessayez dans {$minutes} minutes.", 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            $user->increment('failed_login_attempts');

            if ($user->failed_login_attempts >= 3) {
                $user->update(['locked_until' => now()->addMinutes(15)]);
                $this->notifyAdminsAccountLocked($user);
                return $this->errorResponse('ACCOUNT_LOCKED', 'Compte verrouille suite a 3 tentatives echouees. Reessayez dans 15 minutes.', 403);
            }

            return $this->errorResponse('INVALID_CREDENTIALS', 'Identifiants incorrects.', 401);
        }

        // Reset failed attempts and update last login
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        $token = $user->createToken('api-token', ['*'], now()->addHours(24));

        $this->auditService->log('login', 'auth', $user->id, $request, 200, 'api', $user->id);

        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_in' => 86400,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'organization' => $user->organization,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'region' => $user->region,
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->auditService->log('logout', 'auth', $user->id, $request, 204, 'api', $user->id);

        $user->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'organization' => $user->organization,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'region' => $user->region,
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    private function notifyAdminsAccountLocked(User $lockedUser): void
    {
        $admins = User::where('role', 'admin_sodeci')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'account_locked',
                'title' => 'Compte verrouille',
                'message' => "Le compte de {$lockedUser->full_name} ({$lockedUser->username}) a ete verrouille apres 3 tentatives de connexion echouees.",
                'data' => [
                    'locked_user_id' => $lockedUser->id,
                    'locked_user_username' => $lockedUser->username,
                    'locked_until' => $lockedUser->locked_until?->toIso8601String(),
                ],
            ]);
        }
    }

    private function errorResponse(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }
}
