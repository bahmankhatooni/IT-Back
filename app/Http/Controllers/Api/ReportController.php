<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Computer;
use App\Models\City;
use App\Models\Branch;

class ReportController extends Controller
{
    public function getComputers(Request $request)
    {
        try {
            // دریافت پارامترهای فیلتر
            $filters = $request->only(['city_id', 'branch_id', 'search']);

            // شروع کوئری
            $query = Computer::with([
                'city:id,name',
                'branch:id,name',
                'employee:id,fname,lname'
            ]);

            // اعمال فیلتر شهر
            if (!empty($filters['city_id'])) {
                $query->where('city_id', $filters['city_id']);
            }

            // اعمال فیلتر شعبه
            if (!empty($filters['branch_id'])) {
                $query->where('branch_id', $filters['branch_id']);
            }

            // اعمال جستجو
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('os', 'like', "%{$search}%")
                        ->orWhere('ram', 'like', "%{$search}%")
                        ->orWhere('cpu', 'like', "%{$search}%");
                });
            }

            // دریافت داده‌ها
            $computers = $query->orderBy('id', 'desc')->get();

            // تبدیل به فرمت مناسب
            $data = $computers->map(function($computer) {
                return [
                    'id' => $computer->id,
                    'name' => $computer->name,
                    'employee_name' => $computer->employee ?
                        $computer->employee->fname . ' ' . $computer->employee->lname : '-',
                    'branch_name' => $computer->branch->name ?? '-',
                    'city_name' => $computer->city->name ?? '-',
                    'os' => $computer->os,
                    'ram' => $computer->ram,
                    'cpu' => $computer->cpu,
                    'monitor' => $computer->monitor,
                    'mb' => $computer->mb,
                    'antivirus' => (bool)$computer->antivirus,
                    'hard' => (bool)$computer->hard,
                    'created_at' => $computer->created_at?->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => $computers->count(),
                'message' => 'لیست کامپیوترها با موفقیت دریافت شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت لیست کامپیوترها',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // دریافت لیست شهرها و شعبات برای فیلتر
    public function getFilterOptions()
    {
        try {
            $cities = City::select('id', 'name')->get();
            $branches = Branch::select('id', 'name')->get();

            return response()->json([
                'success' => true,
                'cities' => $cities,
                'branches' => $branches
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت گزینه‌های فیلتر'
            ], 500);
        }
    }
}
