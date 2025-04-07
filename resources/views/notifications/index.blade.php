@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Bildirimler</h5>
                    @if($notifications->isNotEmpty())
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-light btn-sm">
                                <i class="fas fa-check-double"></i> Tümünü Okundu İşaretle
                            </button>
                        </form>
                    @endif
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Başlık</th>
                                    <th>Mesaj</th>
                                    <th>Tür</th>
                                    <th>Durum</th>
                                    <th>Tarih</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                    <tr>
                                        <td>{{ $notification->title }}</td>
                                        <td>{{ $notification->message }}</td>
                                        <td>
                                            @if($notification->type === 'stock_alert')
                                                <span class="badge bg-danger">Stok Uyarısı</span>
                                            @else
                                                <span class="badge bg-info">{{ $notification->type }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($notification->is_read)
                                                <span class="badge bg-success">Okundu</span>
                                            @else
                                                <span class="badge bg-warning">Okunmadı</span>
                                            @endif
                                        </td>
                                        <td>{{ $notification->created_at->format('d.m.Y H:i') }}</td>
                                        <td>
                                            @if(!$notification->is_read)
                                                <form action="{{ route('notifications.mark-as-read', $notification) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Okundu İşaretle
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Henüz bildirim bulunmuyor.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($notifications->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 