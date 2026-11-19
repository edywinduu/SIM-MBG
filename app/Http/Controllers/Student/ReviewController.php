<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends BaseStudentController
{
    /**
     * Menyimpan atau memperbarui ulasan makanan siswa.
     */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'review_id' => 'nullable|integer',
            'menu_id' => 'nullable|integer',
        ]);

        $student = $this->resolveStudent(Auth::user());

        if (! $student) {
            return response()->json(['status' => 'error', 'message' => 'Data profil siswa tidak ditemukan.'], 403);
        }

        $reviewId = $request->input('review_id');
        $menuId = $request->input('menu_id');
        $rating = (int) $request->input('rating');
        $comment = $request->input('comment', '');

        if ($reviewId) {
            $existing = $student->reviews()->find($reviewId);

            if ($existing) {
                $existing->update([
                    'rating' => $rating,
                    'comment' => $comment,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Ulasan berhasil diperbarui!',
                    'review_id' => $existing->id,
                ]);
            }
        }

        if (! $menuId) {
            return response()->json(['status' => 'error', 'message' => 'ID menu tidak valid.'], 422);
        }

        $review = $student->reviews()->create([
            'menu_id' => $menuId,
            'rating' => $rating,
            'comment' => $comment,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ulasan berhasil disimpan!',
            'review_id' => $review->id,
        ]);
    }

    /**
     * Menghapus ulasan makanan siswa.
     */
    public function destroy(int $id): JsonResponse
    {
        $student = $this->resolveStudent(Auth::user());

        if (! $student) {
            return response()->json(['status' => 'error', 'message' => 'Data profil siswa tidak ditemukan.'], 403);
        }

        $review = $student->reviews()->find($id);

        if ($review) {
            $review->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Ulasan berhasil dihapus.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Ulasan tidak ditemukan atau Anda tidak memiliki akses.',
        ], 404);
    }
}
