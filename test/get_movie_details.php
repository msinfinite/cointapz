<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$api_key = 'bd0b7e80d74ee83319effffe2a715e4f'; // Replace with your actual TMDB API key
$base_url = 'https://api.themoviedb.org/3';
$image_base_url = 'image_proxy.php?url=' . urlencode('https://media.themoviedb.org/t/p/original');

function makeApiRequest($endpoint) {
    global $api_key, $base_url;
    $url = $base_url . $endpoint . (strpos($endpoint, '?') !== false ? '&' : '?') . 'api_key=' . $api_key;
    
    $response = file_get_contents($url);
    if ($response === FALSE) {
        throw new Exception("Failed to make API request to: " . $url);
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Failed to parse JSON response: " . json_last_error_msg());
    }
    
    return $data;
}

if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];

    try {
        // Get full movie details
        $movie_details = makeApiRequest("/movie/$movie_id?append_to_response=credits");

        // Get movie backdrops
        $images = makeApiRequest("/movie/$movie_id/images?");
        $backdrops = array_slice($images['backdrops'], 0, 5);

        // Get movie recommendations
        $recommendations = makeApiRequest("/movie/$movie_id/recommendations?");
        $recommendations = array_slice($recommendations['results'], 0, 4);

        $director = array_filter($movie_details['credits']['crew'], function($person) {
            return $person['job'] === 'Director';
        });
        $director = reset($director);

        $movie_data = [
            'title' => $movie_details['title'],
            'poster' => $image_base_url . $movie_details['poster_path'],
            'release_date' => $movie_details['release_date'],
            'vote_average' => $movie_details['vote_average'],
            'overview' => $movie_details['overview'],
            'genres' => array_map(function($genre) { return $genre['name']; }, $movie_details['genres']),
            'runtime' => $movie_details['runtime'],
            'budget' => $movie_details['budget'],
            'revenue' => $movie_details['revenue'],
            'profit' => $movie_details['revenue'] - $movie_details['budget'],
            'director' => $director ? $director['name'] : 'Unknown',
            'cast' => array_slice(array_map(function($person) use ($image_base_url) {
                return [
                    'name' => $person['name'],
                    'character' => $person['character'],
                    'profile_path' => $person['profile_path'] ? $image_base_url . $person['profile_path'] : null
                ];
            }, $movie_details['credits']['cast']), 0, 6),
            'backdrops' => array_map(function($backdrop) use ($image_base_url) {
                return $image_base_url . $backdrop['file_path'];
            }, $backdrops),
            'recommendations' => array_map(function($rec) use ($image_base_url) {
                return [
                    'id' => $rec['id'],
                    'title' => $rec['title'],
                    'poster' => $rec['poster_path'] ? $image_base_url . $rec['poster_path'] : null
                ];
            }, $recommendations)
        ];

        header('Content-Type: application/json');
        echo json_encode($movie_data);
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Movie ID is required']);
}
