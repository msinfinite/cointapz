<?php
$api_key = 'bd0b7e80d74ee83319effffe2a715e4f';
$base_url = 'https://api.themoviedb.org/3';
$image_base_url = 'image_proxy.php?url=' . urlencode('https://media.themoviedb.org/t/p/original');

$id = $_GET['id'] ?? '';

if (!$id) {
    die('No ID provided');
}

$url = $base_url . "/movie/$id?api_key=$api_key&append_to_response=credits,images";
$response = file_get_contents($url);
$data = json_decode($response, true);

// Process the data and display it
// This is where you'd generate the HTML for the movie details page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title']); ?></title>
    <!-- Add your CSS here -->
</head>
<body>
    <h1><?php echo htmlspecialchars($data['title']); ?></h1>
    <img src="<?php echo $image_base_url . $data['poster_path']; ?>" alt="<?php echo htmlspecialchars($data['title']); ?> Poster">
    <p>Release Date: <?php echo $data['release_date']; ?></p>
    <p>Rating: <?php echo $data['vote_average']; ?>/10</p>
    <p><?php echo htmlspecialchars($data['overview']); ?></p>
    <!-- Add more details as needed -->
</body>
</html>
