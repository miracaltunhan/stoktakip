@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Yeni Ürün Ekle</h5>
                    <a href="{{ route('items.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Geri
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('items.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Ürün Adı</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" 
                                class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea name="description" id="description" rows="3" 
                                class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="unit" class="form-label">Birim</label>
                                <select name="unit" id="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                    <option value="adet" {{ old('unit') == 'adet' ? 'selected' : '' }}>Adet</option>
                                    <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                    <option value="lt" {{ old('unit') == 'lt' ? 'selected' : '' }}>Litre (lt)</option>
                                    <option value="paket" {{ old('unit') == 'paket' ? 'selected' : '' }}>Paket</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="current_stock" class="form-label">Mevcut Stok</label>
                                <input type="number" name="current_stock" id="current_stock" value="{{ old('current_stock') }}" 
                                    class="form-control @error('current_stock') is-invalid @enderror" required min="0">
                                @error('current_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="minimum_stock" class="form-label">Minimum Stok</label>
                                <input type="number" name="minimum_stock" id="minimum_stock" value="{{ old('minimum_stock') }}" 
                                    class="form-control @error('minimum_stock') is-invalid @enderror" required min="0">
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="stock_tracking_type" class="form-label">Stok Takip Tipi</label>
                                <select name="stock_tracking_type" id="stock_tracking_type" class="form-select @error('stock_tracking_type') is-invalid @enderror" required>
                                    <option value="manuel" {{ old('stock_tracking_type') == 'manuel' ? 'selected' : '' }}>Manuel</option>
                                    <option value="otomatik" {{ old('stock_tracking_type') == 'otomatik' ? 'selected' : '' }}>Otomatik</option>
                                </select>
                                @error('stock_tracking_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3" id="weekly_consumption_container" style="display: {{ old('stock_tracking_type') == 'otomatik' ? 'block' : 'none' }};">
                                <label for="weekly_consumption" class="form-label">Haftalık Tüketim</label>
                                <input type="number" name="weekly_consumption" id="weekly_consumption" value="{{ old('weekly_consumption') }}" 
                                    class="form-control @error('weekly_consumption') is-invalid @enderror" min="0">
                                @error('weekly_consumption')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('items.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times"></i> İptal
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stockTrackingType = document.getElementById('stock_tracking_type');
        const weeklyConsumptionContainer = document.getElementById('weekly_consumption_container');

        function toggleWeeklyConsumption() {
            if (stockTrackingType.value === 'otomatik') {
                weeklyConsumptionContainer.style.display = 'block';
                document.getElementById('weekly_consumption').setAttribute('required', 'required');
            } else {
                weeklyConsumptionContainer.style.display = 'none';
                document.getElementById('weekly_consumption').removeAttribute('required');
            }
        }

        stockTrackingType.addEventListener('change', toggleWeeklyConsumption);
        toggleWeeklyConsumption(); // Sayfa yüklendiğinde de kontrol et
    });
</script>
@endpush 