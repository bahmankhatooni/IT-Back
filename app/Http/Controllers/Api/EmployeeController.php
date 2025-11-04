<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\City;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;

class EmployeeController extends Controller
{
    // گرفتن کاربر تستی برای مواقعی که auth فعال نیست
    private function getUser()
    {
        return auth()->user() ?? User::find(1);
    }

    // نمایش همه کارکنان (با در نظر گرفتن نقش و جستجو)
    public function index(Request $request)
    {
        $user = $this->getUser();

        // ارتباط با جدول‌های branch و city برای نمایش نام‌ها
        $query = Employee::with(['branch', 'city']);

        // اگر کاربر city_user است فقط کارکنان حوزه خودش را ببیند
        if ($user->role_id != 1) {
            $query->where('city_id', $user->city_id);
        }

        // فیلتر بر اساس شعبه
        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }

        // جستجو بر اساس نام، نام خانوادگی یا کد پرسنلی
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                    ->orWhere('lname', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $employees = $query->get();
        return response()->json($employees);
    }

    // ذخیره کارمند جدید
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = Employee::where('code', $value)
                        ->where('branch_id', $request->branch_id)
                        ->exists();

                    if ($exists) {
                        $fail('این کد پرسنلی در شعبه انتخاب شده قبلاً ثبت شده است.');
                    }
                }
            ],
            'fname' => 'required|string|max:100',
            'lname' => 'required|string|max:100',
            'city_id' => 'required|exists:cities,id',
            'branch_id' => 'required|exists:branches,id',
        ], [
            'code.required' => 'فیلد "کد پرسنلی" الزامی است.',
            'fname.required' => 'فیلد "نام کارمند" الزامی است.',
            'lname.required' => 'فیلد "نام خانوادگی کارمند" الزامی است.',
            'city_id.required' => 'فیلد "نام حوزه" الزامی است.',
            'branch_id.required' => 'فیلد "نام شعبه" الزامی است.',
        ]);

        $employee = Employee::create($validatedData);

        return response()->json([
            'message' => 'کارمند با موفقیت ثبت شد.',
            'employee' => $employee
        ]);
    }

    // نمایش جزئیات یک کارمند خاص
    public function show($id)
    {
        $employee = Employee::with(['city', 'branch'])->findOrFail($id);
        return response()->json($employee);
    }

    // ویرایش اطلاعات کارمند
    public function update(Request $request, Employee $emp, $id)
    {
        $employee = Employee::findOrFail($id);

        $validatedData = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($request, $employee) {
                    $exists = Employee::where('code', $value)
                        ->where('branch_id', $request->branch_id)
                        ->where('id', '!=', $employee->id)
                        ->exists();

                    if ($exists) {
                        $fail('این کد پرسنلی در شعبه انتخاب شده قبلاً ثبت شده است.');
                    }
                }
            ],
            'fname' => 'required|string|max:100',
            'lname' => 'required|string|max:100',
            'city_id' => 'required|exists:cities,id',
            'branch_id' => 'required|exists:branches,id',
        ], [
            'code.required' => 'فیلد "کد پرسنلی" الزامی است.',
            'fname.required' => 'فیلد "نام کارمند" الزامی است.',
            'lname.required' => 'فیلد "نام خانوادگی کارمند" الزامی است.',
            'city_id.required' => 'فیلد "نام حوزه" الزامی است.',
            'branch_id.required' => 'فیلد "نام شعبه" الزامی است.',
        ]);

        $employee->update($validatedData);

        return response()->json([
            'message' => 'کارمند با موفقیت ویرایش شد.',
            'employee' => $employee
        ]);
    }

    // حذف کارمند
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json([
            'message' => 'کارمند با موفقیت حذف شد.'
        ]);
    }

    // برای فرم‌ها - دریافت لیست شهرها و شعب جهت انتخاب
    public function formData()
    {
        $cities = City::all(['id', 'name']);
        $branches = Branch::all(['id', 'name']);

        return response()->json([
            'cities' => $cities,
            'branches' => $branches
        ]);
    }
}
