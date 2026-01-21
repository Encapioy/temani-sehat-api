<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek 1: Apakah user sudah login?
        // Cek 2: Apakah role-nya 'admin'?
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses Ditolak! Anda bukan Admin.'], 403);
        }

        return $next($request); // Silakan masuk bos
    }
}