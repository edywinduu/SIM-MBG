<?php

namespace App\Http\Controllers\Student;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HistoryController extends BaseStudentController
{
    /**
     * Menampilkan halaman riwayat pengambilan & ulasan siswa.
     */
    public function index(): View
    {
        $student = $this->resolveStudent(Auth::user());

        $attendanceRate = '100%';
        $totalReviews = 0;
        $avgScore = '4.8 / 5.0';
        $historyData = [];

        if ($student) {
            // 1. Ringkasan Kehadiran via Model Pickup
            $totalPickups = $student->pickups()->count();
            $pickedUp = $student->pickups()
                ->where('status', 'picked_up')
                ->count();
            if ($totalPickups > 0) {
                $attendanceRate = round(($pickedUp / $totalPickups) * 100).'%';
            }

            // 2. Ringkasan Ulasan via Model StudentReview
            $totalReviews = $student->reviews()->count();
            $avgRating = $student->reviews()->avg('rating');
            if ($avgRating) {
                $avgScore = number_format((float) $avgRating, 1, '.', '').' / 5.0';
            }

            // 3. Data Riwayat Ulasan via Relasi Model
            $reviews = $student->reviews()
                ->with('menu')
                ->orderByDesc('created_at')
                ->get();

            $pickupsByDate = $student->pickups()
                ->get()
                ->keyBy(fn ($p) => Carbon::parse($p->date)->toDateString());

            foreach ($reviews as $rev) {
                $menu = $rev->menu;
                $dateObj = ! empty($rev->created_at) ? Carbon::parse($rev->created_at) : now();
                $dateStr = $dateObj->toDateString();
                $pickup = $pickupsByDate->get($dateStr);

                $formattedDate = $dateObj->translatedFormat('l, d F Y');
                $pickupTime = $pickup && ! empty($pickup->pickup_time)
                    ? Carbon::parse($pickup->pickup_time)->format('H:i').' WITA'
                    : '12:10 WITA';

                $menuCode = match ($menu?->category) {
                    'special' => 'Paket B',
                    'backup' => 'Paket Cadangan',
                    default => 'Paket A',
                };

                $macros = "{$menu?->calories} kkal | {$menu?->protein}g Protein | ".((float) ($menu?->fiber ?? 0)).'g Serat';

                $historyData[] = [
                    'id' => $rev->id,
                    'menu_id' => $rev->menu_id,
                    'date' => $formattedDate,
                    'pickupTime' => $pickupTime,
                    'status' => 'Sudah Diambil',
                    'menuCode' => $menuCode,
                    'menuName' => $menu?->name ?? 'Menu',
                    'rating' => (int) $rev->rating,
                    'review' => $rev->comment ?? '',
                    'macros' => $macros,
                    'image' => $menu?->image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
                ];
            }

            // Tambahkan juga riwayat menu terpilih yang belum diulas (pending review) via Model MenuSelection
            $reviewedMenuIds = $reviews->pluck('menu_id')->toArray();
            $pendingSelections = $student->selections()
                ->with(['menu', 'schedule'])
                ->whereNotIn('menu_id', $reviewedMenuIds)
                ->get()
                ->sortByDesc(fn ($sel) => $sel->schedule?->date);

            foreach ($pendingSelections as $sel) {
                $menu = $sel->menu;
                $schedule = $sel->schedule;
                $dateObj = $schedule?->date ? Carbon::parse($schedule->date) : now();
                $formattedDate = $dateObj->translatedFormat('l, d F Y');

                $menuCode = match ($menu?->category) {
                    'special' => 'Paket B',
                    'backup' => 'Paket Cadangan',
                    default => 'Paket A',
                };

                $macros = "{$menu?->calories} kkal | {$menu?->protein}g Protein | ".((float) ($menu?->fiber ?? 0)).'g Serat';

                $historyData[] = [
                    'id' => null,
                    'menu_id' => $sel->menu_id,
                    'date' => $formattedDate,
                    'pickupTime' => '12:00 WITA',
                    'status' => 'Sudah Diambil',
                    'menuCode' => $menuCode,
                    'menuName' => $menu?->name ?? 'Menu',
                    'rating' => 0,
                    'review' => 'Belum ada ulasan untuk menu ini. Berikan masukan Anda kepada penyedia dapur SPPG.',
                    'macros' => $macros,
                    'image' => $menu?->image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
                ];
            }
        }

        return view('student.history', compact('attendanceRate', 'totalReviews', 'avgScore', 'historyData'));
    }

    /**
     * Menampilkan halaman ulasan siswa.
     */
    public function review(): View
    {
        return view('student.review');
    }
}
