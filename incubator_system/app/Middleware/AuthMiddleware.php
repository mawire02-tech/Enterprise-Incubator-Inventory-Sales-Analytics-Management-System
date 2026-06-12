<?php

namespace App\Middleware;

use App\Helpers\Auth;
use App\Helpers\Session;

/**
 * AuthMiddleware
 * Ensures user is logged in and session is still valid.
 */
class AuthMiddleware
{
    public function handle(): bool
    {
        if (!Auth::check()) {
            if ($this->isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.', 'redirect' => APP_URL . '/login']);
                exit;
            }
            Session::flash('error', 'Please login to continue.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        if (!Auth::checkSessionTimeout()) {
            if ($this->isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Session timed out. Please login again.', 'redirect' => APP_URL . '/login']);
                exit;
            }
            Session::flash('warning', 'Your session has expired due to inactivity. Please login again.');
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        return true;
    }

    private function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
