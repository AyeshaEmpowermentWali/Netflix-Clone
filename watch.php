<?php
require_once 'db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect_to('index.php');
}

$content_id = (int)$_GET['id'];
$current_user = get_logged_user();

// Get content details
$stmt = $pdo->prepare("SELECT * FROM content WHERE id = ?");
$stmt->execute([$content_id]);
$content = $stmt->fetch();

if (!$content) {
    redirect_to('index.php');
}

// Get watch progress if user is logged in
$progress = 0;
$progress_data = null;
if ($current_user) {
    $stmt = $pdo->prepare("SELECT progress_time, total_time FROM watch_progress WHERE user_id = ? AND content_id = ?");
    $stmt->execute([$current_user['id'], $content_id]);
    $progress_data = $stmt->fetch();
    if ($progress_data && is_array($progress_data) && $progress_data['total_time'] > 0) {
        $progress = ($progress_data['progress_time'] / $progress_data['total_time']) * 100;
    }
}

// Check if in watchlist
$in_watchlist = false;
if ($current_user) {
    $stmt = $pdo->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
    $stmt->execute([$current_user['id'], $content_id]);
    $in_watchlist = $stmt->fetch() !== false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($content['title']); ?> - Netflix Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #000;
            color: white;
            overflow-x: hidden;
        }

        .video-container {
            position: relative;
            width: 100%;
            height: 100vh;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .video-player {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.7);
            border: none;
            color: white;
            padding: 1rem;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            z-index: 1000;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background: rgba(0,0,0,0.9);
        }

        .content-info {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            padding: 1.5rem;
            border-radius: 8px;
            max-width: 300px;
            z-index: 1000;
        }

        .content-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .content-meta {
            color: #b3b3b3;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .content-description {
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #e50914;
            color: white;
        }

        .btn-primary:hover {
            background: #f40612;
        }

        .btn-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.3);
        }

        .loading-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            background: rgba(0,0,0,0.8);
            padding: 2rem;
            border-radius: 8px;
            z-index: 999;
        }

        .error-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            background: rgba(229, 9, 20, 0.9);
            padding: 2rem;
            border-radius: 8px;
            z-index: 999;
        }

        .video-url-info {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            max-width: 400px;
            z-index: 1000;
        }

        @media (max-width: 768px) {
            .content-info {
                position: static;
                margin: 1rem;
                max-width: none;
            }

            .video-container {
                height: 60vh;
            }

            .video-url-info {
                position: static;
                margin: 1rem;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="video-container">
        <button class="back-btn" onclick="redirectTo('index.php')" title="Back to Home">←</button>
        
        <div class="loading-message" id="loadingMessage">
            <h2>Loading Video...</h2>
            <p>Please wait while the video loads</p>
        </div>

        <div class="error-message" id="errorMessage" style="display: none;">
            <h2>Video Error</h2>
            <p id="errorText">Unable to load video</p>
            <button class="btn btn-primary" onclick="retryVideo()" style="margin: 10px;">Retry</button>
            <button class="btn btn-secondary" onclick="redirectTo('index.php')">Go Back</button>
        </div>
        
        <video class="video-player" id="videoPlayer" controls preload="metadata" style="display: none;">
            <source src="<?php echo htmlspecialchars($content['video_url']); ?>" type="video/mp4">
            <p>Your browser does not support the video tag.</p>
        </video>

        <div class="content-info">
            <h1 class="content-title"><?php echo htmlspecialchars($content['title']); ?></h1>
            <div class="content-meta">
                <?php echo $content['release_year']; ?> • <?php echo ucfirst($content['type']); ?> • <?php echo $content['duration']; ?> min • ⭐ <?php echo $content['rating']; ?>
            </div>
            <p class="content-description"><?php echo htmlspecialchars($content['description']); ?></p>
            
            <?php if ($current_user): ?>
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="toggleWatchlist()" id="watchlistBtn">
                    <?php echo $in_watchlist ? '✓ In List' : '+ My List'; ?>
                </button>
                <button class="btn btn-secondary" onclick="rateContent()">Rate</button>
            </div>
            <?php endif; ?>
        </div>

        <div class="video-url-info">
            <strong>Video URL:</strong><br>
            <a href="<?php echo htmlspecialchars($content['video_url']); ?>" target="_blank" style="color: #e50914; word-break: break-all;">
                <?php echo htmlspecialchars($content['video_url']); ?>
            </a>
            <br><br>
            <button class="btn btn-secondary" onclick="testVideoUrl()" style="font-size: 0.8rem;">Test Video URL</button>
            <button class="btn btn-secondary" onclick="redirectTo('test_video.php')" style="font-size: 0.8rem;">Video Test Page</button>
        </div>
    </div>

    <script>
        function redirectTo(url) {
            window.location.href = url;
        }

        const video = document.getElementById('videoPlayer');
        const loadingMessage = document.getElementById('loadingMessage');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');

        let currentTime = 0;
        let duration = 0;

        // Video event listeners
        video.addEventListener('loadstart', function() {
            console.log('Video loading started');
            loadingMessage.style.display = 'block';
            errorMessage.style.display = 'none';
        });

        video.addEventListener('loadedmetadata', function() {
            console.log('Video metadata loaded');
            duration = video.duration;
            console.log('Video duration:', duration);
        });

        video.addEventListener('canplay', function() {
            console.log('Video can start playing');
            loadingMessage.style.display = 'none';
            video.style.display = 'block';
        });

        video.addEventListener('timeupdate', function() {
            currentTime = video.currentTime;
            saveProgress();
        });

        video.addEventListener('ended', function() {
            console.log('Video ended');
            saveProgress();
        });

        video.addEventListener('error', function() {
            console.error('Video error:', video.error);
            loadingMessage.style.display = 'none';
            errorMessage.style.display = 'block';
            
            let errorMsg = 'Unknown error occurred';
            if (video.error) {
                switch(video.error.code) {
                    case 1:
                        errorMsg = 'Video loading aborted';
                        break;
                    case 2:
                        errorMsg = 'Network error occurred';
                        break;
                    case 3:
                        errorMsg = 'Video format not supported';
                        break;
                    case 4:
                        errorMsg = 'Video source not found';
                        break;
                }
            }
            errorText.textContent = errorMsg;
        });

        function retryVideo() {
            errorMessage.style.display = 'none';
            loadingMessage.style.display = 'block';
            video.load();
        }

        function testVideoUrl() {
            const videoUrl = '<?php echo htmlspecialchars($content['video_url']); ?>';
            window.open(videoUrl, '_blank');
        }

        // Save watch progress
        function saveProgress() {
            <?php if ($current_user): ?>
            if (currentTime > 0 && duration > 0) {
                fetch('save_progress.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        content_id: <?php echo $content_id; ?>,
                        progress_time: Math.floor(currentTime),
                        total_time: Math.floor(duration)
                    })
                }).catch(error => console.error('Error saving progress:', error));
            }
            <?php endif; ?>
        }

        // Watchlist functionality
        function toggleWatchlist() {
            <?php if ($current_user): ?>
            fetch('toggle_watchlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    content_id: <?php echo $content_id; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                const btn = document.getElementById('watchlistBtn');
                if (data.success) {
                    btn.textContent = data.in_watchlist ? '✓ In List' : '+ My List';
                }
            })
            .catch(error => console.error('Error:', error));
            <?php else: ?>
            redirectTo('login.php');
            <?php endif; ?>
        }

        function rateContent() {
            const rating = prompt('Rate this content (1-5 stars):');
            if (rating && rating >= 1 && rating <= 5) {
                fetch('rate_content.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        content_id: <?php echo $content_id; ?>,
                        rating: parseInt(rating)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Thank you for rating!');
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        // Resume playback from saved progress
        <?php if ($current_user && $progress_data && is_array($progress_data) && $progress_data['progress_time'] > 5): ?>
        video.addEventListener('loadedmetadata', function() {
            if (confirm('Resume from where you left off?')) {
                video.currentTime = <?php echo (int)$progress_data['progress_time']; ?>;
            }
        });
        <?php endif; ?>

        // Initialize video loading
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, starting video load');
            video.load();
        });
    </script>
</body>
</html>
