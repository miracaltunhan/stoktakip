@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-robot me-2"></i>Stok Takip Asistanı
                    </h5>
                    <span class="badge bg-light text-primary">
                        <i class="fas fa-circle text-success me-1"></i>Çevrimiçi
                    </span>
                </div>

                <div class="card-body p-0">
                    <div class="chat-messages p-3" id="chat-messages" style="height: 400px; overflow-y: auto;">
                        <div class="message bot mb-3">
                            <div class="d-flex align-items-start">
                                <div class="avatar me-2">
                                    <i class="fas fa-robot fa-2x text-primary"></i>
                                </div>
                                <div class="message-content bg-light p-3 rounded">
                                    <p class="mb-0">Merhaba! Ben stok takip sisteminizin yapay zeka asistanıyım. Size nasıl yardımcı olabilirim?</p>
                                </div>
                            </div>
                        </div>

                        @foreach($messages as $message)
                            @if($message->is_bot)
                                <div class="message bot mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="avatar me-2">
                                            <i class="fas fa-robot fa-2x text-primary"></i>
                                        </div>
                                        <div class="message-content bg-light p-3 rounded">
                                            <p class="mb-0">{{ $message->content }}</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="message user mb-3">
                                    <div class="d-flex align-items-start justify-content-end">
                                        <div class="message-content bg-primary text-white p-3 rounded">
                                            <p class="mb-0">{{ $message->content }}</p>
                                        </div>
                                        <div class="avatar ms-2">
                                            <i class="fas fa-user fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="chat-input p-3 border-top">
                        <form action="{{ route('chatbot.send') }}" method="POST" class="d-flex">
                            @csrf
                            <input type="text" name="message" class="form-control me-2" placeholder="Mesajınızı yazın..." required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-messages {
    background-color: #f8f9fa;
}

.message-content {
    max-width: 80%;
    word-wrap: break-word;
}

.message.user .message-content {
    background-color: #007bff;
    color: white;
}

.avatar {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Scrollbar stilleri */
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
});
</script>
@endsection 