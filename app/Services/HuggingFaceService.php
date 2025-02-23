<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceService
{
    // API Anahtarınızı doğrudan buraya yerleştiriyoruz
    protected $apiKey = ''; // Buraya doğrudan anahtarınızı ekleyin

    public function analyzeImage($imagePath, $modelUrl)
    {
        try {
            // Model URL'si boşsa hata ver
            if (empty($modelUrl)) {
                throw new \Exception('Model URL is required but not provided.');
            }

            // Görüntü dosyasını oku ve base64 formatına çevir
            $imageContent = base64_encode(file_get_contents($imagePath));
            Log::info('Image content read and encoded to base64 successfully.', [
                'image_path' => $imagePath,
                'image_base64_size' => strlen($imageContent)
            ]);

            // Hugging Face API'ye isteği gönder
            $headers = [
                'Authorization' => 'Bearer ' . $this->apiKey, // API anahtarı doğrudan burada kullanılıyor
                'Content-Type' => 'application/json',
            ];

            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders($headers)
              ->post($modelUrl, [
                  'inputs' => [
                      'image' => $imageContent
                  ]
              ]);

            // Cevap başarılı ise logla ve sonucu döndür
            if ($response->successful()) {
                Log::info('Received response from Hugging Face API.', ['response' => $response->json()]);
                return $response->json();
            } else {
                Log::error('Error from Hugging Face API.', [
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
                return ['error' => 'An error occurred while processing the image. API Response: ' . $response->body()];
            }

        } catch (\Exception $e) {
            Log::error('Exception occurred while analyzing image with Hugging Face.', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'error' => 'An error occurred during image analysis. Please check logs for details.',
            ];
        }
    }
}
