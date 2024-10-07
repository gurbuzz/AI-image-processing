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
        /* Navbar styling */
        .navbar {
            background-color: #343a40;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .navbar h1 {
            margin: 0;
            font-size: 24px;
            color: white;
        }

        .canvas-container {
            margin: 0 auto;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .alert {
            margin-top: 20px;
        }

        .btn {
            margin-top: 20px;
        }

        /* Optional styling for canvas and table */
        canvas {
            border: 1px solid #ccc;
            margin-top: 20px;
        }

        table {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <h1>AI Image Processing</h1>
    </div>

    <!-- Main content -->
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Analysis Result for {{ $image->filename }}</h2>

        <div class="text-center mb-4">
            <canvas id="imageCanvas" width="800" height="600"></canvas>
        </div>

        @if(is_array($results) && !empty($results))
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
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

        // Log the initial canvas dimensions
        console.log('Initial Canvas Dimensions:', { width: canvas.width, height: canvas.height });

        // Load the image onto the canvas
        fabric.Image.fromURL("{{ asset('uploads/' . $image->filename) }}", function(img) {
            console.log('Image loaded:', img);

            var imgWidth = img.width;
            var imgHeight = img.height;
            console.log('Image dimensions:', { imgWidth, imgHeight });

            var canvasWidth = canvas.width;
            var canvasHeight = canvas.height;
            console.log('Canvas dimensions:', { canvasWidth, canvasHeight });

            var scaleX = canvasWidth / imgWidth;
            var scaleY = canvasHeight / imgHeight;
            var scale = Math.min(scaleX, scaleY);
            console.log('Scale factors:', { scaleX, scaleY, scale });

            img.scaleToWidth(canvasWidth);
            img.set({
                left: (canvasWidth - imgWidth * scale) / 2,
                top: (canvasHeight - imgHeight * scale) / 2,
                selectable: false
            });

            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));

            // Prepare the results data
            var resultsData = [];
            @if(is_array($results) && !empty($results))
                resultsData = {!! json_encode($results) !!};
                console.log('Analysis results:', resultsData);
            @else
                console.log('No results found or error in analysis.');
            @endif

            // Draw bounding boxes if there are results
            if (resultsData.length > 0) {
                resultsData.forEach(function(result) {
                    var box = result.box || {};
                    if (box.xmin !== undefined && box.ymin !== undefined && box.xmax !== undefined && box.ymax !== undefined) {
                        // Bounding box coordinates, scaled to fit the canvas
                        var x1 = box.xmin * scaleX;
                        var y1 = box.ymin * scaleY;
                        var x2 = box.xmax * scaleX;
                        var y2 = box.ymax * scaleY;

                        console.log('Label:', result.label);
                        console.log('Box:', box);
                        console.log('Coordinates:', { x1, y1, x2, y2 });

                        var rect = new fabric.Rect({
                            left: x1,
                            top: y1,
                            width: x2 - x1,
                            height: y2 - y1,
                            fill: 'rgba(0, 0, 255, 0.3)', // Semi-transparent blue
                            stroke: 'blue',
                            strokeWidth: 2,
                            selectable: false
                        });
                        canvas.add(rect);

                        // Add label text
                        var text = new fabric.Text(result.label || 'N/A', {
                            left: x1 + 5,
                            top: y1 + 5,
                            fontSize: 14,
                            fill: 'white',
                            backgroundColor: 'rgba(0, 0, 255, 0.7)',
                            selectable: false
                        });
                        canvas.add(text);
                    } else {
                        console.log('Bounding box format is incorrect or empty for label:', result.label);
                    }
                });
            } else {
                console.log('No bounding boxes found in the results.');
            }
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
