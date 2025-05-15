<?php

      namespace App\Http\Middleware;

      use Closure;
      use Illuminate\Http\Request;
      use Illuminate\Support\Facades\Auth;
      use Symfony\Component\HttpFoundation\Response;

      class DosenMiddleware
      {
          public function handle(Request $request, Closure $next): Response
          {
              if (Auth::check() && Auth::user()->role === 'dosen') {
                  return $next($request);
              }
              return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda bukan Dosen.');
          }
      }
