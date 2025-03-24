@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Stok Kalemleri</h2>
    <a href="{{ route('items.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Yeni Kalem Ekle
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ad</th>
                        <th>Açıklama</th>
                        <th>Mevcut Stok</th>
                        <th>Minimum Stok</th>
                        <th>Aylık Tüketim</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->current_stock }}</td>
                        <td>{{ $item->minimum_stock }}</td>
                        <td>{{ $item->monthly_consumption }}</td>
                        <td>
                            @if($item->current_stock <= $item->minimum_stock)
                                <span class="badge bg-danger">Kritik</span>
                            @else
                                <span class="badge bg-success">Normal</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('items.show', $item) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu kalemi silmek istediğinizden emin misiniz?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($items->hasPages())
<div class="mt-4">
    {{ $items->links() }}
</div>
@endif
@endsection 