<?php
$api_key = 'bd0b7e80d74ee83319effffe2a715e4f';
$base_url = 'https://api.themoviedb.org/3';
$image_base_url = 'image_proxy.php?url=' . urlencode('https://media.themoviedb.org/t/p/original');

$genre = $_GET['genre'] ?? '';
$min_year = $_GET['min_year'] ?? '1980';
$max_year = $_GET['max_year'] ?? '2024';
$min_rating = $_GET['min_rating'] ?? '1';
$show_type = $_GET['show_type'] ?? 'all';
$is_default_search = $_GET['default'] ?? 'false';

$is_default_search = ($is_default_search === 'true');

function makeApiRequest($url) {
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function getRandomPage() {
    return rand(1, 20); // TMDB typically has 20 pages of results
}

$results = [];

if ($is_default_search) {
    // Perform multiple requests for both movies and TV shows with random pages
    for ($i = 0; $i < 5; $i++) { // Make 5 requests each for movies and TV shows
        $movie_url = $base_url . "/discover/movie?api_key=$api_key&sort_by=popularity.desc&primary_release_date.gte=1980-01-01&primary_release_date.lte=2024-12-31&page=" . getRandomPage();
        $tv_url = $base_url . "/discover/tv?api_key=$api_key&sort_by=popularity.desc&first_air_date.gte=1980-01-01&first_air_date.lte=2024-12-31&page=" . getRandomPage();
        
        $movie_data = makeApiRequest($movie_url);
        $tv_data = makeApiRequest($tv_url);
        
        $results = array_merge($results, $movie_data['results'], $tv_data['results']);
    }
} else {
    // Perform a filtered search
    $search_url = $base_url . '/discover/' . ($show_type == 'tv' ? 'tv' : 'movie') . "?api_key=$api_key";
    $search_url .= "&sort_by=popularity.desc";
    
    if ($show_type == 'tv') {
        $search_url .= "&first_air_date.gte=$min_year-01-01&first_air_date.lte=$max_year-12-31";
    } else {
        $search_url .= "&primary_release_date.gte=$min_year-01-01&primary_release_date.lte=$max_year-12-31";
    }
    
    if ($genre) {
        $search_url .= "&with_genres=$genre";
    }
    
    $search_url .= "&vote_average.gte=" . ($min_rating * 2);
    
    // Make multiple requests with random pages for filtered search as well
    for ($i = 0; $i < 5; $i++) {
        $page_url = $search_url . "&page=" . getRandomPage();
        $data = makeApiRequest($page_url);
        $results = array_merge($results, $data['results']);
    }
}

// Process results
$processed_results = array_map(function($item) use ($image_base_url) {
    return [
        'id' => $item['id'],
        'title' => $item['title'] ?? $item['name'],
        'poster' => $image_base_url . ($item['poster_path'] ?? ''),
        'release_date' => $item['release_date'] ?? $item['first_air_date'] ?? '',
        'vote_average' => $item['vote_average']
    ];
}, $results);

// Filter out items without posters
$processed_results = array_filter($processed_results, function($item) {
    return !empty($item['poster']);
});

// Shuffle and limit results
shuffle($processed_results);
$final_results = array_slice($processed_results, 0, 4);

header('Content-Type: application/json');
echo json_encode($final_results);
