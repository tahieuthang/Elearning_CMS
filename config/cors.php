<?php

return [

  /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

  'paths' => ['api/*'], // Các route được áp dụng CORS

  'allowed_methods' => ['*'], // Các HTTP method được phép (GET, POST, PUT,...)

  'allowed_origins' => ['*'], // Domain được phép truy cập

  // Pattern cho ngrok URLs (vì ngrok URL thay đổi mỗi lần restart)
  'allowed_origins_patterns' => [
    '/^https:\/\/.*\.ngrok-free\.dev$/',
    '/^https:\/\/.*\.ngrok\.io$/',
    '/^https:\/\/.*\.ngrok-app\.com$/',
  ],

  'allowed_headers' => ['*', 'x-redirect-on-401'], // Các header được phép (bao gồm custom header x-redirect-on-401)

  'exposed_headers' => [], // Các header được phép hiển thị ở client

  'max_age' => 86400 , // Thời gian cache (giây)

  'supports_credentials' => false, // Có cho phép cookie hoặc xác thực không
];
