<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analysis Result for {{ $image->filename }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.5.0/fabric.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Analysis Result for {{ $image->filename }}</h2>

        <div class="text-center mb-4">
            <canvas id="imageCanvas" width="800" height="600"></canvas>
        </div>

        @if(is_array($results) && !empty($results))
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Label</th>
                            <th>Score</th>
                            <th>Bounding Box (x1, y1, x2, y2)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $result)
                            <tr>
                                <td>{{ $result['label'] ?? 'N/A' }}</td>
                                <td>{{ number_format($result['score'] ?? 0, 2) }}</td>
                                <td>{{ json_encode($result['box'] ?? []) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning text-center" role="alert">
                No results found or an error occurred during analysis.
            </div>
        @endif

        <div class="text-center">
            <a href="{{ route('images.index') }}" class="btn btn-primary mt-3">Back to Image List</a>
        </div>
    </div>

    <script>
        var canvas = new fabric.Canvas('imageCanvas');

        // Resmi Canvas'a ekle
        fabric.Image.fromURL("{{ asset('uploads/' . $image->filename) }}", function(img) {
            // Orijinal resim boyutlarını ve canvas boyutlarını kontrol et
            var imgWidth = img.width;
            var imgHeight = img.height;
            var canvasWidth = canvas.width;
            var canvasHeight = canvas.height;

            // Resmi canvas'a uygun şekilde scale et
            var scaleX = canvasWidth / imgWidth;
            var scaleY = canvasHeight / imgHeight;
            var scale = Math.min(scaleX, scaleY);

            // Resmi merkezleyerek canvas'a yerleştir
            img.scale(scale).set({
                left: (canvasWidth - imgWidth * scale) / 2,
                top: (canvasHeight - imgHeight * scale) / 2
            });

            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));

            // Bounding box'ları çiz
            @foreach($results as $result)
                var box = {{ json_encode($result['box']) }};
                var x1 = box[0][0] * scale;
                var y1 = box[0][1] * scale;
                var x2 = box[0][2] * scale;
                var y2 = box[0][3] * scale;

                // Koordinatları kontrol et
                var rect = new fabric.Rect({
                    left: x1 + img.left, // Koordinatları resmi merkeze kaydır
                    top: y1 + img.top,
                    width: (x2 - x1),
                    height: (y2 - y1),
                    fill: 'rgba(0, 0, 255, 0.3)', // Şeffaf mavi
                    stroke: 'blue', // Kenarlık rengi
                    strokeWidth: 2,
                    selectable: false
                });
                canvas.add(rect);
            @endforeach
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
