@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Stok Hareketi Detayı</h1>
        <div class="space-x-3">
            <a href="{{ route('stock-movements.edit', $stockMovement) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Düzenle
            </a>
            <a href="{{ route('stock-movements.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Geri
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Ürün</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $stockMovement->item->name }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Miktar</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $stockMovement->quantity }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Hareket Türü</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $stockMovement->type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $stockMovement->type === 'in' ? 'Giriş' : 'Çıkış' }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Tarih</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $stockMovement->date }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Notlar</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $stockMovement->notes ?: 'Not bulunmuyor' }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Oluşturulma Tarihi</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $stockMovement->created_at }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Son Güncelleme</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $stockMovement->updated_at }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection 