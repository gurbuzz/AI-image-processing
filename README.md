
# AI Image Processing with PHP Laravel and Hugging Face

A web application that allows users to upload images and analyze them using Hugging Face models. The project integrates AI-based image processing by analyzing objects in images, providing bounding boxes, labels, and confidence scores for detected objects.

## Features

- Upload images via a user-friendly web interface.
- Perform object detection on images using Hugging Face models.
- Visualize detected objects with bounding boxes and labels.
- Analyze multiple images and switch between different AI models.
- Simple form input to enter and use any Hugging Face model URL.

## Technologies Used

- **PHP Laravel**: Backend framework for managing image uploads and processing.
- **Hugging Face API**: AI models for image analysis and object detection.
- **Bootstrap**: Frontend framework for creating a responsive and clean user interface.
- **Fabric.js**: JavaScript library for rendering bounding boxes and labels on images.
- **Composer**: Dependency manager for PHP packages.
- **Node.js**: Used to manage frontend assets.

## Setup and Installation

### Prerequisites

Make sure you have the following installed on your system:

- **PHP 7.4+**
- **Composer**
- **Laravel 8.x+**
- **Node.js and npm**
- **Hugging Face API Key**

### Installation Steps

1. **Clone the repository**:

    ```
    git clone https://github.com/yourusername/ai-image-processing-laravel.git
    ```

2. **Navigate to the project directory**:

    ```
    cd ai-image-processing-laravel
    ```

3. **Install PHP dependencies**:

    ```
    composer install
    ```

4. **Install Node.js dependencies**:

    ```
    npm install && npm run dev
    ```

5. **Set up the environment configuration**:

    Copy the `.env.example` file to `.env`:

    ```
    cp .env.example .env
    ```

    Generate an application key:

    ```
    php artisan key:generate
    ```

6. **Configure the database**:

    In the `.env` file, configure your database connection (MySQL, SQLite, etc.):

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

    Then, run the database migrations:

    ```
    php artisan migrate
    ```

7. **Set up storage**:

    Link the storage folder to the public directory to store uploaded images:

    ```
    php artisan storage:link
    ```

8. **Obtain a Hugging Face API Key**:

    To use Hugging Face models, you need to create an API key:

    - Go to [Hugging Face](https://huggingface.co/).
    - Create an account if you don't already have one.
    - Navigate to [API tokens](https://huggingface.co/settings/tokens) and generate a new API key.

9. **Add the Hugging Face API Key to the `.env` file**:

    In your `.env` file, add your Hugging Face API key:

    ```
    HUGGING_FACE_API_KEY=your_hugging_face_api_key
    ```

10. **Run the Laravel application**:

    Start the Laravel development server:

    ```
    php artisan serve
    ```

    Access the application by visiting:

    ```
    http://127.0.0.1:8000/
    ```

## Usage

1. Navigate to the homepage and upload an image by clicking the **Upload New Image** button.
2. Once the image is uploaded, it will be displayed along with options for analysis.
3. Enter the Hugging Face model URL (e.g., `https://api-inference.huggingface.co/models/facebook/detr-resnet-50`) into the form.
4. Click **Analyze with Selected Model** to perform object detection on the uploaded image.
5. The detected objects, bounding boxes, and labels will be displayed on the image, along with a table showing confidence scores.

## How to Add Hugging Face Models

1. Go to the [Hugging Face Model Hub](https://huggingface.co/models) and find a model for image analysis.
2. Copy the API inference URL of the desired model, for example:

    ```
    https://api-inference.huggingface.co/models/facebook/detr-resnet-50
    ```

3. Paste this URL into the input field on the image analysis page when performing the analysis.

## License

This project is licensed under the MIT License.
