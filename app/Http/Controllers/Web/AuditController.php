<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');
        if ($request->filled('action')) $query->where('action', $request->action);
        if ($request->filled('entity_type')) $query->where('entity_type', $request->entity_type);
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('source')) $query->where('source', $request->source);

        $logs = $query->orderByDesc('created_at')->paginate(50)->withQueryString();
        $actions = AuditLog::distinct()->pluck('action')->sort();
        $entityTypes = AuditLog::distinct()->whereNotNull('entity_type')->pluck('entity_type')->sort();

        return view('audit.index', compact('logs', 'actions', 'entityTypes'));
    }

    /**
     * Export audit logs as CSV with current filters applied (US-032).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = AuditLog::with('user');
        if ($request->filled('action')) $query->where('action', $request->action);
        if ($request->filled('entity_type')) $query->where('entity_type', $request->entity_type);
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('source')) $query->where('source', $request->source);

        $filename = 'RIMA_AUDIT_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'Date',
                'Heure',
                'Utilisateur',
                'Role',
                'Source',
                'Action',
                'Type entite',
                'ID entite',
                'Adresse IP',
                'Donnees',
            ], ';');

            // Stream data in chunks
            $query->orderByDesc('created_at')->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->created_at->format('d/m/Y'),
                        $log->created_at->format('H:i:s'),
                        $log->user->full_name ?? '---',
                        $log->user->role ?? '',
                        $log->source ?? '---',
                        $log->action,
                        $log->entity_type ?? '',
                        $log->entity_id ?? '',
                        $log->ip_address ?? '',
                        $log->request_body ? json_encode($log->request_body, JSON_UNESCAPED_UNICODE) : '',
                    ], ';');
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
