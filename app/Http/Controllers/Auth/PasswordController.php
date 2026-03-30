<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ],
            [
                'current_password.required' => 'Mevcut şifrenizi girin.',
                'current_password.current_password' => 'Mevcut şifre doğru değil.',
                'password.required' => 'Yeni şifreyi girin.',
                'password.confirmed' => 'Yeni şifre ile tekrarı eşleşmiyor.',
                'password.min' => 'Yeni şifre en az :min karakter olmalıdır.',
            ],
        );

        // Modelde `password` => `hashed` cast: düz metin verin; Hash::make burada tekrarlanmasın.
        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Şifreniz güncellendi.');
    }
}
