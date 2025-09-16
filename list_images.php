<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$uploadsDir = __DIR__ . '/uploads/';
$images = [];

// Check if uploads directory exists
if (!is_dir($uploadsDir)) {
    echo json_encode(['success' => true, 'images' => []]);
    exit;
}

// Get all files in uploads directory
$files = scandir($uploadsDir);

foreach ($files as $file) {
    // Skip hidden files and directories
    if ($file === '.' || $file === '..' || strpos($file, '.') === 0) {
        continue;
    }
    
    $filePath = $uploadsDir . $file;
    
    // Check if it's a file and an image
    if (is_file($filePath) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $images[] = [
            'filename' => $file,
            'original_name' => getOriginalName($file),
            'size' => filesize($filePath),
            'upload_time' => filemtime($filePath),
            'url' => 'uploads/' . $file
        ];
    }
}

// Sort by upload time (newest first)
usort($images, function($a, $b) {
    return $b['upload_time'] - $a['upload_time'];
});

// Format upload time for display
foreach ($images as &$image) {
    $image['upload_time_formatted'] = date('Y-m-d H:i:s', $image['upload_time']);
}

echo json_encode(['success' => true, 'images' => $images]);

// Helper function to extract original name from filename
function getOriginalName($filename) {
    // Remove the unique ID and timestamp prefix
    $parts = explode('_', $filename);
    if (count($parts) >= 3) {
        // Remove first two parts (unique ID and timestamp)
        array_shift($parts);
        array_shift($parts);
        return implode('_', $parts);
    }
    return $filename;
}
?>