<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActionLogController extends Controller
{
    /**
     * Display a listing of the action logs.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $methodFilter = strtoupper((string) $request->input('method', ''));

        $logsQuery = ActionLog::query()
            ->with('admin')
            ->latest();

        if ($search !== '') {
            $logsQuery->where(function ($query) use ($search) {
                $query->where('content', 'like', '%' . $search . '%')
                    ->orWhereHas('admin', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        if (in_array($methodFilter, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $logsQuery->where('context->method', $methodFilter);
        }

        $logs = $logsQuery->paginate(50)->withQueryString();

        return view('admin.action_logs.index', [
            'logs' => $logs,
            'filters' => [
                'search' => $search,
                'method' => $methodFilter,
            ],
        ]);
    }
}
