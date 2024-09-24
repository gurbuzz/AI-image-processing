<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analysis Result for {{ $image->filename }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Include Fabric.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.5.0/fabric.min.js"></script>
    <style>
        /* Optional styling */
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
    </style>
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
            <a href="{{ route('images.index') }}" class="btn btn-primary">Back to Image List</a>
        </div>
    </div>

    <script>
        // Initialize the Fabric.js canvas
        var canvas = new fabric.Canvas('imageCanvas');

        // Load the image onto the canvas
        fabric.Image.fromURL("{{ asset('uploads/' . $image->filename) }}", function(img) {
            // Get original image dimensions
            var imgWidth = img.width;
            var imgHeight = img.height;
            var canvasWidth = canvas.width;
            var canvasHeight = canvas.height;

            // Calculate scaling to fit the canvas
            var scaleX = canvasWidth / imgWidth;
            var scaleY = canvasHeight / imgHeight;
            var scale = Math.min(scaleX, scaleY);

            // Scale and center the image
            img.scale(scale);
            img.set({
                left: (canvasWidth - imgWidth * scale) / 2,
                top: (canvasHeight - imgHeight * scale) / 2,
                selectable: false
            });

            // Set the image as the background
            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));

            // Prepare the results data
            var resultsData = [];
            @if(is_array($results) && !empty($results))
                resultsData = {!! json_encode($results) !!};
            @endif

            // Draw bounding boxes if there are results
            if (resultsData.length > 0) {
                resultsData.forEach(function(result) {
                    var box = result.box || [];
                    if (box.length === 4) {
                        var x1 = box[0] * scale + img.left;
                        var y1 = box[1] * scale + img.top;
                        var x2 = box[2] * scale + img.left;
                        var y2 = box[3] * scale + img.top;

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
                    }
                });
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

    <!-- Include Bootstrap JS (optional) -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
</body>
</html>
