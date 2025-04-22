@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Stok Analiz Grafikleri</h2>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Stok Durumu</h5>
                    <canvas id="stockStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Stok Takip Türü</h5>
                    <canvas id="stockTrackingChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Stok Hareketleri Analizi</h5>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="itemDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Ürün Seçin
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="itemDropdown">
                                @foreach($activeItems as $item)
                                <li><a class="dropdown-item" href="#" data-item-id="{{ $item->id }}">{{ $item->name }} {{ $item->unit }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div id="itemInfo" class="mb-3" style="display: none;">
                        <h6 class="text-muted">Seçili Ürün: <span id="selectedItemName"></span></h6>
                        <h6 class="text-muted">Birim: <span id="selectedItemUnit"></span></h6>
                    </div>
                    <canvas id="movementsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Stok Durumu Grafiği
    const stockStatusCtx = document.getElementById('stockStatusChart').getContext('2d');
    new Chart(stockStatusCtx, {
        type: 'pie',
        data: {
            labels: ['Normal Stok', 'Kritik Stok'],
            datasets: [{
                data: [{{ $totalItems - $criticalItems }}, {{ $criticalItems }}],
                backgroundColor: ['#36A2EB', '#FF6384']
            }]
        }
    });

    // Stok Takip Türü Grafiği
    const stockTrackingCtx = document.getElementById('stockTrackingChart').getContext('2d');
    new Chart(stockTrackingCtx, {
        type: 'doughnut',
        data: {
            labels: ['Otomatik', 'Manuel'],
            datasets: [{
                data: [{{ $stockTrackingDistribution['otomatik'] }}, {{ $stockTrackingDistribution['manuel'] }}],
                backgroundColor: ['#FF9F40', '#9966FF']
            }]
        }
    });

    // Stok Hareketleri Grafiği
    let movementsChart = null;
    const movementsCtx = document.getElementById('movementsChart').getContext('2d');

    // Dropdown item tıklama olayı
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const itemId = this.dataset.itemId;
            
            // Dropdown buton metnini güncelle
            document.getElementById('itemDropdown').textContent = this.textContent;
            
            // Grafik verilerini getir
            fetch(`/api/items/${itemId}/movements`)
                .then(response => response.json())
                .then(data => {
                    // Ürün bilgilerini göster
                    document.getElementById('itemInfo').style.display = 'block';
                    document.getElementById('selectedItemName').textContent = data.item.name;
                    document.getElementById('selectedItemUnit').textContent = data.item.unit;
                    
                    // Eğer önceki grafik varsa yok et
                    if (movementsChart) {
                        movementsChart.destroy();
                    }
                    
                    // Yeni grafiği oluştur
                    movementsChart = new Chart(movementsCtx, {
                        type: 'line',
                        data: {
                            labels: data.movements.dates,
                            datasets: [{
                                label: 'Stok Durumu',
                                data: data.movements.stock,
                                borderColor: '#4BC0C0',
                                tension: 0.1,
                                fill: true,
                                backgroundColor: 'rgba(75, 192, 192, 0.1)'
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
        });
    });
</script>
@endpush
@endsection 