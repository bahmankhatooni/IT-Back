<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scanner;
use App\Models\City;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    // گرفتن کاربر تستی برای مواقعی که auth فعال نیست
    private function getUser()
    {
        return auth()->user() ?? \App\Models\User::find(1);
    }

    // نمایش همه اسکنرها (با در نظر گرفتن نقش و جستجو)
    public function index(Request $request)
    {
        $user = $this->getUser();

        // ارتباط با جدول‌های مرتبط
        $query = Scanner::with(['branch', 'city']);

        // اگر کاربر city_user است فقط اسکنرهای حوزه خودش را ببیند
        if ($user->role_id != 1) {
            $query->where('city_id', $user->city_id);
        }

        // جستجو بر اساس مدل اسکنر یا مشخصات
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $scanners = $query->get();
        return response()->json($scanners);
    }

    // ذخیره اسکنر جدید
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'model' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|string|max:50',
        ], [
            'model.required' => 'فیلد "مدل اسکنر" الزامی است.',
            'city_id.required' => 'فیلد "حوزه قضایی" الزامی است.',
            'branch_id.required' => 'فیلد "شعبه" الزامی است.',
            'quantity.required' => 'فیلد "تعداد" الزامی است.',
            'quantity.min' => 'تعداد باید حداقل 1 باشد.',
        ]);

        $scanner = Scanner::create($validatedData);

        return response()->json([
            'message' => 'اسکنر با موفقیت ثبت شد.',
            'scanner' => $scanner
        ]);
    }

    // نمایش جزئیات یک اسکنر خاص
    public function show($id)
    {
        $scanner = Scanner::with(['city', 'branch'])->findOrFail($id);
        return response()->json($scanner);
    }

    // ویرایش اطلاعات اسکنر
    public function update(Request $request, $id)
    {
        $scanner = Scanner::findOrFail($id);

        $validatedData = $request->validate([
            'model' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|string|max:50',
        ], [
            'model.required' => 'فیلد "مدل اسکنر" الزامی است.',
            'city_id.required' => 'فیلد "حوزه قضایی" الزامی است.',
            'branch_id.required' => 'فیلد "شعبه" الزامی است.',
            'quantity.required' => 'فیلد "تعداد" الزامی است.',
            'quantity.min' => 'تعداد باید حداقل 1 باشد.',
        ]);

        $scanner->update($validatedData);

        return response()->json([
            'message' => 'اسکنر با موفقیت ویرایش شد.',
            'scanner' => $scanner
        ]);
    }

    // حذف اسکنر
    public function destroy($id)
    {
        $scanner = Scanner::findOrFail($id);
        $scanner->delete();

        return response()->json([
            'message' => 'اسکنر با موفقیت حذف شد.'
        ]);
    }
}
