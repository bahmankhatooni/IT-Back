<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class BranchController extends Controller
{
    // گرفتن کاربر تستی برای مواقعی که auth فعال نیست
    private function getUser()
    {
        // اگر auth فعال باشه کاربر واقعی برگرده، وگرنه کاربر با id=1
        return auth()->user() ?? User::find(1);
    }

    // نمایش همه شعبات مربوط به حوزه کاربر با نام حوزه
    public function index(Request $request)
    {
        $user = $this->getUser();
        $query = Branch::with('city');

        // اگر کاربر city_user است، فقط شعب شهر خودش را ببیند
        if ($user->role_id != 1) {
            $query->where('city_id', $user->city_id);
        }
        else if ($user->role_id == 1 AND $request->city_id!=null) {
            $query->where('city_id', $request->city_id);
        }

        // جستجو بر اساس نام یا کد شعبه
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $branches = $query->get();
        return response()->json($branches);
    }


    // ایجاد شعبه جدید
    public function store(Request $request)
    {
        $user = $this->getUser();

        try {
            $request->validate([
                'code' => 'required|unique:branches,code',
                'name' => 'required',
            ], [
                'code.required' => 'فیلد "کد شعبه" الزامی است.',
                'code.unique' => 'کد وارد شده تکراری است.',
                'name.required' => 'فیلد "نام شعبه" الزامی است.',
            ]);
            if ($user->role_id!== 1)
                $city = $user->city_id;
            else
                $city = $request->city_id;
            $branch = Branch::create([
                'code' => $request->code,
                'name' => $request->name,
                'city_id' => $city,
            ]);

            // لود رابطه city برای نمایش نام حوزه بلافاصله
            $branch->load('city');

            return response()->json([
                'message' => 'شعبه با موفقیت ثبت شد.',
                'branch' => $branch,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // بروزرسانی شعبه
    public function update(Request $request, $id)
    {
        $user = $this->getUser();
        if ($user->role_id!== 1) {
            $branch = Branch::where('city_id', $user->city_id)->findOrFail($id);
            $city = $user->city_id;
        }
        else {
            $branch = Branch::findOrFail($id);
            $city = $request->city_id;
        }

        try {
            $request->validate([
                'code' => 'required|unique:branches,code,' . $branch->id,
                'name' => 'required',
            ], [
                'code.required' => 'فیلد "کد شعبه" الزامی است.',
                'code.unique' => 'کد وارد شده تکراری است.',
                'name.required' => 'فیلد "نام شعبه" الزامی است.',
            ]);

            $branch->update([
                'code' => $request->code,
                'name' => $request->name,
                'city_id' => $city,
            ]);

            // لود رابطه city برای نمایش نام حوزه بلافاصله
            $branch->load('city');

            return response()->json([
                'message' => 'اطلاعات شعبه با موفقیت بروزرسانی شد.',
                'branch' => $branch,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // حذف شعبه
    public function destroy($id)
    {
        $user = $this->getUser();
        $branch = Branch::where('city_id', $user->city_id)->findOrFail($id);
        $branch->delete();

        return response()->json(['message' => 'شعبه با موفقیت حذف شد.']);
    }
}
