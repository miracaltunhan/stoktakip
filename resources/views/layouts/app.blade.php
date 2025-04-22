<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Takip Sistemi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Stok Takip</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('items.index') }}">Stok Kalemleri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('stock-movements.index') }}">Stok Hareketleri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('charts') ? 'active' : '' }}" href="{{ route('charts') }}">
                            <i class="fas fa-chart-bar me-2"></i>Grafikler
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('chatbot.index') ? 'active' : '' }}" href="{{ route('chatbot.index') }}">
                            <i class="fas fa-robot me-2"></i>Chatbot
                        </a>
                    </li>
                </ul>
                
                <!-- Notifications Dropdown -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a id="notificationsDropdown" class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            @php
                                $unreadCount = \App\Models\Notification::where('is_read', false)->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="badge bg-danger">{{ $unreadCount }}</span>
                            @endif
                        </a>

                        <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationsDropdown">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <h6 class="mb-0">Bildirimler</h6>
                                @if($unreadCount > 0)
                                    <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">
                                            Tümünü Okundu İşaretle
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="notifications-list" style="max-height: 300px; overflow-y: auto;">
                                @foreach(\App\Models\Notification::latest()->take(5)->get() as $notification)
                                    <div class="dropdown-item {{ $notification->is_read ? '' : 'bg-light' }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $notification->title }}</strong>
                                                <p class="mb-0 small">{{ $notification->message }}</p>
                                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                            @if(!$notification->is_read)
                                                <form action="{{ route('notifications.mark-as-read', $notification) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">
                                                        <i class="fas fa-check text-success"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                @if(\App\Models\Notification::count() === 0)
                                    <div class="dropdown-item text-center text-muted">
                                        Bildirim bulunmuyor
                                    </div>
                                @endif
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">
                                Tüm Bildirimleri Gör
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4">
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>
</html> 