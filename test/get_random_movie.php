<?php
$api_key = 'bd0b7e80d74ee83319effffe2a715e4f';
$base_url = 'https://api.themoviedb.org/3';
$image_base_url = 'image_proxy.php?url=' . urlencode('https://media.themoviedb.org/t/p/original');

function makeApiRequest($endpoint) {
    global $api_key, $base_url;
    $url = $base_url . $endpoint . '&api_key=' . $api_key;
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Get a random movie from 1980 to present
$current_year = date('Y');
$random_year = rand(1980, $current_year);
$random_page = rand(1, 5); // Assuming there are at least 5 pages for each year

$discover_movies = makeApiRequest('/discover/movie?sort_by=popularity.desc&primary_release_year=' . $random_year . '&page=' . $random_page);
$random_movie = $discover_movies['results'][array_rand($discover_movies['results'])];

// Get full movie details
$movie_details = makeApiRequest('/movie/' . $random_movie['id'] . '?append_to_response=credits');

// Get movie backdrops
$images = makeApiRequest('/movie/' . $random_movie['id'] . '/images?');
$backdrops = array_slice($images['backdrops'], 0, 5);

// Get movie recommendations
$recommendations = makeApiRequest('/movie/' . $random_movie['id'] . '/recommendations?');
$recommendations = array_slice($recommendations['results'], 0, 4);

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
    'director' => array_filter($movie_details['credits']['crew'], function($person) {
        return $person['job'] === 'Director';
    })[0]['name'] ?? 'Unknown',
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
            'poster' => $image_base_url . $rec['poster_path']
        ];
    }, $recommendations)
];

header('Content-Type: application/json');
echo json_encode($movie_data);
