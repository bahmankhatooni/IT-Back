<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use App\Models\City;
use App\Models\Branch;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    // گرفتن کاربر تستی برای مواقعی که auth فعال نیست
    private function getUser()
    {
        return auth()->user() ?? \App\Models\User::find(1);
    }

    // نمایش همه پرینترها (با در نظر گرفتن نقش و جستجو)
    public function index(Request $request)
    {
        $user = $this->getUser();

        // ارتباط با جدول‌های مرتبط
        $query = Printer::with(['branch', 'city']);

        // اگر کاربر city_user است فقط پرینترهای حوزه خودش را ببیند
        if ($user->role_id != 1) {
            $query->where('city_id', $user->city_id);
        }

        // جستجو بر اساس مدل پرینتر
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $printers = $query->get();
        return response()->json($printers);
    }

    // ذخیره پرینتر جدید
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'model' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:1',
            'ip_address' => 'nullable|ip',
            'type' => 'required|string|max:50',
            'is_color' => 'required|in:0,1',
            'is_network' => 'required|in:0,1',
        ], [
            'model.required' => 'فیلد "مدل پرینتر" الزامی است.',
            'city_id.required' => 'فیلد "حوزه قضایی" الزامی است.',
            'branch_id.required' => 'فیلد "شعبه" الزامی است.',
            'quantity.required' => 'فیلد "تعداد" الزامی است.',
            'quantity.min' => 'تعداد باید حداقل 1 باشد.',
        ]);

        $printer = Printer::create($validatedData);

        return response()->json([
            'message' => 'پرینتر با موفقیت ثبت شد.',
            'printer' => $printer
        ]);
    }

    // نمایش جزئیات یک پرینتر خاص
    public function show($id)
    {
        $printer = Printer::with(['city', 'branch'])->findOrFail($id);
        return response()->json($printer);
    }

    // ویرایش اطلاعات پرینتر
    public function update(Request $request, $id)
    {
        $printer = Printer::findOrFail($id);

        $validatedData = $request->validate([
            'model' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:1',
            'ip_address' => 'nullable|ip',
            'type' => 'required|string|max:50',
            'is_color' => 'required|in:0,1',
            'is_network' => 'required|in:0,1',
        ], [
            'model.required' => 'فیلد "مدل پرینتر" الزامی است.',
            'city_id.required' => 'فیلد "حوزه قضایی" الزامی است.',
            'branch_id.required' => 'فیلد "شعبه" الزامی است.',
            'quantity.required' => 'فیلد "تعداد" الزامی است.',
            'quantity.min' => 'تعداد باید حداقل 1 باشد.',
        ]);

        $printer->update($validatedData);

        return response()->json([
            'message' => 'پرینتر با موفقیت ویرایش شد.',
            'printer' => $printer
        ]);
    }

    // حذف پرینتر
    public function destroy($id)
    {
        $printer = Printer::findOrFail($id);
        $printer->delete();

        return response()->json([
            'message' => 'پرینتر با موفقیت حذف شد.'
        ]);
    }
}
