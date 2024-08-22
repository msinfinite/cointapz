<?php

$api_key = 'bd0b7e80d74ee83319effffe2a715e4f';
$movie_id = 550;  // This is the ID for "Fight Club"

$url = "https://api.themoviedb.org/3/movie/{$movie_id}?api_key={$api_key}";

// Create a stream context that disables SSL verification
// Note: This is not recommended for production use due to security implications
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
    'http' => [
        'method' => 'GET',
        'header' => [
            'Accept: application/json',
            'Content-Type: application/json',
        ],
    ],
]);

// Use file_get_contents with the created context
try {
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        echo "Failed to get contents from the URL.";
    } else {
        $data = json_decode($response, true);
        if (isset($data['title'])) {
            echo "API is working. Movie title: " . $data['title'];
        } else {
            echo "Couldn't find movie title in the response. Response: " . $response;
        }
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}

?>