<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->notifications();
        
        // Apply filters
        switch ($request->get('filter', 'all')) {
            case 'unread':
                $query->whereNull('read_at');
                break;
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'all':
            default:
                // No filter
                break;
        }
        
        $notifications = $query->latest()->paginate(15);
        
        return view('notifications.index', compact('notifications'));
    }
    
    public function check()
    {
        $user = Auth::user();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }
    
    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();
        
        return back()->with('success', 'Notification marked as read.');
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return back()->with('success', 'All notifications marked as read.');
    }
    
    /**
     * Delete a notification
     */
    public function destroy($notificationId)
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->delete();
        
        return back()->with('success', 'Notification deleted.');
    }
    
    /**
     * Clear all notifications
     */
    public function clearAll()
    {
        Auth::user()->notifications()->delete();
        
        return redirect()->route('notifications.index')
            ->with('success', 'All notifications cleared.');
    }
}