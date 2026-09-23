<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function show(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * The login field accepts either an email address or a username (name).
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'login.required' => 'Kredensial tidak boleh kosong.',
            'password.required' => 'Password tidak boleh kosong.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $username = trim($data['login']);

        $credentials = [
            'password' => $data['password'],
        ];

        if (Str::contains($username, '@')) {
            // Login with email address (case-insensitive across databases).
            $credentials['email'] = Str::lower($username);
        } else {
            // Login with username (the user's name).
            $credentials['name'] = $username;
        }

        // Checkbox "Ingat saya" mengirim value "1" (bukan "on"), sehingga
        // boolean() dipakai agar remember-me benar-benar aktif.
        if (auth()->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return redirect()
            ->route('login')
            ->withInput(['login' => $username])
            ->withErrors(['login' => 'Email/username atau password tidak sesuai. Silakan periksa kembali.']);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(): RedirectResponse
    {
        auth()->logout();

        return redirect()->route('login');
    }
}