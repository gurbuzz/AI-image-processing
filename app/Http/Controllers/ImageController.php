<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use App\Services\HuggingFaceService;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    protected $huggingFaceService;

    public function __construct(HuggingFaceService $huggingFaceService)
    {
        $this->huggingFaceService = $huggingFaceService;
    }

    public function index()
    {
        $images = Image::all();
        return view('images.index', compact('images'));
    }

    public function create()
    {
        Log::info('Image upload form accessed.');
        return view('images.create');
    }

    public function destroy($id)
    {
        try {
            $image = Image::findOrFail($id);

            // Dosyayı sunucudan sil
            $imagePath = public_path('uploads') . '/' . $image->filename;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // Veritabanından kaydı sil
            $image->delete();

            return redirect()->route('images.index')
                            ->with('success', 'Image deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('images.index')
                            ->withErrors(['error' => 'Failed to delete image.']);
        }
    }

    public function store(Request $request)
    {
        Log::info('Image upload request received.', ['request' => $request->all()]);

        $validatedData = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        Log::info('Request validation passed.', ['validated_data' => $validatedData]);

        $imageName = null;

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            
            Log::info('Image file detected.', ['image_name' => $imageName]);

            try {
                $request->image->move(public_path('uploads'), $imageName);
                Log::info('Image moved to public/uploads directory successfully.', ['image_path' => public_path('uploads') . '/' . $imageName]);
            } catch (\Exception $e) {
                Log::error('Failed to move the image file.', ['error' => $e->getMessage()]);
                return redirect()->back()->withErrors(['error' => 'Failed to upload image. Please try again.']);
            }
        } else {
            Log::warning('No image file detected in the request.');
        }

        try {
            Log::info('Starting to save image record to the database.');

            $image = new Image;
            $image->filename = $imageName;
            $image->save();

            Log::info('Image record saved successfully.', ['image_id' => $image->id]);

        } catch (\Exception $e) {
            Log::error('Failed to save image record to the database.', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Failed to save image information. Please try again.']);
        }

        return redirect()->route('images.index')
                         ->with('success', 'Image uploaded successfully.');
    }

    // Görüntü analizi fonksiyonu
    public function analyze(Request $request, $id)
    {
        // Resmi bul
        $image = Image::findOrFail($id);
        $imagePath = public_path('uploads') . '/' . $image->filename;
    
        // Eğer GET isteği ise sadece formu göster
        if ($request->isMethod('get')) {
            return view('images.analyze', compact('image')); // GET isteğinde sadece 'image' değişkenini gönderiyoruz
        }
    
        // POST isteği ise Hugging Face model URL'sini al
        $modelUrl = $request->input('model_url');
    
        // Model URL'sinin boş olup olmadığını kontrol edin
        if (empty($modelUrl)) {
            return redirect()->back()->withErrors(['error' => 'Model URL is required.']);
        }
    
        // Hugging Face Servisini kullanarak analizi başlat
        $results = $this->huggingFaceService->analyzeImage($imagePath, $modelUrl);
    
        // Sonuçlarla birlikte view'e dön
        return view('images.analyze', compact('image', 'results')); // Hem 'image' hem de 'results' değişkenini view'e gönderiyoruz
    }
    
}
