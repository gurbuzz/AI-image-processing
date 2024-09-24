<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .img-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2>Upload Image</h2>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
                    </div>
                    <div class="card-body">
                        @if ($message = Session::get('success'))
                            <div class="alert alert-success">
                                <strong>{{ $message }}</strong>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="image">Select Image:</label>
                                <input type="file" class="form-control-file" id="image" name="image" required onchange="previewImage(event)">
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>

                        <img id="imgPreview" src="" alt="Image Preview" class="img-preview d-none"/>

                        @if ($imagePath = Session::get('imagePath'))
                            <div class="mt-3">
                                <h4>Uploaded Image:</h4>
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="Uploaded Image" class="img-fluid img-preview">
                            </div>
                        @endif

                        <a href="http://127.0.0.1:8000/images" class="btn btn-info mt-3">Go to Images Page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            var input = event.target;
            var reader = new FileReader();
            reader.onload = function(){
                var imgPreview = document.getElementById('imgPreview');
                imgPreview.src = reader.result;
                imgPreview.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
