@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Ürün Düzenle</h5>
                    <a href="{{ route('items.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Geri
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('items.update', $item) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Ürün Adı</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}" 
                                class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea name="description" id="description" rows="3" 
                                class="form-control @error('description') is-invalid @enderror">{{ old('description', $item->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="unit" class="form-label">Birim</label>
                                <select name="unit" id="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                    <option value="adet" {{ old('unit', $item->unit) == 'adet' ? 'selected' : '' }}>Adet</option>
                                    <option value="kg" {{ old('unit', $item->unit) == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                    <option value="lt" {{ old('unit', $item->unit) == 'lt' ? 'selected' : '' }}>Litre (lt)</option>
                                    <option value="paket" {{ old('unit', $item->unit) == 'paket' ? 'selected' : '' }}>Paket</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="current_stock" class="form-label">Mevcut Stok</label>
                                <input type="number" name="current_stock" id="current_stock" value="{{ old('current_stock', $item->current_stock) }}" 
                                    class="form-control @error('current_stock') is-invalid @enderror" required min="0">
                                @error('current_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="minimum_stock" class="form-label">Minimum Stok</label>
                                <input type="number" name="minimum_stock" id="minimum_stock" value="{{ old('minimum_stock', $item->minimum_stock) }}" 
                                    class="form-control @error('minimum_stock') is-invalid @enderror" required min="0">
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="monthly_consumption" class="form-label">Aylık Tüketim</label>
                                <input type="number" name="monthly_consumption" id="monthly_consumption" value="{{ old('monthly_consumption', $item->monthly_consumption) }}" 
                                    class="form-control @error('monthly_consumption') is-invalid @enderror" required min="0">
                                @error('monthly_consumption')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('items.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times"></i> İptal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 