<?php
include('functions.php');
session_start();

$decryptedMessage = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $uploadDir = 'uploads/';
    
    // Validate file upload
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "File upload error: " . $_FILES['image']['error'];
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // Validate file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['image']['tmp_name']);
    if ($mime !== 'image/png') {
        $_SESSION['error'] = "Only PNG images are supported";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    $src = $uploadDir . basename($_FILES['image']['name']);
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $src)) {
        $img = imagecreatefrompng($src);
        if (!$img) {
            unlink($src);
            $_SESSION['error'] = "Invalid PNG file";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }

        $real_message = '';
        $count = 0;
        $pixelX = 0;
        $pixelY = 0;
        list($width, $height) = getimagesize($src);

        for ($x = 0; $x < $width * $height; $x++) {
            if ($pixelX >= $width) {
                $pixelY++;
                $pixelX = 0;
            }
            if ($pixelY >= $height) break;

            $rgb = imagecolorat($img, $pixelX, $pixelY);
            $b = $rgb & 0xFF;
            $blue = toBin($b);
            $real_message .= $blue[strlen($blue) - 1];

            $count++;
            $pixelX++;

            if ($count === 8) {
                if (toString(substr($real_message, -8)) === '|') {
                    $decryptedMessage = toString(substr($real_message, 0, -8));
                    $_SESSION['decryptedMessage'] = $decryptedMessage;
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit();
                }
                $count = 0;
            }
        }

        // If we reached here, no message found
        unlink($src);
        $_SESSION['error'] = "No hidden message found";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();

    } else {
        $_SESSION['error'] = "Failed to move uploaded file";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Retrieve messages from session
$decryptedMessage = $_SESSION['decryptedMessage'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['decryptedMessage'], $_SESSION['error']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decode Image</title>
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
            <h2 class="text-xl font-semibold mb-4">Upload an Image with Hidden Message</h2>

            <form id="uploadForm" class="flex flex-col items-center" action="" method="POST" enctype="multipart/form-data">
                <div class="mb-4 w-full">
                    <label for="image" class="block text-gray-700 font-medium mb-2 mt-2">
                        Drag and drop an image or click to select:
                    </label>
                    <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer h-40 w-90 mt-6">
                        <p class="text-gray-500 mt-9" id="dropText">Drag and drop an image here or click to select</p>
                        <input type="file" id="image" name="image" accept="image/png" class="hidden" />
                    </div>
                </div>

                <div id="previewContainer" class="mb-4 w-full hidden">
                    <p class="text-gray-700 font-medium mb-2">Image Preview:</p>
                    <img id="previewImage" class="max-w-full rounded-lg" width="500" height="600" />
                </div>

                <button type="submit" class="bg-teal-500 text-black px-4 py-2 rounded-lg hover:bg-teal-600">
                    Decode
                </button>
            </form>

            <?php if (!empty($decryptedMessage)): ?>
                <div class="mt-6 p-4 bg-green-100 text-green-800 rounded-lg">
                    <h3 class="text-lg font-semibold">Decrypted Message:</h3>
                    <p class="break-words whitespace-pre-wrap"><?= htmlspecialchars($decryptedMessage) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="mt-6 p-4 bg-red-100 text-red-800 rounded-lg">
                    <h3 class="text-lg font-semibold">Error:</h3>
                    <p class="break-words"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>
            
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