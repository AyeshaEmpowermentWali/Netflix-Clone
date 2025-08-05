<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    // Update existing content with working video URLs
    $updates = [
        [
            'id' => 1,
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'title' => 'Big Buck Bunny'
        ],
        [
            'id' => 2,
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
            'title' => 'Elephants Dream'
        ],
        [
            'id' => 3,
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
            'title' => 'Sintel'
        ],
        [
            'id' => 4,
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
            'title' => 'Tears of Steel'
        ],
        [
            'id' => 5,
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
            'title' => 'For Bigger Blazes'
        ]
    ];

    foreach ($updates as $update) {
        $stmt = $pdo->prepare("UPDATE content SET video_url = ?, title = ? WHERE id = ?");
        $stmt->execute([$update['video_url'], $update['title'], $update['id']]);
    }

    // Add new content if it doesn't exist
    $new_content = [
        [
            'title' => 'Subspace',
            'description' => 'A young woman discovers a strange portal in her uncle\'s garden.',
            'genre' => 'Sci-Fi',
            'year' => 2010,
            'duration' => 8,
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubspaceRhapsody.mp4'
        ],
        [
            'title' => 'We Are Going On Bullrun',
            'description' => 'A documentary about the famous Bullrun rally.',
            'genre' => 'Documentary',
            'year' => 2011,
            'duration' => 5,
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4'
        ]
    ];

    foreach ($new_content as $content) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO content (title, description, genre, release_year, duration, type, video_url, thumbnail_url, banner_url, rating, is_featured) 
            VALUES (?, ?, ?, ?, ?, 'movie', ?, 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=400&fit=crop', 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=400&fit=crop', 7.5, FALSE)
        ");
        $stmt->execute([
            $content['title'],
            $content['description'],
            $content['genre'],
            $content['year'],
            $content['duration'],
            $content['video_url']
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Videos updated successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
