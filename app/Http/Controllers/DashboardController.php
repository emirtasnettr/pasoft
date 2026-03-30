<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardStatsService $dashboardStats,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => $this->dashboardStats->summary(),
        ]);
    }
}
