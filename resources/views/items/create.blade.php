@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Yeni Stok Kalemi Ekle</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('items.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Kalem Adı</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="current_stock" class="form-label">Mevcut Stok</label>
                            <input type="number" class="form-control @error('current_stock') is-invalid @enderror" 
                                id="current_stock" name="current_stock" value="{{ old('current_stock', 0) }}" required min="0">
                            @error('current_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="minimum_stock" class="form-label">Minimum Stok</label>
                            <input type="number" class="form-control @error('minimum_stock') is-invalid @enderror" 
                                id="minimum_stock" name="minimum_stock" value="{{ old('minimum_stock', 0) }}" required min="0">
                            @error('minimum_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="monthly_consumption" class="form-label">Aylık Tüketim</label>
                            <input type="number" class="form-control @error('monthly_consumption') is-invalid @enderror" 
                                id="monthly_consumption" name="monthly_consumption" value="{{ old('monthly_consumption', 0) }}" required min="0">
                            @error('monthly_consumption')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('items.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Geri
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 