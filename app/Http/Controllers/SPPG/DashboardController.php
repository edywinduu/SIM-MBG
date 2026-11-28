<?php

namespace App\Http\Controllers\SPPG;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Distribution;
use App\Models\Menu;
use App\Models\MenuSchedule;
use App\Models\School;
use App\Models\SchoolFeedback;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends BaseSppgController
{
    public function index(): View
    {
        $sppgId = $this->getSppgId();
        $today = today();

        $todaySchedule = MenuSchedule::with('menu')
            ->where('sppg_id', $sppgId)
            ->whereDate('date', $today)
            ->first()
            ?? MenuSchedule::with('menu')->whereDate('date', $today)->first()
            ?? MenuSchedule::with('menu')->latest('date')->first();

        $todayMenu = $todaySchedule?->menu ?? Menu::where('is_active', true)->first();

        $announcement = Announcement::where('sppg_id', $sppgId)
            ->latest('created_at')
            ->first()
            ?? Announcement::latest('created_at')->first();

        $distributions = $this->getDistributionsList();
        $schools = $this->formatDistributions($distributions);
        $activities = $this->getFormattedActivities($sppgId);
        $kpis = $this->buildKpis($distributions);

        return view('sppg.dashboard', compact(
            'todaySchedule',
            'todayMenu',
            'announcement',
            'schools',
            'activities',
            'kpis'
        ));
    }

    /**
     * Sinkronisasi data ringkasan dashboard SPPG secara langsung dari database.
     */
    public function sync(): JsonResponse
    {
        $sppgId = $this->getSppgId();
        $distributions = $this->getDistributionsList();

        return response()->json([
            'status' => 'success',
            'message' => 'Data distribusi dan aktivitas berhasil disinkronkan dari database.',
            'kpis' => $this->buildKpis($distributions),
            'schools' => $this->formatDistributions($distributions),
            'activities' => $this->getFormattedActivities($sppgId),
        ]);
    }

    /**
     * Memperbarui status pengiriman distribusi sekolah (menunggu -> dikirim -> terkirim).
     */
    public function updateDistributionStatus(Request $request, int $id): JsonResponse
    {
        $distribution = Distribution::with('school')->find($id);

        if (! $distribution) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data distribusi sekolah tidak ditemukan.',
            ], 404);
        }

        $requestedStatus = $request->input('status');
        if ($requestedStatus) {
            $dbStatus = match ($requestedStatus) {
                'terkirim', 'delivered' => 'delivered',
                'dikirim', 'in_transit' => 'in_transit',
                default => 'pending',
            };
        } else {
            $dbStatus = match ($distribution->status) {
                'pending' => 'in_transit',
                'in_transit' => 'delivered',
                default => 'delivered',
            };
        }

        $distribution->update(['status' => $dbStatus]);

        $schoolName = $distribution->school?->name ?? 'Sekolah Mitra';
        $uiStatus = match ($dbStatus) {
            'delivered' => 'terkirim',
            'in_transit' => 'dikirim',
            default => 'menunggu',
        };
        $statusLabel = match ($uiStatus) {
            'terkirim' => 'Terkirim',
            'dikirim' => 'Sedang Dikirim',
            default => 'Menunggu',
        };

        ActivityLog::create([
            'sppg_id' => $distribution->sppg_id ?: $this->getSppgId(),
            'title' => "Status distribusi diperbarui • {$schoolName}",
            'description' => "{$distribution->portion_count} porsi berstatus {$statusLabel}",
            'status' => $dbStatus === 'delivered' ? 'Delivered' : ($dbStatus === 'in_transit' ? 'In Transit' : 'Pending'),
            'created_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Status distribusi {$schoolName} diperbarui menjadi {$statusLabel}.",
            'distribution_status' => $uiStatus,
            'activities' => $this->getFormattedActivities($this->getSppgId()),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Distribution>
     */
    private function getDistributionsList()
    {
        $distributions = Distribution::with('school')
            ->whereDate('date', today())
            ->get();

        if ($distributions->isEmpty()) {
            $latestDate = Distribution::max('date');
            if ($latestDate) {
                $distributions = Distribution::with('school')
                    ->whereDate('date', $latestDate)
                    ->get();
            }
        }

        return $distributions;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Distribution>  $distributions
     * @return array<string, int>
     */
    private function buildKpis($distributions): array
    {
        $totalPortions = (int) $distributions->sum('portion_count');
        $activeSchools = School::where('status', 'active')->count();
        $verifiedMenus = Menu::where('is_active', true)->count();
        $pendingEvaluations = SchoolFeedback::where('status', 'needs_evaluation')
            ->where(function ($q) {
                $q->whereNull('kitchen_response')->orWhere('kitchen_response', '');
            })
            ->count();

        return [
            'total_portions' => $totalPortions > 0 ? $totalPortions : 2940,
            'active_schools' => $activeSchools > 0 ? $activeSchools : $distributions->count(),
            'verified_menus' => $verifiedMenus,
            'pending_evaluations' => $pendingEvaluations,
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Distribution>  $distributions
     * @return array<int, array<string, mixed>>
     */
    private function formatDistributions($distributions): array
    {
        return $distributions->map(function (Distribution $dist): array {
            $school = $dist->school;
            $etaStart = $dist->eta_start ? Carbon::parse($dist->eta_start)->format('H:i') : '09:00';
            $etaEnd = $dist->eta_end ? Carbon::parse($dist->eta_end)->format('H:i') : '09:30';
            $status = match ($dist->status) {
                'delivered' => 'terkirim',
                'in_transit' => 'dikirim',
                default => 'menunggu',
            };

            return [
                'id' => $dist->id,
                'school_id' => $dist->school_id,
                'name' => $school?->name ?? 'Sekolah Mitra',
                'npsn' => $school?->npsn ?? '-',
                'pic' => $school?->pic_name ?? 'Koordinator UKS',
                'area' => trim(($school?->city ?? 'Kota Denpasar').' / '.($school?->district ?? 'Denpasar')),
                'qty' => (int) $dist->portion_count,
                'eta' => "{$etaStart} - {$etaEnd} WITA",
                'status' => $status,
            ];
        })->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getFormattedActivities(int $sppgId): array
    {
        $logs = ActivityLog::where('sppg_id', $sppgId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        if ($logs->isEmpty()) {
            $logs = ActivityLog::orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(6)
                ->get();
        }

        return $logs->map(function (ActivityLog $log): array {
            $statusLower = strtolower((string) $log->status);
            $icon = 'task_alt';
            $tone = 'ico-green';
            $pill = ['pill-green', 'Terkirim'];

            if (str_contains($statusLower, 'transit') || str_contains($statusLower, 'proses') || str_contains($statusLower, 'dikirim')) {
                $icon = 'directions_run';
                $tone = 'ico-blue';
                $pill = ['pill-blue', 'Diproses'];
            } elseif (str_contains($statusLower, 'eval') || str_contains($statusLower, 'review')) {
                $icon = 'rate_review';
                $tone = 'ico-orange';
                $pill = ['pill-orange', 'Evaluasi Diterima'];
            } elseif (str_contains($statusLower, 'pending') || str_contains($statusLower, 'tunggu')) {
                $icon = 'schedule';
                $tone = 'ico-orange';
                $pill = ['pill-gray', 'Menunggu'];
            }

            $timeFormatted = 'Hari ini, 09:00 WITA';
            if ($log->created_at) {
                $cDate = Carbon::parse($log->created_at);
                $timeFormatted = ($cDate->isToday() ? 'Hari ini, ' : $cDate->translatedFormat('d M, ')).$cDate->format('H:i').' WITA';
            }

            return [
                'id' => $log->id,
                'icon' => $icon,
                'tone' => $tone,
                'title' => $log->title,
                'desc' => $log->description ?? '',
                'time' => $timeFormatted,
                'pill' => $pill,
            ];
        })->values()->all();
    }
}
