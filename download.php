<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encoded Image</title>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <!-- Header -->
    <header class="w-full bg-teal-500 text-black py-4 shadow-md">
        <h1 class="text-center text-2xl font-bold">
            <a href="./">PHP Steg</a>
        </h1>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col items-center justify-center p-6">
        <div class="bg-white shadow-lg rounded-lg p-6 max-w-md w-full text-center">
            <h1 class="text-xl font-semibold mb-6">Encoded Image</h1>
            <!-- Image Preview -->
            <div class="image-preview mb-6">
                <img id="imagePreview" src="./uploads/result.png" alt="Image Preview" class="rounded-lg mx-auto w-full max-w-xs">
            </div>
            <!-- Download Button -->
            <a id="downloadLink" href="./uploads/result.png" download class="block bg-teal-500 text-black px-4 py-2 rounded-lg hover:bg-teal-600 transition">
                Download
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full bg-teal-500 text-black py-4">
        <p class="text-center">
            Developed By: 
            <a href="https://github.com/abdulrehman-03" target="_blank" class="border-2 border-black rounded-md px-3 py-1 hover:bg-teal-600 hover:text-white transition">
                Syed Abdulrehman
            </a>
        </p>
    </footer>

    <script>
        const imagePath = './uploads/result.png'; // Path to the image

        const imgElement = document.getElementById('imagePreview');
        const downloadLink = document.getElementById('downloadLink');
        const imagePreviewContainer = document.querySelector('.image-preview');

        // Check if the image exists at the given path
        const img = new Image();
        img.src = imagePath;
        img.onload = function() {
            imgElement.src = imagePath;
            downloadLink.href = imagePath;
            imagePreviewContainer.style.display = 'block';
            downloadLink.style.display = 'inline-block';
        };
        img.onerror = function() {
            alert('Image not found at ' + imagePath);
        };
    </script>

</body>
</html>
