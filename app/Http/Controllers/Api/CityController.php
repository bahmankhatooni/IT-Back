<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use Illuminate\Validation\ValidationException;

class CityController extends Controller
{
    // نمایش همه شهرها (فقط auth:sanctum روی روت اعمال می‌شود، نه اینجا)
    public function index(Request $request)
    {
        $query = City::query();

        if ($request->has('search') && $request->search !== '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->get());
    }


    // ایجاد شهر جدید
    public function store(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|unique:cities,code',
                'name' => 'required',
            ], [
                'code.required' => 'فیلد "کد حوزه" الزامی است.',
                'code.unique' => 'کد وارد شده تکراری است.',
                'name.required' => 'فیلد "نام حوزه" الزامی است.',
            ]);

            $city = City::create([
                'code' => $request->code,
                'name' => $request->name,
            ]);

            return response()->json([
                'message' => 'حوزه با موفقیت ثبت شد.',
                'city' => $city,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // بروزرسانی شهر
    public function update(Request $request, $id)
    {
        $city = City::findOrFail($id);

        try {
            $request->validate([
                'code' => 'required|unique:cities,code,' . $city->id,
                'name' => 'required',
            ], [
                'code.required' => 'فیلد "کد حوزه" الزامی است.',
                'code.unique' => 'کد وارد شده تکراری است.',
                'name.required' => 'فیلد "نام حوزه" الزامی است.',
            ]);

            $city->update([
                'code' => $request->code,
                'name' => $request->name,
            ]);

            return response()->json([
                'message' => 'اطلاعات حوزه با موفقیت بروزرسانی شد.',
                'city' => $city,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // حذف شهر
    public function destroy($id)
    {
        $city = City::findOrFail($id);
        $city->delete();

        return response()->json(['message' => 'حوزه با موفقیت حذف شد.']);
    }
}
