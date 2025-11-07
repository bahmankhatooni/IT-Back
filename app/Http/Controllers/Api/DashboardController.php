<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Computer;
use App\Models\Printer;
use App\Models\Scanner;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\City;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // گرفتن کاربر
    private function getUser()
    {
        return auth()->user() ?? \App\Models\User::find(1);
    }

    // آمار کلی داشبورد
    public function getStats(Request $request)
    {
        $user = $this->getUser();

        // ایجاد کوئری‌ها با در نظر گرفتن نقش کاربر
        $computerQuery = Computer::query();
        $printerQuery = Printer::query();
        $scannerQuery = Scanner::query();
        $employeeQuery = Employee::query();
        $branchQuery = Branch::query();
        $cityQuery = City::query();
        $antivirusQuery = Computer::where('antivirus', 1);

        if ($user->role_id != 1) {
            $cityId = $user->city_id;
            $computerQuery->where('city_id', $cityId);
            $printerQuery->where('city_id', $cityId);
            $scannerQuery->where('city_id', $cityId);
            $antivirusQuery = Computer::where('antivirus','1')->where('city_id',$cityId);
            $employeeQuery->whereHas('branch', function($q) use ($cityId) {
                $q->where('city_id', $cityId);
            });
            $branchQuery->where('city_id', $cityId);
        }

        // محاسبه آمار
        $totalComputers = $computerQuery->count();
        $totalPrinters = $printerQuery->count();
        $totalScanners = $scannerQuery->count();
        $totalEmployees = $employeeQuery->count();
        $totalBranches = $branchQuery->count();
        $totalCities = $cityQuery->count();
        $totalAntivirus = $antivirusQuery->count();

        // آمار نمونه برای نمایش
        $todayEquipment = Computer::whereDate('created_at', today())
                ->when($user->role_id != 1, function($q) use ($user) {
                    $q->where('city_id', $user->city_id);
                })
                ->count() +
            Printer::whereDate('created_at', today())
                ->when($user->role_id != 1, function($q) use ($user) {
                    $q->where('city_id', $user->city_id);
                })
                ->count();

        // رشد ماهانه (نمونه)


        return response()->json([
            'totalComputers' => $totalComputers,
            'totalPrinters' => $totalPrinters,
            'totalScanners' => $totalScanners,
            'totalEmployees' => $totalEmployees,
            'totalBranches' => $totalBranches,
            'totalCities' => $totalCities,
            'totalAntivirus' => $totalAntivirus,
            'todayEquipment' => $todayEquipment,
        ]);
    }

    // آخرین تجهیزات اضافه شده
    public function getRecentEquipment(Request $request)
    {
        $user = $this->getUser();

        // کامپیوترهای جدید
        $recentComputers = Computer::with('branch')
            ->when($user->role_id != 1, function($q) use ($user) {
                $q->where('city_id', $user->city_id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($computer) {
                return [
                    'id' => $computer->id,
                    'type' => 'computer',
                    'name' => $computer->name,
                    'branch' => $computer->branch,
                    'created_at' => $computer->created_at
                ];
            });

        // پرینترهای جدید
        $recentPrinters = Printer::with('branch')
            ->when($user->role_id != 1, function($q) use ($user) {
                $q->where('city_id', $user->city_id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($printer) {
                return [
                    'id' => $printer->id,
                    'type' => 'printer',
                    'model' => $printer->model,
                    'branch' => $printer->branch,
                    'created_at' => $printer->created_at
                ];
            });

        // اسکنرهای جدید
        $recentScanners = Scanner::with('branch')
            ->when($user->role_id != 1, function($q) use ($user) {
                $q->where('city_id', $user->city_id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($scanner) {
                return [
                    'id' => $scanner->id,
                    'type' => 'scanner',
                    'model' => $scanner->model,
                    'branch' => $scanner->branch,
                    'created_at' => $scanner->created_at
                ];
            });

        // ترکیب و مرتب‌سازی همه تجهیزات
        $allEquipment = $recentComputers
            ->merge($recentPrinters)
            ->merge($recentScanners)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        return response()->json($allEquipment);
    }
}
