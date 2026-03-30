<?php

namespace App\Http\Controllers;

use App\Services\ReportAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportAnalyticsService $reportAnalytics,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Reports/Index', [
            'analytics' => $this->reportAnalytics->build($request),
        ]);
    }
}
