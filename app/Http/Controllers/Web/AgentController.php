<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PrintAgent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
    public function index(): View
    {
        $tenantId = Auth::user()?->tenant_id;

        return view('portal.agents', [
            'agents' => PrintAgent::query()->where('tenant_id', $tenantId)->orderByDesc('last_seen_at')->get(),
        ]);
    }
}
