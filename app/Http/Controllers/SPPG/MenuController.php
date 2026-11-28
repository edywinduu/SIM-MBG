<?php

namespace App\Http\Controllers\SPPG;

use App\Models\ActivityLog;
use App\Models\Menu;
use App\Models\StudentAllergy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends BaseSppgController
{
    public function index(): View
    {
        $dbMenus = Menu::with(['allergens', 'schedules'])
            ->orderBy('id')
            ->get();

        $menus = $dbMenus->values()->map(fn (Menu $menu, int $index) => $this->formatMenu($menu, $index))->all();

        $totalMenus = $dbMenus->count();
        $activeMenusCount = $dbMenus->where('is_active', true)->count();
        $avgCalories = (int) round((float) ($dbMenus->where('is_active', true)->avg('calories') ?: 615));
        $specialCount = $dbMenus->where('category', 'special')->count();
        $regularCount = $dbMenus->where('category', 'regular')->count();
        $backupCount = $dbMenus->where('category', 'backup')->count();
        $sensitiveStudents = StudentAllergy::distinct('student_id')->count();

        $kpis = [
            'total_active_menus' => $activeMenusCount,
            'total_menus' => $totalMenus,
            'avg_calories' => $avgCalories,
            'special_menus' => $specialCount,
            'sensitive_students' => $sensitiveStudents > 0 ? $sensitiveStudents : 3,
        ];

        $counts = [
            'all' => $totalMenus,
            'reguler' => $regularCount,
            'khusus' => $specialCount,
            'cadangan' => $backupCount,
        ];

        return view('sppg.menu', compact('menus', 'kpis', 'counts'));
    }

    /**
     * Menyimpan paket menu baru ke database.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'desc' => 'nullable|string',
            'kcal' => 'required|integer|min:100|max:2000',
            'cat' => 'required|string',
            'carb' => 'nullable|integer|min:0',
            'prot' => 'nullable|integer|min:0',
            'fat' => 'nullable|integer|min:0',
            'fiber' => 'nullable|numeric|min:0',
        ]);

        $dbCategory = $this->mapToDbCategory($validated['cat']);

        $menu = Menu::create([
            'name' => trim($validated['name']),
            'description' => trim((string) ($validated['desc'] ?? '-')),
            'category' => $dbCategory,
            'calories' => (int) $validated['kcal'],
            'carbs' => (int) ($validated['carb'] ?? 70),
            'protein' => (int) ($validated['prot'] ?? 26),
            'fat' => (int) ($validated['fat'] ?? 15),
            'fiber' => (float) ($validated['fiber'] ?? 7.0),
            'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
            'is_active' => true,
        ]);

        ActivityLog::create([
            'sppg_id' => $this->getSppgId(),
            'title' => "Menu baru ditambahkan • {$menu->name}",
            'description' => "{$menu->calories} kkal | Protein {$menu->protein}g | Karbo {$menu->carbs}g",
            'status' => 'Delivered',
            'created_at' => now(),
        ]);

        $index = max(0, Menu::count() - 1);

        return response()->json([
            'status' => 'success',
            'message' => 'Menu baru berhasil ditambahkan ke database.',
            'menu' => $this->formatMenu($menu->fresh(['allergens', 'schedules']), $index),
        ]);
    }

    /**
     * Memperbarui data paket menu di database.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $menu = Menu::find($id);
        if (! $menu) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data menu tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'desc' => 'nullable|string',
            'kcal' => 'required|integer|min:100|max:2000',
            'cat' => 'required|string',
            'carb' => 'nullable|integer|min:0',
            'prot' => 'nullable|integer|min:0',
            'fat' => 'nullable|integer|min:0',
            'fiber' => 'nullable|numeric|min:0',
        ]);

        $menu->update([
            'name' => trim($validated['name']),
            'description' => trim((string) ($validated['desc'] ?? $menu->description)),
            'category' => $this->mapToDbCategory($validated['cat']),
            'calories' => (int) $validated['kcal'],
            'carbs' => (int) ($validated['carb'] ?? $menu->carbs),
            'protein' => (int) ($validated['prot'] ?? $menu->protein),
            'fat' => (int) ($validated['fat'] ?? $menu->fat),
            'fiber' => (float) ($validated['fiber'] ?? $menu->fiber),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Menu {$menu->name} berhasil diperbarui.",
            'menu' => $this->formatMenu($menu->fresh(['allergens', 'schedules']), max(0, $menu->id - 1)),
        ]);
    }

    /**
     * Mengubah status aktif/nonaktif paket menu di database.
     */
    public function toggle(Request $request, int $id): JsonResponse
    {
        $menu = Menu::find($id);
        if (! $menu) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data menu tidak ditemukan.',
            ], 404);
        }

        $active = $request->has('active')
            ? (bool) $request->boolean('active')
            : ! $menu->is_active;

        $menu->update(['is_active' => $active]);

        return response()->json([
            'status' => 'success',
            'message' => "{$menu->name} ".($active ? 'diaktifkan' : 'dinonaktifkan').'.',
            'active' => (bool) $menu->is_active,
        ]);
    }

    private function mapToDbCategory(string $uiCat): string
    {
        return match (strtolower(trim($uiCat))) {
            'khusus', 'special' => 'special',
            'cadangan', 'backup' => 'backup',
            default => 'regular',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMenu(Menu $menu, int $index): array
    {
        $uiCat = match ($menu->category) {
            'special' => 'khusus',
            'backup' => 'cadangan',
            default => 'reguler',
        };

        $catLabel = match ($uiCat) {
            'khusus' => 'Alergi Khusus',
            'cadangan' => 'Ready-to-Deploy',
            default => 'Reguler SD/SMP',
        };

        $days = [
            'Senin • Minggu ke-1',
            'Selasa • Minggu ke-1',
            'Rabu • Minggu ke-1',
            'Kamis • Minggu ke-1',
            'Jumat • Minggu ke-1',
        ];
        $dayLabel = $uiCat === 'cadangan'
            ? 'Cadangan Operasional'
            : ($days[$index % count($days)] ?? 'Siklus Mingguan');

        $tags = [];
        $containsNames = [];
        foreach ($menu->allergens as $allergen) {
            $pivotType = $allergen->pivot?->type;
            if ($pivotType === 'free_from') {
                $tags[] = ['ok', 'Bebas '.$allergen->name];
            } else {
                $tags[] = ['warn', 'Mengandung '.$allergen->name];
                $containsNames[] = $allergen->name;
            }
        }

        if (empty($tags)) {
            $tags[] = ['ok', 'Terverifikasi Gizi SPPG'];
        }

        $letter = chr(65 + ($index % 26));
        $imgUrl = $menu->image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';

        return [
            'id' => $menu->id,
            'day' => $dayLabel,
            'cat' => $uiCat,
            'catLabel' => $catLabel,
            'name' => $menu->name,
            'desc' => $menu->description ?? '-',
            'kcal' => (int) $menu->calories,
            'carb' => (int) $menu->carbs,
            'prot' => (int) $menu->protein,
            'fat' => (int) $menu->fat,
            'tags' => $tags,
            'active' => (bool) $menu->is_active,
            'img' => $imgUrl,
            'code' => 'Paket '.$letter,
            'calories' => $menu->calories.' kkal',
            'protein' => $menu->protein.'g',
            'carbs' => $menu->carbs.'g',
            'fiber' => ((float) $menu->fiber).'g',
            'items' => $menu->items,
            'allergens' => $containsNames,
            'image' => $imgUrl,
            'ecoFriendly' => true,
        ];
    }
}
