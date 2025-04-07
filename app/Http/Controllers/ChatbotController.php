<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;

class ChatbotController extends Controller
{
    public function index()
    {
        $messages = ChatMessage::orderBy('created_at', 'asc')->get();
        return view('chatbot.index', compact('messages'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        // Kullanıcı mesajını kaydet
        $userMessage = ChatMessage::create([
            'content' => $request->message,
            'is_bot' => false
        ]);

        // Bot yanıtını oluştur
        $botResponse = $this->generateResponse($request->message);

        // Bot yanıtını kaydet
        $botMessage = ChatMessage::create([
            'content' => $botResponse,
            'is_bot' => true
        ]);

        return redirect()->back();
    }

    private function generateResponse($message)
    {
        // Basit bir yanıt mantığı
        $message = strtolower($message);
        
        if (str_contains($message, 'merhaba') || str_contains($message, 'selam')) {
            return 'Merhaba! Size nasıl yardımcı olabilirim?';
        }
        
        if (str_contains($message, 'stok') && str_contains($message, 'durum')) {
            return 'Stok durumunu kontrol etmek için ürünler sayfasını ziyaret edebilirsiniz.';
        }
        
        if (str_contains($message, 'kritik') && str_contains($message, 'stok')) {
            return 'Kritik stok seviyesindeki ürünleri bildirimler sayfasından görebilirsiniz.';
        }
        
        if (str_contains($message, 'yardım')) {
            return 'Size yardımcı olabileceğim konular:
- Stok durumu sorgulama
- Kritik stok seviyesi kontrolü
- Ürün ekleme/düzenleme
- Stok hareketleri görüntüleme';
        }
        
        return 'Üzgünüm, bu konuda size yardımcı olamadım. Lütfen sorunuzu daha açık bir şekilde belirtin veya "yardım" yazarak size yardımcı olabileceğim konuları görüntüleyin.';
    }
} 