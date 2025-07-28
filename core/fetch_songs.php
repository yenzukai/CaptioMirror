<?php
$musicFolder = '../assets/music/'; // Path to the music folder
$songs = [];

// Open the folder and scan for .mp3 files
if (is_dir($musicFolder)) {
    $files = scandir($musicFolder);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'mp3') {
            $songs[] = [
                'name' => pathinfo($file, PATHINFO_FILENAME), // File name without extension
                'path' => $musicFolder . $file // Full path to the file
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($songs);
