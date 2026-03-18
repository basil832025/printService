<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PrintAgent;
use App\Models\PrintApiKey;
use App\Models\PrintJob;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();
        $tenantId = $user?->tenant_id;

        $stats = [
            'queued' => PrintJob::query()->where('tenant_id', $tenantId)->whereIn('status', ['queued', 'reserved', 'retry_wait'])->count(),
            'printed' => PrintJob::query()->where('tenant_id', $tenantId)->where('status', 'printed')->count(),
            'failed' => PrintJob::query()->where('tenant_id', $tenantId)->where('status', 'failed')->count(),
            'agents_online' => PrintAgent::query()->where('tenant_id', $tenantId)->where('status', 'online')->count(),
        ];

        $recentJobs = PrintJob::query()
            ->where('tenant_id', $tenantId)
            ->latest('created_at')
            ->limit(10)
            ->get();

        $apiKeys = PrintApiKey::query()
            ->where('tenant_id', $tenantId)
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('portal.dashboard', [
            'stats' => $stats,
            'recentJobs' => $recentJobs,
            'apiKeys' => $apiKeys,
        ]);
    }
}
