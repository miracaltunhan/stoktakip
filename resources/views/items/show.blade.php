@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Ürün Detayı</h5>
                    <div>
                        <a href="{{ route('items.edit', $item) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit"></i> Düzenle
                        </a>
                        <a href="{{ route('items.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Geri
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Ürün Adı</h6>
                            <p class="h5">{{ $item->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Birim</h6>
                            <p class="h5">{{ $item->unit }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-1">Açıklama</h6>
                        <p>{{ $item->description ?: 'Açıklama bulunmuyor.' }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Mevcut Stok</h6>
                                    <p class="h4 mb-0">{{ $item->current_stock }} {{ $item->unit }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Minimum Stok</h6>
                                    <p class="h4 mb-0">{{ $item->minimum_stock }} {{ $item->unit }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Aylık Tüketim</h6>
                                    <p class="h4 mb-0">{{ $item->monthly_consumption }} {{ $item->unit }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="mb-3">Stok Hareketleri</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>İşlem</th>
                                        <th>Miktar</th>
                                        <th>Açıklama</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($item->stockMovements as $movement)
                                        <tr>
                                            <td>{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                                            <td>
                                                @if($movement->type === 'in')
                                                    <span class="badge bg-success">Giriş</span>
                                                @else
                                                    <span class="badge bg-danger">Çıkış</span>
                                                @endif
                                            </td>
                                            <td>{{ $movement->quantity }} {{ $item->unit }}</td>
                                            <td>{{ $movement->description }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">Henüz stok hareketi bulunmuyor.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 