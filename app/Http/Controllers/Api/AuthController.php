<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ثبت‌نام کاربر جدید (اختیاری)
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'city_id' => $request->city_id ?? null,
        ]);

        return response()->json(['message' => 'کاربر با موفقیت ساخته شد', 'user' => $user]);
    }

    // لاگین - نسخه اصلاح شده
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // پیدا کردن کاربر با username
        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['نام کاربری یا رمز عبور اشتباه است.'],
            ]);
        }

        // ایجاد توکن
        $token = $user->createToken('api-token')->plainTextToken;

        // بارگذاری روابط مورد نیاز
        $user->load(['role', 'city']);

        return response()->json([
            'message' => 'ورود موفق',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role_id' => $user->role_id,
                'city_id' => $user->city_id,
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'name' => $user->role->name
                ] : null,
                'city' => $user->city ? [
                    'id' => $user->city->id,
                    'name' => $user->city->name
                ] : null
            ]
        ]);
    }

    // خروج
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'خروج با موفقیت انجام شد']);
    }

    // اطلاعات کاربر
    public function me(Request $request)
    {
        $user = $request->user()->load(['role', 'city']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role_id' => $user->role_id,
                'city_id' => $user->city_id,
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'name' => $user->role->name
                ] : null,
                'city' => $user->city ? [
                    'id' => $user->city->id,
                    'name' => $user->city->name
                ] : null
            ]
        ]);
    }
}
