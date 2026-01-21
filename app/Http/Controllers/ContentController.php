<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;

class ContentController extends Controller
{
    // 1. Ambil Semua Konten (Bisa Filter Kategori)
    public function index(Request $request)
    {
        $query = Content::query();

        // Fitur Filter: ?category=Ginjal
        if ($request->has('category')) {
            $query->where('category', 'LIKE', '%' . $request->category . '%');
        }

        // Fitur Filter: ?type=video
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return response()->json([
            'message' => 'Daftar konten berhasil diambil',
            'data' => $query->get()
        ]);
    }

    // 2. Ambil Detail 1 Konten
    public function show($id)
    {
        $content = Content::find($id);

        if (!$content) {
            return response()->json(['message' => 'Konten tidak ditemukan'], 404);
        }

        return response()->json([
            'data' => $content
        ]);
    }
}