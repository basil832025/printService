<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = Auth::user()?->tenant_id;
        $status = (string) $request->query('status', '');

        $jobs = PrintJob::query()
            ->where('tenant_id', $tenantId)
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('portal.jobs', [
            'jobs' => $jobs,
            'status' => $status,
        ]);
    }
}
