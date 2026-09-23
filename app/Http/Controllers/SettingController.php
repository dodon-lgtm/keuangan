<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    /**
     * Panjang minimal password baru — dipakai juga oleh indikator di modal.
     */
    public const PASSWORD_MIN_LENGTH = 8;

    /**
     * Update the authenticated user's password (modal pengaturan).
     *
     * The modal submits with fetch() (Accept: application/json), so errors are
     * returned as JSON and can be rendered inside the modal without closing it.
     * Plain form posts (JS disabled) keep working through redirect + flash.
     */
    public function updatePassword(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:'.self::PASSWORD_MIN_LENGTH, 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal '.self::PASSWORD_MIN_LENGTH.' karakter.',
            'password.confirmed' => 'Password belum cocok.',
        ]);

        $user = $request->user();

        // Password lama diperiksa dengan Hash::check (mendukung hash apa pun
        // yang tersimpan di kolom users.password).
        if (! Hash::check($data['current_password'], (string) $user->password)) {
            return $this->passwordError($request, 'current_password', 'Password saat ini salah.');
        }

        // Password baru selalu di-hash. Cast `hashed` pada App\Models\User
        // mengenali nilai yang sudah ter-hash (Hash::isHashed), jadi tidak
        // terjadi double-hash dan login berikutnya tetap cocok.
        $user->forceFill(['password' => Hash::make($data['password'])])->save();

        // Cegah session fixation setelah kredensial berubah.
        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diperbarui.',
                'csrf_token' => csrf_token(),
            ]);
        }

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    /**
     * Send a field level error either as JSON (modal, no reload) or as a
     * redirect back with the error bag (progressive enhancement fallback).
     *
     * The plain fallback also flashes the field/message as simple strings
     * ("password_update_error") because ViewErrorBag round-trips through
     * JSON session serialization can arrive at the next view still empty;
     * a scalar flash always survives the redirect for the no-JS flow.
     */
    protected function passwordError(Request $request, string $field, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => [$field => [$message]],
            ], 422);
        }

        return back()
            ->withErrors([$field => $message])
            ->with('password_update_error', ['field' => $field, 'message' => $message]);
    }
}

