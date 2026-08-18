<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use App\Http\Middleware\JWTVerifyCustomer;

class Kernel extends HttpKernel
{
  /**
   * The application's global HTTP middleware stack.
   *
   * These middleware are run during every request to your application.
   *
   * @var array<int, class-string|string>
   */
  // Laravel 11 tự động thêm HandleCors vào global middleware
  // Không cần khai báo ở đây nữa
  protected $middleware = [
    // \Illuminate\Http\Middleware\HandleCors::class, // Đã được Laravel 11 tự động thêm
  ];

  protected $middlewareGroups = [
    'api' => [
      'throttle:api',
      \Illuminate\Routing\Middleware\SubstituteBindings::class,
      \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
      // Không apply auth.jwt cho tất cả API routes - chỉ apply cho routes cần thiết trong routes/api.php
      // 'auth.jwt', // Đã được apply riêng cho từng route group trong routes/api.php
    ],
  ];
  protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'auth.jwt' => \App\Http\Middleware\JWTVerifyCustomer::class, // Đăng ký auth.jwt middleware
    'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
    'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
    'can' => \Illuminate\Auth\Middleware\Authorize::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
    'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    // 'sso-verified' => \App\Http\Middleware\SSOMiddleware::class,
    // 'permission-check' => \App\Http\Middleware\PermissionMiddleware::class,
    // 'custom.logs' => \App\Http\Middleware\CustomLogs::class,
  ];
}
