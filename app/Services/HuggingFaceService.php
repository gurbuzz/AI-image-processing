<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceService
{
    protected $apiUrl = 'https://api-inference.huggingface.co/models/facebook/detr-resnet-50';

    public function analyzeImage($imagePath)
    {
        try {
            // Görüntü dosyasını oku ve base64 formatına çevir
            $imageContent = base64_encode(file_get_contents($imagePath));
            Log::info('Image content read and encoded to base64 successfully.', [
                'image_path' => $imagePath,
                'image_base64_size' => strlen($imageContent)
            ]);
    
            // Hugging Face API'ye isteği gönder
            $headers = [
                'Authorization' => 'Bearer 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' . env('HUGGING_FACE_API_KEY'),
                'Content-Type' => 'application/json',
            ];

            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders($headers)
              ->post($this->apiUrl, [
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
