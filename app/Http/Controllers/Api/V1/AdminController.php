<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->has('role')) $query->where('role', $request->role);
        if ($request->has('organization')) $query->where('organization', $request->organization);
        if ($request->has('is_active')) $query->where('is_active', $request->boolean('is_active'));

        $users = $query->orderBy('created_at', 'desc')->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $users->map(fn($u) => [
                'id' => $u->id,
                'username' => $u->username,
                'email' => $u->email,
                'first_name' => $u->first_name,
                'last_name' => $u->last_name,
                'role' => $u->role,
                'organization' => $u->organization,
                'region' => $u->region,
                'is_active' => $u->is_active,
                'last_login_at' => $u->last_login_at,
                'created_at' => $u->created_at,
            ]),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'total_pages' => $users->lastPage(),
                'total_items' => $users->total(),
            ],
        ]);
    }

    public function storeUser(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

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
                'region' => $user->region,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
            ],
            'message' => 'Utilisateur cree avec succes',
        ], 201);
    }

    public function updateUser(UpdateUserRequest $request, string $user): JsonResponse
    {
        $u = User::findOrFail($user);
        $u->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $u->fresh(),
            'message' => 'Utilisateur mis a jour avec succes',
        ]);
    }

    public function auditLogs(Request $request): JsonResponse
    {
        $query = AuditLog::with('user');

        if ($request->has('user_id')) $query->where('user_id', $request->user_id);
        if ($request->has('action')) $query->where('action', $request->action);
        if ($request->has('entity_type')) $query->where('entity_type', $request->entity_type);
        if ($request->has('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->has('date_to')) $query->whereDate('created_at', '<=', $request->date_to);

        $limit = min((int) $request->get('limit', 50), 100);
        $logs = $query->orderByDesc('created_at')->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $logs->map(fn($l) => [
                'id' => $l->id,
                'user' => $l->user ? [
                    'id' => $l->user->id,
                    'username' => $l->user->username,
                ] : null,
                'action' => $l->action,
                'entity_type' => $l->entity_type,
                'entity_id' => $l->entity_id,
                'ip_address' => $l->ip_address,
                'created_at' => $l->created_at,
            ]),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'total_pages' => $logs->lastPage(),
                'total_items' => $logs->total(),
                'items_per_page' => $logs->perPage(),
            ],
        ]);
    }
}
