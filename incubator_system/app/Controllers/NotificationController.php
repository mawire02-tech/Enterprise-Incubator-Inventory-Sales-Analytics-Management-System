<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    private NotificationService $notifications;

    public function __construct()
    {
        parent::__construct();
        $this->notifications = new NotificationService();
    }

    public function index(): void
    {
        $user   = Auth::user();
        $result = $this->notifications->getAll();
        $this->view('notifications.index', [
            'pageTitle'     => 'Notifications',
            'notifications' => $result['data'],
            'paginate'      => $result,
        ]);
    }

    public function unread(): void
    {
        $user  = Auth::user();
        $items = $this->notifications->getForUser($user['id'], $user['role_id'], true, 20);
        $count = $this->notifications->getUnreadCount($user['id'], $user['role_id']);
        $this->success(['items' => $items, 'count' => $count]);
    }

    public function markRead(array $params): void
    {
        $this->notifications->markRead((int)$params['id'], Auth::id());
        $this->success(null, 'Marked as read.');
    }

    public function markAllRead(): void
    {
        $user = Auth::user();
        $this->notifications->markAllRead($user['id'], $user['role_id']);
        $this->success(null, 'All notifications marked as read.');
    }
}
