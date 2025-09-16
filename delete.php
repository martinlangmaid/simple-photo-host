<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['filename']) || empty($input['filename'])) {
    echo json_encode(['success' => false, 'error' => 'No filename provided']);
    exit;
}

$filename = $input['filename'];

// Validate filename to prevent directory traversal
if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
    echo json_encode(['success' => false, 'error' => 'Invalid filename']);
    exit;
}

$filePath = __DIR__ . '/uploads/' . $filename;

// Check if file exists
if (!file_exists($filePath)) {
    echo json_encode(['success' => false, 'error' => 'File not found']);
    exit;
}

// Check if it's actually in the uploads directory
$realPath = realpath($filePath);
$uploadsDir = realpath(__DIR__ . '/uploads/');

if (!$realPath || strpos($realPath, $uploadsDir) !== 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid file path']);
    exit;
}

// Delete the file
if (unlink($filePath)) {
    echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete file']);
}
?>