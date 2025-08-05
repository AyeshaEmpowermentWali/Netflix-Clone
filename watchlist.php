<?php
require_once 'db.php';

if (!is_user_logged_in()) {
    redirect_to('login.php');
}

$current_user = get_logged_user();

// Get user's watchlist
$stmt = $pdo->prepare("
    SELECT c.*, w.added_at 
    FROM content c 
    JOIN watchlist w ON c.id = w.content_id 
    WHERE w.user_id = ? 
    ORDER BY w.added_at DESC
");
$stmt->execute([$current_user['id']]);
$watchlist_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My List - Netflix Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #141414;
            color: white;
            min-height: 100vh;
        }

        /* Header Styles */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: #141414;
            z-index: 1000;
            padding: 20px 4%;
            border-bottom: 1px solid #333;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #e50914;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #b3b3b3;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .profile-btn:hover {
            background-color: rgba(255,255,255,0.1);
        }

        /* Main Content */
        .main-content {
            padding: 120px 4% 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 2rem;
        }

        .watchlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 2rem;
        }

        .content-card {
            position: relative;
            cursor: pointer;
            transition: transform 0.3s;
            border-radius: 8px;
            overflow: hidden;
        }

        .content-card:hover {
            transform: scale(1.05);
        }

        .content-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .content-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.9));
            padding: 1.5rem;
        }

        .content-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .content-meta {
            font-size: 0.9rem;
            color: #b3b3b3;
            margin-bottom: 0.5rem;
        }

        .content-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
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

        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            border: none;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .content-card:hover .remove-btn {
            opacity: 1;
        }

        .remove-btn:hover {
            background: #e50914;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #b3b3b3;
        }

        .empty-state p {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
        }

        .btn-large {
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }

        .added-date {
            font-size: 0.8rem;
            color: #999;
            margin-top: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .main-content {
                padding: 100px 2% 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .watchlist-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
            }

            .content-card img {
                height: 225px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">NETFLIX</a>
            
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="search.php">Browse</a></li>
                <li><a href="watchlist.php">My List</a></li>
            </ul>

            <div class="user-menu">
                <span>Welcome, <?php echo htmlspecialchars($current_user['username']); ?></span>
                <button class="profile-btn" onclick="redirectTo('profile.php')">Profile</button>
                <button class="profile-btn" onclick="redirectTo('logout.php')">Logout</button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <h1 class="page-title">My List</h1>

        <?php if (!empty($watchlist_items)): ?>
            <div class="watchlist-grid">
                <?php foreach ($watchlist_items as $content): ?>
                    <div class="content-card" data-id="<?php echo $content['id']; ?>">
                        <button class="remove-btn" onclick="removeFromWatchlist(<?php echo $content['id']; ?>)" title="Remove from list">×</button>
                        
                        <img src="<?php echo $content['thumbnail_url']; ?>" alt="<?php echo htmlspecialchars($content['title']); ?>">
                        
                        <div class="content-overlay">
                            <div class="content-title"><?php echo htmlspecialchars($content['title']); ?></div>
                            <div class="content-meta">
                                <?php echo $content['release_year']; ?> • <?php echo ucfirst($content['type']); ?> • ⭐ <?php echo $content['rating']; ?>
                            </div>
                            <div class="added-date">
                                Added <?php echo date('M j, Y', strtotime($content['added_at'])); ?>
                            </div>
                            
                            <div class="content-actions">
                                <button class="btn btn-primary" onclick="redirectTo('watch.php?id=<?php echo $content['id']; ?>')">
                                    ▶ Play
                                </button>
                                <button class="btn btn-secondary" onclick="redirectTo('search.php?q=<?php echo urlencode($content['title']); ?>')">
                                    Info
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>Your list is empty</h2>
                <p>Add movies and TV shows to your list to watch them later</p>
                <button class="btn btn-primary btn-large" onclick="redirectTo('search.php')">
                    Browse Content
                </button>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function redirectTo(url) {
            window.location.href = url;
        }

        function removeFromWatchlist(contentId) {
            if (confirm('Remove this item from your list?')) {
                fetch('toggle_watchlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        content_id: contentId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && !data.in_watchlist) {
                        // Remove the card from the grid
                        const card = document.querySelector(`[data-id="${contentId}"]`);
                        card.style.transform = 'scale(0)';
                        card.style.opacity = '0';
                        
                        setTimeout(() => {
                            card.remove();
                            
                            // Check if list is now empty
                            const remainingCards = document.querySelectorAll('.content-card');
                            if (remainingCards.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to remove item from list');
                });
            }
        }

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.content-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
