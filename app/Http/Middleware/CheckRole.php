<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'کاربر احراز هویت نشده است'], 401);
        }

        // دریافت نام نقش کاربر از جدول roles
        $roleName = DB::table('roles')->where('id', $user->role_id)->value('name');

        // اگر نقشی پیدا نشد
        if (!$roleName) {
            return response()->json(['message' => 'نقش کاربر یافت نشد'], 403);
        }

        // بررسی وجود نقش در پارامترهای مجاز
        if (!in_array($roleName, $roles)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        return $next($request);
    }
}
