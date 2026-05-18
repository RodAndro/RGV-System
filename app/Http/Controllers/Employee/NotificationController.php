<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function unreadCount()
    {
        $user = Auth::user();

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'notifications' => $user->unreadNotifications()->latest()->take(5)->get()->map(function ($n) {
                $link = $n->link;
                if ($link && preg_match('#/admin/bookings/(\d+)#', $link, $m)) {
                    $link = route('employee.bookings.show', $m[1]);
                }
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => Str::limit($n->message, 80),
                    'link' => $link ?: route('employee.notifications.index'),
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();
        
        return view('employee.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return back()->with('success', 'Notification marked as read.');
    }

    public function open($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        $link = $notification->link;

        // Rewrite admin booking links to employee booking routes
        if ($link && preg_match('#/admin/bookings/(\d+)#', $link, $m)) {
            $link = route('employee.bookings.show', $m[1]);
        }

        return redirect($link ?: route('employee.notifications.index'));
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        
        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();
        
        return back()->with('success', 'Notification deleted successfully.');
    }

    public function clearAll()
    {
        $user = Auth::user();
        $user->notifications()->delete();
        
        return back()->with('success', 'All notifications cleared successfully.');
    }
}
