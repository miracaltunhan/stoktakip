<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::latest()->paginate(10);
        return view('notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('notifications.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,error,success',
            'is_read' => 'boolean',
        ]);

        Notification::create($validated);

        return redirect()->route('notifications.index')
            ->with('success', 'Bildirim başarıyla oluşturuldu.');
    }

    public function show(Notification $notification)
    {
        return view('notifications.show', compact('notification'));
    }

    public function edit(Notification $notification)
    {
        return view('notifications.edit', compact('notification'));
    }

    public function update(Request $request, Notification $notification)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,error,success',
            'is_read' => 'boolean',
        ]);

        $notification->update($validated);

        return redirect()->route('notifications.index')
            ->with('success', 'Bildirim başarıyla güncellendi.');
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();

        return redirect()->route('notifications.index')
            ->with('success', 'Bildirim başarıyla silindi.');
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['is_read' => true]);

        return redirect()->route('notifications.index')
            ->with('success', 'Bildirim okundu olarak işaretlendi.');
    }

    public function markAllAsRead()
    {
        Notification::where('is_read', false)->update(['is_read' => true]);

        return redirect()->route('notifications.index')
            ->with('success', 'Tüm bildirimler okundu olarak işaretlendi.');
    }
} 