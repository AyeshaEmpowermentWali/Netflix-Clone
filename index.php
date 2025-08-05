<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

// Initialize variables to prevent undefined variable errors
$featured_content = [];
$content_by_genre = [];

try {
    // Get featured content
    $featured_stmt = $pdo->prepare("SELECT * FROM content WHERE is_featured = 1 ORDER BY rating DESC LIMIT 5");
    $featured_stmt->execute();
    $featured_content = $featured_stmt->fetchAll();

    // Get content by genre
    $genres = ['Action', 'Comedy', 'Drama', 'Sci-Fi', 'Crime'];
    
    foreach ($genres as $genre) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM content WHERE genre = ? ORDER BY rating DESC LIMIT 8");
            $stmt->execute([$genre]);
            $content_by_genre[$genre] = $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error fetching genre $genre: " . $e->getMessage());
            $content_by_genre[$genre] = [];
        }
    }
} catch (Exception $e) {
    error_log("Error in index.php: " . $e->getMessage());
    $featured_content = [];
    $content_by_genre = [];
}

$current_user = get_logged_user(); // UPDATED function name
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix Clone</title>
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
            overflow-x: hidden;
        }

        /* Header Styles */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            background: linear-gradient(180deg, rgba(0,0,0,0.7) 10%, transparent);
            z-index: 1000;
            padding: 20px 4%;
            transition: background-color 0.4s;
        }

        .header.scrolled {
            background-color: #141414;
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

        .auth-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #e50914;
            color: white;
        }

        .btn-primary:hover {
            background-color: #f40612;
        }

        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 1px solid white;
        }

        .btn-secondary:hover {
            background-color: white;
            color: black;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            padding: 0 4%;
        }

        .hero-content {
            max-width: 500px;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
        }

        .hero-description {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            line-height: 1.5;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-large {
            padding: 12px 24px;
            font-size: 1.1rem;
            font-weight: bold;
        }

        /* Content Sections */
        .content-section {
            padding: 2rem 4%;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .content-row {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 1rem;
        }

        .content-row::-webkit-scrollbar {
            height: 8px;
        }

        .content-row::-webkit-scrollbar-track {
            background: #333;
        }

        .content-row::-webkit-scrollbar-thumb {
            background: #666;
            border-radius: 4px;
        }

        .content-card {
            min-width: 200px;
            cursor: pointer;
            transition: transform 0.3s;
            position: relative;
        }

        .content-card:hover {
            transform: scale(1.05);
        }

        .content-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
        }

        .content-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            padding: 1rem;
            border-radius: 0 0 8px 8px;
        }

        .content-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .content-meta {
            font-size: 0.9rem;
            color: #b3b3b3;
        }

        /* Featured Carousel */
        .featured-carousel {
            position: relative;
            margin-bottom: 2rem;
        }

        .carousel-container {
            display: flex;
            transition: transform 0.5s ease;
        }

        .carousel-slide {
            min-width: 100%;
            height: 60vh;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            padding: 0 4%;
            position: relative;
        }

        .carousel-slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4));
        }

        .carousel-content {
            position: relative;
            z-index: 1;
            max-width: 500px;
        }

        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            border: none;
            color: white;
            padding: 1rem;
            cursor: pointer;
            font-size: 1.5rem;
            transition: background-color 0.3s;
        }

        .carousel-nav:hover {
            background: rgba(0,0,0,0.8);
        }

        .carousel-prev {
            left: 20px;
        }

        .carousel-next {
            right: 20px;
        }

        .carousel-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.5rem;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .indicator.active {
            background: white;
        }

        /* Welcome Message */
        .welcome-section {
            padding: 120px 4% 2rem;
            text-align: center;
        }

        .welcome-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #e50914;
        }

        .welcome-description {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #b3b3b3;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero-title, .welcome-title {
                font-size: 2rem;
            }

            .hero-description, .welcome-description {
                font-size: 1rem;
            }

            .content-card {
                min-width: 150px;
            }

            .content-card img {
                height: 225px;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 0 2%;
            }

            .content-section {
                padding: 1rem 2%;
            }

            .hero-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <nav class="nav">
            <a href="index.php" class="logo">NETFLIX</a>
            
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="search.php">Browse</a></li>
                <?php if ($current_user): ?>
                    <li><a href="watchlist.php">My List</a></li>
                <?php endif; ?>
            </ul>

            <div class="user-menu">
                <?php if ($current_user): ?>
                    <span>Welcome, <?php echo htmlspecialchars($current_user['username']); ?></span>
                    <button class="profile-btn" onclick="redirectTo('profile.php')">Profile</button>
                    <button class="profile-btn" onclick="redirectTo('logout.php')">Logout</button>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="login.php" class="btn btn-secondary">Sign In</a>
                        <a href="signup.php" class="btn btn-primary">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Welcome Section (shown when no content) -->
    <?php if (empty($featured_content) && empty(array_filter($content_by_genre))): ?>
    <section class="welcome-section">
        <h1 class="welcome-title">Welcome to Netflix Clone</h1>
        <p class="welcome-description">Your streaming platform is ready! Please add some content to get started.</p>
        <div class="hero-buttons">
            <a href="login.php" class="btn btn-primary btn-large">Sign In</a>
            <a href="signup.php" class="btn btn-secondary btn-large">Sign Up</a>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Carousel -->
    <?php if (!empty($featured_content)): ?>
    <section class="featured-carousel">
        <div class="carousel-container" id="carousel">
            <?php foreach ($featured_content as $index => $content): ?>
            <div class="carousel-slide" style="background-image: url('<?php echo htmlspecialchars($content['banner_url'] ?? 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'); ?>')">
                <div class="carousel-content">
                    <h1 class="hero-title"><?php echo htmlspecialchars($content['title'] ?? 'Title'); ?></h1>
                    <p class="hero-description"><?php echo htmlspecialchars($content['description'] ?? 'Description'); ?></p>
                    <div class="hero-buttons">
                        <button class="btn btn-primary btn-large" onclick="redirectTo('watch.php?id=<?php echo intval($content['id']); ?>')">
                            ▶ Play
                        </button>
                        <button class="btn btn-secondary btn-large" onclick="redirectTo('search.php?q=<?php echo urlencode($content['title']); ?>')">
                            ℹ More Info
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <button class="carousel-nav carousel-prev" onclick="previousSlide()">❮</button>
        <button class="carousel-nav carousel-next" onclick="nextSlide()">❯</button>
        
        <div class="carousel-indicators">
            <?php foreach ($featured_content as $index => $content): ?>
            <div class="indicator <?php echo $index === 0 ? 'active' : ''; ?>" onclick="goToSlide(<?php echo $index; ?>)"></div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Content Sections -->
    <?php foreach ($content_by_genre as $genre => $content_list): ?>
        <?php if (!empty($content_list)): ?>
        <section class="content-section">
            <h2 class="section-title"><?php echo $genre; ?> Movies & Shows</h2>
            <div class="content-row">
                <?php foreach ($content_list as $content): ?>
                <div class="content-card" onclick="redirectTo('watch.php?id=<?php echo $content['id']; ?>')">
                    <img src="<?php echo $content['thumbnail_url']; ?>" alt="<?php echo htmlspecialchars($content['title']); ?>">
                    <div class="content-info">
                        <div class="content-title"><?php echo htmlspecialchars($content['title']); ?></div>
                        <div class="content-meta">
                            <?php echo $content['release_year']; ?> • <?php echo $content['type']; ?> • ⭐ <?php echo $content['rating']; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    <?php endforeach; ?>

    <script>
        // Utility function for redirects
        function redirectTo(url) {
            try {
                window.location.href = url;
            } catch (e) {
                console.error('Redirect error:', e);
            }
        }

        // Header scroll effect
        window.addEventListener('scroll', function() {
            try {
                const header = document.getElementById('header');
                if (header) {
                    if (window.scrollY > 100) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }
                }
            } catch (e) {
                console.error('Scroll error:', e);
            }
        });

        // Carousel functionality
        let currentSlide = 0;
        const totalSlides = <?php echo count($featured_content); ?>;

        function updateCarousel() {
            try {
                const carousel = document.getElementById('carousel');
                const indicators = document.querySelectorAll('.indicator');
                
                if (carousel) {
                    carousel.style.transform = `translateX(-${currentSlide * 100}%)`;
                }
                
                indicators.forEach((indicator, index) => {
                    indicator.classList.toggle('active', index === currentSlide);
                });
            } catch (e) {
                console.error('Carousel error:', e);
            }
        }

        function nextSlide() {
            if (totalSlides > 0) {
                currentSlide = (currentSlide + 1) % totalSlides;
                updateCarousel();
            }
        }

        function previousSlide() {
            if (totalSlides > 0) {
                currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
                updateCarousel();
            }
        }

        function goToSlide(index) {
            if (index >= 0 && index < totalSlides) {
                currentSlide = index;
                updateCarousel();
            }
        }

        // Auto-play carousel only if there are slides
        if (totalSlides > 1) {
            setInterval(nextSlide, 5000);
        }
    </script>
</body>
</html>
