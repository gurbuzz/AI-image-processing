<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceService
{
    protected $apiUrl = 'https://api-inference.huggingface.co/models/Ultralytics/YOLOv8';

    public function analyzeImage($imagePath)
    {
        try {
            // Görüntü dosyasını oku ve base64 formatına çevir
            $imageContent = base64_encode(file_get_contents($imagePath));
            Log::info('Image content read and encoded to base64 successfully.', ['image_path' => $imagePath]);
    
            // Hugging Face API'ye isteği gönder
            $response = Http::withOptions([
                'verify' => false, // SSL doğrulamasını devre dışı bırak
            ])->withHeaders([
                'Authorization' => 'Bearer ' . env('HUGGINGFACE_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'inputs' => $imageContent // Base64 olarak görüntü verisi gönderiliyor
            ]);
    
            if ($response->successful()) {
                Log::info('Received response from Hugging Face YOLOv8 API.', ['response' => $response->json()]);
                return $response->json();
            } else {
                Log::error('Error from Hugging Face API: ' . $response->body());
                return ['error' => 'An error occurred while processing the image. API Response: ' . $response->body()];
            }
    
        } catch (\Exception $e) {
            Log::error('Error occurred while analyzing image with YOLOv8.', ['error' => $e->getMessage()]);
            return [
                'error' => 'An error occurred during image analysis. Please check logs for details.',
            ];
        }
    }
}
