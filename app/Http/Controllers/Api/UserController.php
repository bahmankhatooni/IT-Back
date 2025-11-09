<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // گرفتن کاربر
    private function getUser()
    {
        return auth()->user() ?? User::find(1);
    }

    // نمایش همه کاربران
    public function index(Request $request)
    {
        $user = $this->getUser();

        // ارتباط با جدول city برای نمایش نام حوزه
        $query = User::with(['city']);

        // جستجو بر اساس نام، نام خانوادگی، نام کاربری یا ایمیل
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                    ->orWhere('lname', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->get()->makeHidden(['password', 'remember_token']);
        return response()->json($users);
    }

    // نمایش جزئیات یک کاربر خاص
    public function show($id)
    {
        $user = User::with(['city'])->findOrFail($id);
        return response()->json($user->makeHidden(['password', 'remember_token']));
    }

    // ذخیره کاربر جدید
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'fname' => 'required|string|max:100',
            'lname' => 'required|string|max:100',
            'username' => [
                'required',
                'string',
                'max:100',
                'unique:users,username'
            ],
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'role_id' => 'required|in:1,2',
            'city_id' => 'required|exists:cities,id',
        ], [
            'fname.required' => 'فیلد "نام" الزامی است.',
            'lname.required' => 'فیلد "نام خانوادگی" الزامی است.',
            'username.required' => 'فیلد "نام کاربری" الزامی است.',
            'username.unique' => 'این نام کاربری قبلاً ثبت شده است.',
            'password.required' => 'فیلد "کلمه عبور" الزامی است.',
            'password.min' => 'کلمه عبور باید حداقل 6 کاراکتر باشد.',
            'role_id.required' => 'فیلد "نقش کاربری" الزامی است.',
            'city_id.required' => 'فیلد "حوزه قضایی" الزامی است.',
        ]);

        // هش کردن رمز عبور
        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        return response()->json([
            'message' => 'کاربر با موفقیت ثبت شد.',
            'user' => $user->makeHidden(['password', 'remember_token'])
        ]);
    }

    // ویرایش اطلاعات کاربر
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'fname' => 'required|string|max:100',
            'lname' => 'required|string|max:100',
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'role_id' => 'required|in:1,2',
            'city_id' => 'required|exists:cities,id',
        ], [
            'fname.required' => 'فیلد "نام" الزامی است.',
            'lname.required' => 'فیلد "نام خانوادگی" الزامی است.',
            'username.required' => 'فیلد "نام کاربری" الزامی است.',
            'username.unique' => 'این نام کاربری قبلاً ثبت شده است.',
            'password.min' => 'کلمه عبور باید حداقل 6 کاراکتر باشد.',
            'role_id.required' => 'فیلد "نقش کاربری" الزامی است.',
            'city_id.required' => 'فیلد "حوزه قضایی" الزامی است.',
        ]);

        // اگر رمز عبور جدید وارد شده، آن را هش کن
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            // اگر رمز عبور وارد نشده، آن را از داده‌های validated حذف کن
            unset($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json([
            'message' => 'کاربر با موفقیت ویرایش شد.',
            'user' => $user->makeHidden(['password', 'remember_token'])
        ]);
    }

    // حذف کاربر
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // جلوگیری از حذف خود کاربر
        $currentUser = $this->getUser();
        if ($user->id === $currentUser->id) {
            return response()->json([
                'message' => 'شما نمی‌توانید حساب کاربری خود را حذف کنید.'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'کاربر با موفقیت حذف شد.'
        ]);
    }

    // در UserController.php - اضافه کردن متدهای جدید

    /**
     * نمایش اطلاعات کاربر جاری
     */
    public function profile()
    {
        $user = Auth::user();
        $user->load('city');
        return response()->json($user);
    }

    /**
     * بروزرسانی پروفایل کاربر جاری
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'پروفایل با موفقیت بروزرسانی شد',
            'user' => $user->load('city')
        ]);
    }

    /**
     * تغییر رمز عبور کاربر جاری
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => bcrypt($validated['new_password'])
        ]);

        return response()->json([
            'message' => 'رمز عبور با موفقیت تغییر کرد'
        ]);
    }
}
