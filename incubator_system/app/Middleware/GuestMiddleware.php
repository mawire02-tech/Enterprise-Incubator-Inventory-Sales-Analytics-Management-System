<?php

namespace App\Middleware;

use App\Helpers\Auth;

/**
 * GuestMiddleware
 * Redirects logged-in users away from login/register pages.
 */
class GuestMiddleware
{
    public function handle(): bool
    {
        if (Auth::check()) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        return true;
    }
}
