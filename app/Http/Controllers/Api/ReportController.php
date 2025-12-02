<?php

namespace App\Http\Controllers\Api;

use App\Exports\ReportsExport;
use App\Http\Controllers\Controller;
use App\Models\Printer;
use App\Models\Scanner;
use Illuminate\Http\Request;
use App\Models\Computer;
use App\Models\City;
use App\Models\Branch;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function getComputers(Request $request)
    {
        try {
            // دریافت تمام پارامترهای فیلتر جدید
            $filters = $request->only([
                'city_id', 'branch_id', 'search',
                'antivirus_status', 'hard_type',
                'ram_min', 'ram_max', 'cpu_search', 'os_search'
            ]);

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

            // اعمال فیلتر وضعیت آنتی‌ویروس
            if (!empty($filters['antivirus_status'])) {
                if ($filters['antivirus_status'] === 'active') {
                    $query->where('antivirus', true);
                } elseif ($filters['antivirus_status'] === 'inactive') {
                    $query->where('antivirus', false);
                }
            }

            // اعمال فیلتر نوع هارد
            if (!empty($filters['hard_type'])) {
                if ($filters['hard_type'] === 'ssd') {
                    $query->where('hard', true);
                } elseif ($filters['hard_type'] === 'hdd') {
                    $query->where('hard', false);
                }
            }

            // اعمال فیلتر محدوده RAM
            if (!empty($filters['ram_min'])) {
                $query->whereRaw('CAST(SUBSTRING_INDEX(ram, " ", 1) AS UNSIGNED) >= ?', [$filters['ram_min']]);
            }

            if (!empty($filters['ram_max'])) {
                $query->whereRaw('CAST(SUBSTRING_INDEX(ram, " ", 1) AS UNSIGNED) <= ?', [$filters['ram_max']]);
            }

            // اعمال جستجوی اصلی
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('os', 'like', "%{$search}%")
                        ->orWhere('ram', 'like', "%{$search}%")
                        ->orWhere('cpu', 'like', "%{$search}%");
                });
            }

            // جستجوی پردازنده
            if (!empty($filters['cpu_search'])) {
                $query->where('cpu', 'like', "%{$filters['cpu_search']}%");
            }

            // جستجوی سیستم عامل
            if (!empty($filters['os_search'])) {
                $query->where('os', 'like', "%{$filters['os_search']}%");
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

    public function getPrinters(Request $request)
    {
        try {
            // دریافت تمام پارامترهای فیلتر جدید
            $filters = $request->only([
                'city_id', 'branch_id', 'search',
                'min_quantity', 'max_quantity', 'ip_search'
            ]);

            $query = Printer::with(['city:id,name', 'branch:id,name']);

            if (!empty($filters['city_id'])) {
                $query->where('city_id', $filters['city_id']);
            }

            if (!empty($filters['branch_id'])) {
                $query->where('branch_id', $filters['branch_id']);
            }

            // اعمال فیلتر محدوده تعداد
            if (!empty($filters['min_quantity'])) {
                $query->where('quantity', '>=', $filters['min_quantity']);
            }

            if (!empty($filters['max_quantity'])) {
                $query->where('quantity', '<=', $filters['max_quantity']);
            }

            // اعمال جستجوی اصلی
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('model', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            }

            // جستجوی آدرس IP
            if (!empty($filters['ip_search'])) {
                $query->where('ip_address', 'like', "%{$filters['ip_search']}%");
            }

            $printers = $query->orderBy('id', 'desc')->get();

            $data = $printers->map(function($printer) {
                return [
                    'id' => $printer->id,
                    'model' => $printer->model,
                    'quantity' => $printer->quantity,
                    'ip_address' => $printer->ip_address,
                    'type' => $printer->type,
                    'is_color' => (bool)$printer->is_color,
                    'is_network' => (bool)$printer->is_network,
                    'branch_name' => $printer->branch->name ?? '-',
                    'city_name' => $printer->city->name ?? '-',
                    'created_at' => $printer->created_at?->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => $printers->count(),
                'message' => 'لیست پرینترها با موفقیت دریافت شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت لیست پرینترها'
            ], 500);
        }
    }

    public function getScanners(Request $request)
    {
        try {
            // دریافت تمام پارامترهای فیلتر جدید
            $filters = $request->only([
                'city_id', 'branch_id', 'search',
                'min_quantity', 'max_quantity'
            ]);

            $query = Scanner::with(['city:id,name', 'branch:id,name']);

            if (!empty($filters['city_id'])) {
                $query->where('city_id', $filters['city_id']);
            }

            if (!empty($filters['branch_id'])) {
                $query->where('branch_id', $filters['branch_id']);
            }

            // اعمال فیلتر محدوده تعداد
            if (!empty($filters['min_quantity'])) {
                $query->where('quantity', '>=', $filters['min_quantity']);
            }

            if (!empty($filters['max_quantity'])) {
                $query->where('quantity', '<=', $filters['max_quantity']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where('model', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            }

            $scanners = $query->orderBy('id', 'desc')->get();

            $data = $scanners->map(function($scanner) {
                return [
                    'id' => $scanner->id,
                    'model' => $scanner->model,
                    'quantity' => $scanner->quantity,
                    'type' => $scanner->type,
                    'branch_name' => $scanner->branch->name ?? '-',
                    'city_name' => $scanner->city->name ?? '-',
                    'created_at' => $scanner->created_at?->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => $scanners->count(),
                'message' => 'لیست اسکنرها با موفقیت دریافت شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت لیست اسکنرها'
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

    public function exportToExcel(Request $request)
    {
        try {
            $type = $request->get('type', 'computers');
            // دریافت تمام فیلترهای جدید
            $filters = $request->only([
                'city_id', 'branch_id', 'search',
                'antivirus_status', 'hard_type',
                'ram_min', 'ram_max', 'cpu_search', 'os_search',
                'min_quantity', 'max_quantity', 'ip_search'
            ]);

            // دریافت داده‌ها بر اساس نوع
            $data = [];
            $headers = [];
            $fileName = '';

            switch ($type) {
                case 'printers':
                    $data = $this->getPrintersData($filters);
                    $headers = ['مدل', 'تعداد', 'آدرس IP', 'نوع', 'رنگی', 'شبکه‌ای', 'شعبه', 'حوزه'];
                    $fileName = 'printers_report_' . date('Y-m-d') . '.xlsx';
                    break;

                case 'scanners':
                    $data = $this->getScannersData($filters);
                    $headers = ['مدل', 'تعداد', 'نوع', 'شعبه', 'حوزه'];
                    $fileName = 'scanners_report_' . date('Y-m-d') . '.xlsx';
                    break;

                default: // computers
                    $data = $this->getComputersData($filters);
                    $headers = ['نام دستگاه', 'کارمند', 'شعبه', 'حوزه', 'سیستم عامل', 'رم', 'پردازنده', 'مانیتور', 'مادربرد', 'آنتی‌ویروس', 'نوع هارد'];
                    $fileName = 'computers_report_' . date('Y-m-d') . '.xlsx';
            }

            return Excel::download(new ReportsExport($data, $headers, $type), $fileName);

        } catch (\Exception $e) {
            \Log::error('Excel export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'خطا در تولید فایل اکسل: ' . $e->getMessage()
            ], 500);
        }
    }

    // متدهای کمکی برای اکسل
    private function getComputersData($filters)
    {
        $query = Computer::with(['city:id,name', 'branch:id,name', 'employee:id,fname,lname']);

        // اعمال تمام فیلترها مانند متد اصلی
        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['antivirus_status'])) {
            if ($filters['antivirus_status'] === 'active') {
                $query->where('antivirus', true);
            } elseif ($filters['antivirus_status'] === 'inactive') {
                $query->where('antivirus', false);
            }
        }

        if (!empty($filters['hard_type'])) {
            if ($filters['hard_type'] === 'ssd') {
                $query->where('hard', true);
            } elseif ($filters['hard_type'] === 'hdd') {
                $query->where('hard', false);
            }
        }

        if (!empty($filters['ram_min'])) {
            $query->whereRaw('CAST(SUBSTRING_INDEX(ram, " ", 1) AS UNSIGNED) >= ?', [$filters['ram_min']]);
        }

        if (!empty($filters['ram_max'])) {
            $query->whereRaw('CAST(SUBSTRING_INDEX(ram, " ", 1) AS UNSIGNED) <= ?', [$filters['ram_max']]);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('os', 'like', "%{$search}%")
                    ->orWhere('ram', 'like', "%{$search}%")
                    ->orWhere('cpu', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['cpu_search'])) {
            $query->where('cpu', 'like', "%{$filters['cpu_search']}%");
        }

        if (!empty($filters['os_search'])) {
            $query->where('os', 'like', "%{$filters['os_search']}%");
        }

        $computers = $query->get();

        return $computers->map(function($computer) {
            return [
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
                'hard' => (bool)$computer->hard
            ];
        })->toArray();
    }

    private function getPrintersData($filters)
    {
        $query = Printer::with(['city:id,name', 'branch:id,name']);

        // اعمال تمام فیلترها مانند متد اصلی
        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['min_quantity'])) {
            $query->where('quantity', '>=', $filters['min_quantity']);
        }

        if (!empty($filters['max_quantity'])) {
            $query->where('quantity', '<=', $filters['max_quantity']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['ip_search'])) {
            $query->where('ip_address', 'like', "%{$filters['ip_search']}%");
        }

        $printers = $query->get();

        return $printers->map(function($printer) {
            return [
                'model' => $printer->model,
                'quantity' => $printer->quantity,
                'ip_address' => $printer->ip_address,
                'type' => $printer->type,
                'is_color' => (bool)$printer->is_color,
                'is_network' => (bool)$printer->is_network,
                'branch_name' => $printer->branch->name ?? '-',
                'city_name' => $printer->city->name ?? '-'
            ];
        })->toArray();
    }

    private function getScannersData($filters)
    {
        $query = Scanner::with(['city:id,name', 'branch:id,name']);

        // اعمال تمام فیلترها مانند متد اصلی
        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['min_quantity'])) {
            $query->where('quantity', '>=', $filters['min_quantity']);
        }

        if (!empty($filters['max_quantity'])) {
            $query->where('quantity', '<=', $filters['max_quantity']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('model', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%");
        }

        $scanners = $query->get();

        return $scanners->map(function($scanner) {
            return [
                'model' => $scanner->model,
                'quantity' => $scanner->quantity,
                'type' => $scanner->type,
                'branch_name' => $scanner->branch->name ?? '-',
                'city_name' => $scanner->city->name ?? '-'
            ];
        })->toArray();
    }
}
