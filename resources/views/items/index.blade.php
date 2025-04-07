@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Ürünler</h5>
                    <a href="{{ route('items.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Yeni Ürün
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Ürün Adı</th>
                                    <th>Birim</th>
                                    <th>Mevcut Stok</th>
                                    <th>Minimum Stok</th>
                                    <th>Aylık Tüketim</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>{{ $item->current_stock }} {{ $item->unit }}</td>
                                        <td>{{ $item->minimum_stock }} {{ $item->unit }}</td>
                                        <td>{{ $item->monthly_consumption }} {{ $item->unit }}</td>
                                        <td>
                                            @if($item->current_stock <= $item->minimum_stock)
                                                <span class="badge bg-danger">Kritik Seviye</span>
                                            @else
                                                <span class="badge bg-success">Normal</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addModal{{ $item->id }}">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#consumeModal{{ $item->id }}">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <a href="{{ route('items.show', $item) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Stok Artırma Modal -->
                                            <div class="modal fade" id="addModal{{ $item->id }}" tabindex="-1" aria-labelledby="addModalLabel{{ $item->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="addModalLabel{{ $item->id }}">{{ $item->name }} - Stok Artışı</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                                        </div>
                                                        <form action="{{ route('items.add-stock', $item) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="quantity" class="form-label">Eklenecek Miktar</label>
                                                                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                                                                    <div class="form-text">Mevcut stok: {{ $item->current_stock }} {{ $item->unit }}</div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="description" class="form-label">Açıklama</label>
                                                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                                <button type="submit" class="btn btn-success">Stok Ekle</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tüketim Modal -->
                                            <div class="modal fade" id="consumeModal{{ $item->id }}" tabindex="-1" aria-labelledby="consumeModalLabel{{ $item->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="consumeModalLabel{{ $item->id }}">{{ $item->name }} - Stok Tüketimi</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                                        </div>
                                                        <form action="{{ route('items.consume', $item) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="quantity" class="form-label">Tüketim Miktarı</label>
                                                                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="{{ $item->current_stock }}" required>
                                                                    <div class="form-text">Mevcut stok: {{ $item->current_stock }} {{ $item->unit }}</div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="description" class="form-label">Açıklama</label>
                                                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                                <button type="submit" class="btn btn-primary">Tüketimi Kaydet</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Henüz ürün bulunmuyor.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($items->hasPages())
                        <div class="mt-4">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 