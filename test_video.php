<?php
require_once 'db.php';

// Get a sample video for testing
$stmt = $pdo->prepare("SELECT * FROM content LIMIT 1");
$stmt->execute();
$content = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Test - Netflix Clone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #141414;
            color: white;
            padding: 2rem;
        }
        
        .test-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .video-test {
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        
        video {
            width: 100%;
            max-width: 600px;
            height: auto;
        }
        
        .test-info {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(0,0,0,0.5);
            border-radius: 4px;
        }
        
        .btn {
            background: #e50914;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        
        .btn:hover {
            background: #f40612;
        }
        
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .success { background: #4CAF50; }
        .error { background: #f44336; }
        .warning { background: #ff9800; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🎬 Video Test Page</h1>
        
        <div class="video-test">
            <h2>Database Video Test</h2>
            <?php if ($content): ?>
                <p><strong>Title:</strong> <?php echo htmlspecialchars($content['title']); ?></p>
                <p><strong>Video URL:</strong> <a href="<?php echo $content['video_url']; ?>" target="_blank"><?php echo $content['video_url']; ?></a></p>
                
                <video controls preload="metadata" id="dbVideo">
                    <source src="<?php echo $content['video_url']; ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                
                <div class="test-info">
                    <p><strong>Status:</strong> <span id="dbVideoStatus">Loading...</span></p>
                    <button class="btn" onclick="testVideoLoad('dbVideo')">Test Load</button>
                    <button class="btn" onclick="window.open('<?php echo $content['video_url']; ?>', '_blank')">Open Direct Link</button>
                </div>
            <?php else: ?>
                <div class="status error">No content found in database!</div>
            <?php endif; ?>
        </div>

        <div class="video-test">
            <h2>Sample Video Tests</h2>
            
            <h3>Test Video 1: Big Buck Bunny</h3>
            <video controls preload="metadata" id="testVideo1">
                <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="test-info">
                <p><strong>Status:</strong> <span id="testVideo1Status">Loading...</span></p>
                <button class="btn" onclick="testVideoLoad('testVideo1')">Test Load</button>
            </div>

            <h3>Test Video 2: Elephants Dream</h3>
            <video controls preload="metadata" id="testVideo2">
                <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="test-info">
                <p><strong>Status:</strong> <span id="testVideo2Status">Loading...</span></p>
                <button class="btn" onclick="testVideoLoad('testVideo2')">Test Load</button>
            </div>

            <h3>Test Video 3: Sintel</h3>
            <video controls preload="metadata" id="testVideo3">
                <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="test-info">
                <p><strong>Status:</strong> <span id="testVideo3Status">Loading...</span></p>
                <button class="btn" onclick="testVideoLoad('testVideo3')">Test Load</button>
            </div>
        </div>

        <div class="video-test">
            <h2>Actions</h2>
            <button class="btn" onclick="updateDatabase()">Update Database with Working Videos</button>
            <button class="btn" onclick="window.location.href='index.php'">Go to Homepage</button>
            <button class="btn" onclick="window.location.href='test_db.php'">Test Database</button>
        </div>

        <div id="updateResult"></div>
    </div>

    <script>
        function testVideoLoad(videoId) {
            const video = document.getElementById(videoId);
            const statusSpan = document.getElementById(videoId + 'Status');
            
            statusSpan.textContent = 'Testing...';
            statusSpan.className = 'status warning';
            
            video.addEventListener('loadedmetadata', function() {
                statusSpan.textContent = '✅ Video loaded successfully! Duration: ' + Math.round(video.duration) + 's';
                statusSpan.className = 'status success';
            });
            
            video.addEventListener('error', function() {
                statusSpan.textContent = '❌ Failed to load video: ' + (video.error ? video.error.message : 'Unknown error');
                statusSpan.className = 'status error';
            });
            
            video.load();
        }

        function updateDatabase() {
            const resultDiv = document.getElementById('updateResult');
            resultDiv.innerHTML = '<div class="status warning">Updating database...</div>';
            
            fetch('update_videos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = '<div class="status success">✅ Database updated successfully!</div>';
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    resultDiv.innerHTML = '<div class="status error">❌ Failed to update database: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="status error">❌ Error: ' + error.message + '</div>';
            });
        }

        // Auto-test all videos on page load
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($content): ?>
            testVideoLoad('dbVideo');
            <?php endif; ?>
            testVideoLoad('testVideo1');
            testVideoLoad('testVideo2');
            testVideoLoad('testVideo3');
        });
    </script>
</body>
</html>
