<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * 📋 لیست تمام کاربران همراه با نقش
     * GET /api/users
     */
    public function index()
    {
        // واکشی کاربران به همراه نقش مربوطه
        $users = User::with('role')->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $users
        ], 200);
    }

    /**
     * 👤 نمایش جزئیات یک کاربر خاص
     * GET /api/users/{id}
     */
    public function show($id)
    {
        $user = User::with('role')->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'کاربر مورد نظر یافت نشد'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user
        ], 200);
    }

    /**
     * ➕ ایجاد کاربر جدید
     * POST /api/users
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fname' => 'required|string|max:100',
            'lname' => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|integer|exists:roles,id', // ✅ نقش باید از جدول roles وجود داشته باشد
            'email' => 'required|email|unique:users',
        ]);

        // رمزنگاری پسورد
        $validated['password'] = Hash::make($validated['password']);

        // ایجاد کاربر
        $user = User::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'کاربر با موفقیت ایجاد شد',
            'data' => $user->load('role')
        ], 201);
    }

    /**
     * ✏️ ویرایش اطلاعات کاربر
     * PUT /api/users/{id}
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'کاربر یافت نشد'
            ], 404);
        }

        $validated = $request->validate([
            'fname' => 'sometimes|required|string|max:100',
            'lname' => 'sometimes|required|string|max:100',
            'username' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'sometimes|required|integer|exists:roles,id',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'اطلاعات کاربر با موفقیت ویرایش شد',
            'data' => $user->load('role')
        ], 200);
    }

    /**
     * 🗑️ حذف کاربر
     * DELETE /api/users/{id}
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'کاربر یافت نشد'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'کاربر با موفقیت حذف شد'
        ], 200);
    }
}
