<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analysis Result for {{ $image->filename }}</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    
    <!-- Include Fabric.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.5.0/fabric.min.js"></script>

    <style>
        /* Flexbox container to center the canvas */
        .canvas-container {
            display: flex;
            justify-content: center;  /* Yatayda ortalama */
            align-items: center;      /* Dikeyde ortalama */
            height: 100%;             /* Yükseklik ayarı */
            margin-bottom: 40px;      /* Fotoğraf ve tablo arasına boşluk */
        }

        .table-responsive {
            margin-top: 40px; /* Fotoğraf ile tablo arasında boşluk */
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 10px;
            background-color: #f8f9fa;
        }

        .table thead th {
            background-color: #343a40;
            color: white;
            padding: 10px;
        }

        .table tbody td {
            background-color: white;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .alert {
            margin-top: 20px;
        }

        .btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">AI Image Processing</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('images.index') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('images.create') }}">Upload New Image</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main content -->
    <div class="container mt-5">

        <!-- Model URL form -->
        <form action="{{ route('images.analyze', ['id' => $image->id]) }}" method="POST" class="mb-4">
            @csrf
            <div class="form-group">
                <label for="model_url">Enter Hugging Face Model URL:</label>
                <input type="text" name="model_url" id="model_url" class="form-control" placeholder="https://api-inference.huggingface.co/models/facebook/detr-resnet-50" required>
            </div>
            <button type="submit" class="btn btn-primary">Analyze with Selected Model</button>
        </form>

        <!-- Display canvas for the image -->
        <div class="canvas-container">
            <canvas id="imageCanvas" width="800" height="600"></canvas>
        </div>

        <!-- Check if $results variable exists -->
        @if(isset($results) && is_array($results) && !empty($results))
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Score</th>
                            <th>Bounding Box (xmin, ymin, xmax, ymax)</th>
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
            <a href="{{ route('images.index') }}" class="btn btn-primary">Back to Image List</a>
        </div>
    </div>

    <script>
        // Initialize the Fabric.js canvas
        var canvas = new fabric.Canvas('imageCanvas');

        // Load the image onto the canvas
        fabric.Image.fromURL("{{ asset('uploads/' . $image->filename) }}", function(img) {
            var imgWidth = img.width;
            var imgHeight = img.height;
            var canvasWidth = canvas.width;
            var canvasHeight = canvas.height;

            var scaleX = canvasWidth / imgWidth;
            var scaleY = canvasHeight / imgHeight;
            var scale = Math.min(scaleX, scaleY);

            img.scaleToWidth(canvasWidth);
            img.set({
                left: (canvasWidth - imgWidth * scale) / 2,
                top: (canvasHeight - imgHeight * scale) / 2,
                selectable: false
            });

            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));

            // Check if there are results to draw bounding boxes
            @if(isset($results) && is_array($results) && !empty($results))
                var resultsData = {!! json_encode($results) !!};

                resultsData.forEach(function(result) {
                    var box = result.box || {};
                    if (box.xmin !== undefined && box.ymin !== undefined && box.xmax !== undefined && box.ymax !== undefined) {
                        var x1 = box.xmin * scaleX;
                        var y1 = box.ymin * scaleY;
                        var x2 = box.xmax * scaleX;
                        var y2 = box.ymax * scaleY;

                        var rect = new fabric.Rect({
                            left: x1,
                            top: y1,
                            width: x2 - x1,
                            height: y2 - y1,
                            fill: 'rgba(0, 0, 255, 0.1)', // Daha şeffaf
                            stroke: 'blue',
                            strokeWidth: 2,
                            selectable: false
                        });
                        canvas.add(rect);

                        var text = new fabric.Text(result.label || 'N/A', {
                            left: x1 + 5,
                            top: y1 + 5,
                            fontSize: 14,
                            fill: 'white',
                            backgroundColor: 'rgba(0, 0, 255, 0.5)', // Daha şeffaf
                            selectable: false
                        });
                        canvas.add(text);
                    }
                });
            @endif
        });

        // Optional: Resize the canvas when the window size changes
        window.addEventListener('resize', function() {
            var canvasContainer = document.getElementById('imageCanvas').parentElement;
            canvas.setWidth(canvasContainer.offsetWidth);
            canvas.setHeight(canvasContainer.offsetHeight);
            canvas.renderAll();
        });
    </script>

    <!-- Bootstrap JS (optional) -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
</body>
</html>
