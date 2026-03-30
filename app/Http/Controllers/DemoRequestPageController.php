<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemoRequestRequest;
use App\Models\DemoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class DemoRequestPageController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('DemoRequest', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function store(StoreDemoRequestRequest $request): RedirectResponse
    {
        DemoRequest::query()->create($request->validated());

        return redirect()
            ->route('demo.request')
            ->with('success', 'Demo talebiniz alındı. En kısa sürede sizinle iletişime geçeceğiz.');
    }
}
