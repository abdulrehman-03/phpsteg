<?php
session_start();
include('functions.php');

$savepath = './uploads/result.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image'], $_POST['message'])) {
        try {
            // Validate message
            $msg = trim($_POST['message']);
            if (empty($msg)) {
                throw new Exception("Message cannot be empty");
            }
            $msg .= '|';
            
            // Validate file upload
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Upload error: " . $_FILES['image']['error']);
            }

            // Validate image type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['image']['tmp_name']);
            if ($mime !== 'image/jpeg') {
                throw new Exception("Only JPEG images are supported");
            }

            // Process upload
            $uploadDir = 'uploads/';
            $src = $uploadDir . basename($_FILES['image']['name']);
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $src)) {
                throw new Exception("Failed to move uploaded file");
            }

            // Create image resource
            $img = imagecreatefromjpeg($src);
            if (!$img) {
                unlink($src);
                throw new Exception("Invalid JPEG file");
            }

            // Validate message length
            $msgBin = toBin($msg);
            $msgLength = strlen($msgBin);
            list($width, $height) = getimagesize($src);
            
            if ($msgLength > ($width * $height)) {
                unlink($src);
                imagedestroy($img);
                throw new Exception("Message too long for selected image");
            }

            // Encode message
            $pixelX = $pixelY = 0;
            for ($x = 0; $x < $msgLength; $x++) {
                if ($pixelX >= $width) {
                    $pixelY++;
                    $pixelX = 0;
                }
                if ($pixelY >= $height) break;

                $rgb = imagecolorat($img, $pixelX, $pixelY);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $newB = toBin($b);
                $newB[strlen($newB) - 1] = $msgBin[$x];
                $newB = toString($newB);

                $new_color = imagecolorallocate($img, $r, $g, $newB);
                imagesetpixel($img, $pixelX, $pixelY, $new_color);
                $pixelX++;
            }

            // Save and cleanup
            imagepng($img, $savepath);
            imagedestroy($img);
            unlink($src); // Remove original upload
            
            $_SESSION['success'] = "Message encoded successfully! Download your image below.";
            header("Location: download.php");
            exit();

        } catch (Exception $e) {
            // Cleanup on error
            if (isset($src) && file_exists($src)) unlink($src);
            if (isset($img) && $img) imagedestroy($img);
            
            $_SESSION['error'] = $e->getMessage();
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encode Image</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .drop-zone-active {
            background-color: #f0fdf4;
            border-color: #4ade80;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center">
    <header class="w-full bg-teal-500 text-black py-4 shadow-md">
        <h1 class="text-center text-2xl font-bold">
            <a href="./" class="text-center block">PHP Steg</a>
        </h1>
    </header>

    <main class="flex-grow container mx-auto mt-8 px-4">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Upload and Encode a Message</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                    <h3 class="text-lg font-semibold">Error:</h3>
                    <p><?= htmlspecialchars($_SESSION['error']) ?></p>
                </div>
                <?php unset($_SESSION['error']) ?>
            <?php endif; ?>

            <form class="flex flex-col items-center" method="POST" enctype="multipart/form-data">
                <div class="mb-4 w-full">
                    <label for="image" class="block text-gray-700 font-medium mb-2">
                        Select JPEG Image:
                    </label>
                    <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer h-40">
                        <p class="text-gray-500 mt-4" id="dropText">Drag and drop JPEG file or click to select</p>
                        <input type="file" id="image" name="image" accept="image/jpeg" class="hidden" required>
                    </div>
                </div>

                <div id="previewContainer" class="mb-4 w-full hidden">
                    <p class="text-gray-700 font-medium mb-2">Image Preview:</p>
                    <img id="previewImage" class="max-w-full rounded-lg" width="500">
                </div>

                <div class="w-full mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Secret Message:</label>
                    <textarea name="message" rows="4" class="w-full border p-2 rounded-lg" placeholder="Type your message..."></textarea>
                </div>

                <button type="submit" class="bg-teal-500 text-black px-6 py-2 rounded-lg hover:bg-teal-600 transition-colors">
                    Encode Message
                </button>
            </form>
        </div>
    </main>

    <footer class="w-full bg-teal-500 text-black py-4 mt-8">
        <p class="text-center">
            Developed By: 
            <a href="https://github.com/abdulrehman-03" target="_blank" class="border-2 border-black rounded-md px-3 py-1 hover:bg-teal-600 hover:text-white transition">
                Syed Abdulrehman
            </a>
        </p>
    </footer>

    <script src="script.js"></script>
</body>
</html>