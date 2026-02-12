<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Exports\AuditLogsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
     * Export audit logs as Excel with current filters applied (US-032).
     */
    public function export(Request $request)
    {
        $filters = $request->only(['action', 'entity_type', 'user_id', 'date_from', 'date_to', 'source']);
        $filename = 'RIMA_AUDIT_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new AuditLogsExport($filters), $filename);
    }
}
