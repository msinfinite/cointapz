<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to validate URL
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) 
           && strpos($url, 'https://media.themoviedb.org/') === 0;
}

// Check if URL parameter is set
if (isset($_GET['url'])) {
    $image_url = $_GET['url'];
    
    // Validate URL
    if (isValidUrl($image_url)) {
        // Fetch the image
        $image_data = file_get_contents($image_url);
        
        if ($image_data !== false) {
            // Determine image type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->buffer($image_data);
            
            // Set appropriate headers
            header("Content-Type: $mime_type");
            header("Cache-Control: public, max-age=86400"); // Cache for 1 day
            
            // Output image data
            echo $image_data;
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "Failed to fetch image";
        }
    } else {
        header("HTTP/1.0 400 Bad Request");
        echo "Invalid URL";
    }
} else {
    header("HTTP/1.0 400 Bad Request");
    echo "No URL provided";
}
