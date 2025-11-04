<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Computer;
use App\Models\City;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;

class ComputerController extends Controller
{
    // گرفتن کاربر تستی برای مواقعی که auth فعال نیست
    private function getUser()
    {
        return auth()->user() ?? \App\Models\User::find(1);
    }

    // نمایش همه سخت‌افزارها (با در نظر گرفتن نقش و جستجو)
    public function index(Request $request)
    {
        $user = $this->getUser();

        // ارتباط با جدول‌های مرتبط
        $query = Computer::with(['branch', 'city', 'employee']);

        // اگر کاربر city_user است فقط سخت‌افزارهای حوزه خودش را ببیند
        if ($user->role_id != 1) {
            $query->where('city_id', $user->city_id);
        }

        // جستجو بر اساس نام دستگاه یا مشخصات
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mb', 'like', "%{$search}%")
                    ->orWhere('cpu', 'like', "%{$search}%")
                    ->orWhere('ram', 'like', "%{$search}%")
                    ->orWhere('os', 'like', "%{$search}%");
            });
        }

        $computers = $query->get();
        return response()->json($computers);
    }

    // ذخیره سخت‌افزار جدید
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'branch_id' => 'required|exists:branches,id',
            'employee_id' => 'required|exists:employees,id',
            'monitor' => 'nullable|string|max:100',
            'mb' => 'nullable|string|max:100',
            'cpu' => 'nullable|string|max:100',
            'ram' => 'nullable|string|max:50',
            'hard' => 'required|in:0,1',
            'os' => 'nullable|string|max:100',
            'antivirus' => 'required|in:0,1',
        ], [
            'name.required' => 'فیلد "نام دستگاه" الزامی است.',
            'city_id.required' => 'فیلد "حوزه قضایی" الزامی است.',
            'branch_id.required' => 'فیلد "شعبه" الزامی است.',
            'employee_id.required' => 'فیلد "کارمند" الزامی است.',
        ]);

        $computer = Computer::create($validatedData);

        return response()->json([
            'message' => 'سخت‌افزار با موفقیت ثبت شد.',
            'computer' => $computer
        ]);
    }

    // نمایش جزئیات یک سخت‌افزار خاص
    public function show($id)
    {
        $computer = Computer::with(['city', 'branch', 'employee'])->findOrFail($id);
        return response()->json($computer);
    }

    // ویرایش اطلاعات سخت‌افزار
    public function update(Request $request, $id)
    {
        $computer = Computer::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'branch_id' => 'required|exists:branches,id',
            'employee_id' => 'required|exists:employees,id',
            'monitor' => 'nullable|string|max:100',
            'mb' => 'nullable|string|max:100',
            'cpu' => 'nullable|string|max:100',
            'ram' => 'nullable|string|max:50',
            'hard' => 'required|in:0,1',
            'os' => 'nullable|string|max:100',
            'antivirus' => 'required|in:0,1',
        ], [
            'name.required' => 'فیلد "نام دستگاه" الزامی است.',
            'city_id.required' => 'فیلد "حوزه قضایی" الزامی است.',
            'branch_id.required' => 'فیلد "شعبه" الزامی است.',
            'employee_id.required' => 'فیلد "کارمند" الزامی است.',
        ]);

        $computer->update($validatedData);

        return response()->json([
            'message' => 'سخت‌افزار با موفقیت ویرایش شد.',
            'computer' => $computer
        ]);
    }

    // حذف سخت‌افزار
    public function destroy($id)
    {
        $computer = Computer::findOrFail($id);
        $computer->delete();

        return response()->json([
            'message' => 'سخت‌افزار با موفقیت حذف شد.'
        ]);
    }
}
