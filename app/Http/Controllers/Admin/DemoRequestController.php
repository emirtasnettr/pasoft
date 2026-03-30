<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DemoRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $demos = DemoRequest::query()
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/DemoRequests/Index', [
            'demoRequests' => $demos,
        ]);
    }
}
