<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function __construct(private AuditService $auditService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && !$request->isMethod('GET')) {
            $this->auditService->log(
                action: $this->resolveAction($request),
                entityType: $this->resolveEntityType($request),
                entityId: $this->resolveEntityId($request),
                request: $request,
                responseStatus: $response->getStatusCode(),
            );
        }

        return $response;
    }

    private function resolveAction(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        if (str_contains($path, 'login')) return 'login';
        if (str_contains($path, 'logout')) return 'logout';
        if (str_contains($path, 'validate')) return 'validate_vehicle';
        if (str_contains($path, 'reject')) return 'reject_vehicle';
        if (str_contains($path, 'sync')) return 'sync_vehicle';
        if (str_contains($path, 'media/upload')) return 'upload_photo';
        if (str_contains($path, 'financial')) return 'update_financial';

        return match ($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => strtolower($method),
        };
    }

    private function resolveEntityType(Request $request): ?string
    {
        $path = $request->path();

        // Specific matches first (order matters: check longer paths before shorter)
        if (str_contains($path, 'vehicle-types')) return 'vehicle_type';
        if (str_contains($path, 'vehicle-categories')) return 'vehicle_category';
        if (str_contains($path, 'vehicle-statuses')) return 'vehicle_status';
        if (str_contains($path, 'fuel-types')) return 'fuel_type';
        if (str_contains($path, 'transmissions')) return 'transmission';
        if (str_contains($path, 'contract-types')) return 'contract_type';
        if (str_contains($path, 'insurance-companies') || str_contains($path, 'insurances')) return 'insurance_company';
        if (str_contains($path, 'colors')) return 'color';
        if (str_contains($path, 'vehicles') || str_contains($path, 'sodeci/vehicles')) return 'vehicle';
        if (str_contains($path, 'media')) return 'photo';
        if (str_contains($path, 'users')) return 'user';
        if (str_contains($path, 'brands')) return 'brand';
        if (str_contains($path, 'models')) return 'vehicle_model';
        if (str_contains($path, 'structures')) return 'structure';
        if (str_contains($path, 'directions')) return 'direction';
        if (str_contains($path, 'notifications')) return 'notification';
        if (str_contains($path, 'login') || str_contains($path, 'logout') || str_contains($path, 'auth')) return 'auth';

        return null;
    }

    private function resolveEntityId(Request $request): ?string
    {
        // Try all common route parameter names
        $params = ['vehicle', 'brand', 'model', 'structure', 'company',
                    'insurance', 'direction', 'user', 'photo', 'id'];

        foreach ($params as $param) {
            $value = $request->route($param);
            if ($value !== null) {
                return (string) $value;
            }
        }

        return null;
    }
}
