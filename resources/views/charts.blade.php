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
                        <div class="d-flex align-items-center">
                            <div class="dropdown me-3">
                                <button class="btn btn-primary dropdown-toggle" type="button" id="itemDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    Ürün Seçin
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="itemDropdown">
                                    @foreach($activeItems as $item)
                                    <li><a class="dropdown-item" href="#" data-item-id="{{ $item->id }}">{{ $item->name }} {{ $item->unit }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            <button id="downloadPdf" class="btn btn-success" style="display: none;">
                                <i class="fas fa-file-pdf"></i> PDF Olarak İndir
                            </button>
                        </div>
                    </div>
                    <div id="itemInfo" class="mb-3" style="display: none;">
                        <h6 class="text-muted">Seçili Ürün: <span id="selectedItemName"></span></h6>
                        <h6 class="text-muted">Birim: <span id="selectedItemUnit"></span></h6>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Aylık Ortalama Tüketim</h6>
                                        <p class="card-text" id="avgMonthlyConsumption"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Mevcut Stok</h6>
                                        <p class="card-text" id="currentStock"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Kritik Seviyeye Kalan Süre</h5>
                                        <p class="card-text" id="monthsUntilCritical"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Stok Durumu ve Tahmin</h5>
                                    <canvas id="stockChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Tüketim Tahmini</h5>
                                    <canvas id="forecastChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
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
    let stockChart = null;
    const stockCtx = document.getElementById('stockChart').getContext('2d');
    
    // Tahmin Grafiği
    let forecastChart = null;
    const forecastCtx = document.getElementById('forecastChart').getContext('2d');

    // Dropdown item tıklama olayı
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const itemId = this.dataset.itemId;
            
            // Dropdown buton metnini güncelle
            document.getElementById('itemDropdown').textContent = this.textContent;
            
            // PDF indirme butonunu göster
            document.getElementById('downloadPdf').style.display = 'block';
            
            // Grafik verilerini getir
            fetch(`/api/items/${itemId}/movements`)
                .then(response => response.json())
                .then(data => {
                    updateCharts(data);
                })
                .catch(error => console.error('Hata:', error));
        });
    });

    function updateCharts(data) {
        // Ürün bilgilerini göster
        document.getElementById('itemInfo').style.display = 'block';
        document.getElementById('selectedItemName').textContent = data.item.name;
        document.getElementById('selectedItemUnit').textContent = data.item.unit;
        
        // Tüketim istatistiklerini göster
        document.getElementById('avgMonthlyConsumption').textContent = 
            data.movements.consumption_stats.has_movements ? 
            `${data.movements.consumption_stats.avg_monthly} ${data.item.unit}/ay` :
            data.movements.consumption_stats.has_weekly_consumption ?
            `${data.movements.consumption_stats.avg_monthly} ${data.item.unit}/ay (Haftalık tüketimden hesaplanmıştır)` :
            'Henüz tüketim verisi yok';
        
        document.getElementById('currentStock').textContent = 
            `${data.movements.consumption_stats.current_stock} ${data.item.unit}`;
        
        // Kritik seviye bilgilerini güncelle
        const monthsUntilCritical = data.movements.consumption_stats.months_until_critical;
        let criticalText = 'Kritik seviye belirlenmemiş';
        
        if (data.movements.consumption_stats.has_minimum_stock) {
            if (monthsUntilCritical > 0) {
                criticalText = `${monthsUntilCritical} ay (Kritik Seviye: ${data.movements.consumption_stats.minimum_stock} ${data.item.unit})`;
                if (data.movements.consumption_stats.is_near_critical) {
                    criticalText += ' (Dikkat: Kritik seviyeye yaklaşılıyor!)';
                }
            } else {
                criticalText = `Kritik seviyeye ulaşıldı! (Kritik Seviye: ${data.movements.consumption_stats.minimum_stock} ${data.item.unit})`;
            }
        }
        
        document.getElementById('monthsUntilCritical').textContent = criticalText;
        
        // Eğer önceki grafikler varsa yok et
        if (stockChart) {
            stockChart.destroy();
        }
        if (forecastChart) {
            forecastChart.destroy();
        }
        
        // Stok Durumu Grafiği
        stockChart = new Chart(stockCtx, {
            type: 'line',
            data: {
                labels: data.movements.dates,
                datasets: [{
                    label: 'Stok Durumu',
                    data: data.movements.stock,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Stok Miktarı (' + data.item.unit + ')'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tarih'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Stok: ' + context.raw + ' ' + data.item.unit;
                            }
                        }
                    }
                }
            }
        });

        // Tahmin Grafiği
        forecastChart = new Chart(forecastCtx, {
            type: 'line',
            data: {
                labels: data.movements.forecast.dates,
                datasets: [{
                    label: 'Tahmin',
                    data: data.movements.forecast.values,
                    borderColor: 'rgb(255, 159, 64)',
                    tension: 0.1
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
    }

    $('#downloadPdf').click(function() {
        const selectedItem = document.querySelector('.dropdown-item.active');
        if (selectedItem) {
            const itemId = selectedItem.dataset.itemId;
            window.location.href = '/dashboard/download-report/' + itemId;
        }
    });
</script>
@endpush
@endsection 