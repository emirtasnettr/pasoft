<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSystemUserRequest;
use App\Http\Requests\Admin\UpdateSystemUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SystemUserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $users = User::query()
            ->with('role:id,name,slug')
            ->when($search !== '', function ($q) use ($search): void {
                $s = '%'.$search.'%';
                $q->where(function ($w) use ($s): void {
                    $w->where('name', 'like', $s)
                        ->orWhere('email', 'like', $s);
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => Role::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(StoreSystemUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['email_verified_at'] = now();
        unset($data['password_confirmation']);

        User::query()->create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Kullanıcı oluşturuldu.');
    }

    public function edit(Request $request, User $user): Response
    {
        $user->load('role:id,name,slug');

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => Role::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'can_edit_role' => $request->user()->id !== $user->id,
        ]);
    }

    public function update(UpdateSystemUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureCanModifyRole($request->user(), $user, (int) $request->validated('role_id'));

        $this->ensureActiveSystemAdminInvariant(
            $user,
            (int) $request->validated('role_id'),
            $request->boolean('is_active', $user->is_active),
        );

        $validated = $request->validated();
        unset($validated['password'], $validated['password_confirmation']);
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->validated('password'));
        }
        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Kullanıcı güncellendi.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        if ($user->isSystemAdmin() && $this->activeSystemAdminCountExcluding($user->id) < 1) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Son aktif sistem yöneticisi silinemez.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Kullanıcı silindi.');
    }

    private function ensureCanModifyRole(User $actor, User $target, int $newRoleId): void
    {
        if ($actor->id !== $target->id) {
            return;
        }

        if ($actor->role_id !== $newRoleId) {
            abort(403, 'Kendi rolünüzü değiştiremezsiniz.');
        }
    }

    private function ensureActiveSystemAdminInvariant(User $target, int $newRoleId, bool $willBeActive): void
    {
        $newRole = Role::query()->find($newRoleId);
        $wasSystemAdmin = $target->isSystemAdmin();
        $willBeSystemAdmin = $newRole?->slug === 'system_admin';

        if ($wasSystemAdmin && (! $willBeSystemAdmin || ! $willBeActive)) {
            if ($this->activeSystemAdminCountExcluding($target->id) < 1) {
                throw ValidationException::withMessages([
                    'role_id' => 'En az bir aktif sistem yöneticisi kalmalıdır.',
                ]);
            }
        }
    }

    private function activeSystemAdminCountExcluding(int $userId): int
    {
        return User::query()
            ->where('id', '!=', $userId)
            ->where('is_active', true)
            ->whereHas('role', fn ($q) => $q->where('slug', 'system_admin'))
            ->count();
    }
}
