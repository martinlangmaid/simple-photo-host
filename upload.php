<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $errorMsg = isset($errorMessages[$file['error']]) ? $errorMessages[$file['error']] : 'Unknown upload error';
    echo json_encode(['success' => false, 'error' => $errorMsg]);
    exit;
}

// Validate file type
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$fileType = mime_content_type($file['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only images are allowed.']);
    exit;
}

// Validate file size (max 10MB)
$maxSize = 10 * 1024 * 1024; // 10MB in bytes
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 10MB.']);
    exit;
}

// Check if uploads directory exists and is writable
$uploadsDir = __DIR__ . '/uploads/';
if (!is_dir($uploadsDir)) {
    echo json_encode(['success' => false, 'error' => 'Uploads directory does not exist']);
    exit;
}

if (!is_writable($uploadsDir)) {
    echo json_encode(['success' => false, 'error' => 'Uploads directory is not writable']);
    exit;
}

// Generate unique filename
$originalName = $file['name'];
$extension = pathinfo($originalName, PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $extension;
$uploadPath = $uploadsDir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Return success response
    echo json_encode([
        'success' => true,
        'filename' => $filename,
        'original_name' => $originalName,
        'size' => $file['size'],
        'type' => $fileType,
        'upload_time' => date('Y-m-d H:i:s')
    ]);
} else {
    // Get more specific error information
    $error = error_get_last();
    $errorMsg = 'Failed to save file';
    if ($error) {
        $errorMsg .= ': ' . $error['message'];
    }
    echo json_encode(['success' => false, 'error' => $errorMsg]);
}
?>