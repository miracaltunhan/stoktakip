@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Bildirim Detayı</h1>
        <div class="space-x-3">
            <a href="{{ route('notifications.edit', $notification) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Düzenle
            </a>
            <a href="{{ route('notifications.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Geri
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Başlık</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $notification->title }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Tür</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $notification->type === 'info' ? 'bg-blue-100 text-blue-800' : 
                               ($notification->type === 'warning' ? 'bg-yellow-100 text-yellow-800' : 
                               ($notification->type === 'error' ? 'bg-red-100 text-red-800' : 
                               'bg-green-100 text-green-800')) }}">
                            {{ ucfirst($notification->type) }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Durum</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $notification->is_read ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $notification->is_read ? 'Okundu' : 'Okunmadı' }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Oluşturulma Tarihi</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $notification->created_at }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Mesaj</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $notification->message }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Son Güncelleme</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $notification->updated_at }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection 