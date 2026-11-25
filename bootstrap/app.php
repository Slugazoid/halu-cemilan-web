<?php

use App\Services\RedirectService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Akun pelanggan ditunda: rutenya hanya ada bila feature flag menyala.
            if (config('features.customer_accounts')) {
                Route::middleware('web')->group(base_path('routes/customer.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Slug lama menu/kategori → alihan 301 ke alamat baru (BR-50). Hanya dicek saat halaman tidak ditemukan.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
                return null;
            }

            $redirect = app(RedirectService::class)->resolve($request->path());

            return $redirect ? redirect($redirect->new_path, $redirect->status_code) : null;
        });
    })->create();
