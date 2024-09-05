<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ImageController extends Controller
{
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
    public function analyze($id)
    {
        $image = Image::findOrFail($id);
        $imagePath = public_path('uploads') . '/' . $image->filename;
    
        // Python betiğini çalıştırma komutunu hazırla
        $command = escapeshellcmd('python.exe ' . base_path('yolo_analyze.py') . ' ' . escapeshellarg($imagePath));
        
        // Çıktıyı ve dönüş değerini tutacak değişkenler
        $output = [];
        $return_var = null;
    
        // Python betiğini çalıştır ve sonucu al
        exec($command, $output, $return_var);
    
        // Çıktı ve dönüş değerine göre işlem yap
        Log::info('Python betiği çıktısı:', ['output_raw' => $output]);
    
        // Çıktıyı birleştir ve sadece JSON formatına uygun olan kısmı ayıkla
        $output_str = implode("\n", $output);
        Log::info('Python betiği birleşik çıktısı:', ['output_combined' => $output_str]);
    
        // JSON kısmını ayıklamak için, JSON formatına benzeyen kısmı bul
        if (preg_match('/\[{.*}\]/', $output_str, $matches)) {
            $json_str = $matches[0];  // Sadece JSON kısmını al
            Log::info('Ayıklanan JSON kısmı:', ['json_str' => $json_str]);
    
            // JSON'u çözümle
            $results = json_decode($json_str, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error: ' . json_last_error_msg());
                return redirect()->back()->withErrors(['error' => 'JSON decode error: ' . json_last_error_msg()]);
            }
    
            Log::info('JSON decode başarılı:', ['results' => $results]);
    
            return view('images.analyze', compact('image', 'results'));
        } else {
            Log::error('JSON formatına uygun veri bulunamadı.');
            return redirect()->back()->withErrors(['error' => 'JSON formatına uygun veri bulunamadı.']);
        }
    }
    
}
