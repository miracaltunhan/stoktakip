@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Toplam Stok Kalemi</h5>
                    <h2 class="card-text">{{ $totalItems }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Kritik Stok Sayısı</h5>
                    <h2 class="card-text">{{ $criticalItems }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Toplam Stok Değeri</h5>
                    <h2 class="card-text">{{ $totalValue }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Aktif Bildirimler</h5>
                    <h2 class="card-text">{{ $activeNotifications }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Son Stok Hareketleri</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Hareket</th>
                                    <th>Miktar</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentMovements as $movement)
                                <tr>
                                    <td>{{ $movement->item->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $movement->type == 'in' ? 'success' : 'danger' }}">
                                            {{ $movement->type == 'in' ? 'Giriş' : 'Çıkış' }}
                                        </span>
                                    </td>
                                    <td>{{ $movement->quantity }}</td>
                                    <td>{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Kritik Stok Uyarıları</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($criticalStockAlerts as $alert)
                        <div class="list-group-item">
                            <h6 class="mb-1">{{ $alert->name }}</h6>
                            <p class="mb-1">Mevcut Stok: {{ $alert->current_stock }}</p>
                            <small class="text-danger">Minimum Stok: {{ $alert->minimum_stock }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 