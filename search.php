<?php
require_once 'db.php';

$search_query = isset($_GET['q']) ? sanitize_input($_GET['q']) : '';
$genre_filter = isset($_GET['genre']) ? sanitize_input($_GET['genre']) : '';
$type_filter = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';

$current_user = get_logged_user();

// Build search query
$sql = "SELECT * FROM content WHERE 1=1";
$params = [];

if (!empty($search_query)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

if (!empty($genre_filter)) {
    $sql .= " AND genre = ?";
    $params[] = $genre_filter;
}

if (!empty($type_filter)) {
    $sql .= " AND type = ?";
    $params[] = $type_filter;
}

$sql .= " ORDER BY rating DESC, title ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$search_results = $stmt->fetchAll();

// Get all genres for filter
$genre_stmt = $pdo->prepare("SELECT DISTINCT genre FROM content ORDER BY genre");
$genre_stmt->execute();
$genres = $genre_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse - Netflix Clone</title>
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

        /* Search Section */
        .search-section {
            padding: 120px 4% 2rem;
        }

        .search-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .search-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 2rem;
            text-align: center;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 300px;
            padding: 1rem;
            border: none;
            border-radius: 4px;
            background: #333;
            color: white;
            font-size: 1rem;
        }

        .search-input::placeholder {
            color: #999;
        }

        .search-input:focus {
            outline: none;
            background: #555;
        }

        .search-btn {
            padding: 1rem 2rem;
            background: #e50914;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .search-btn:hover {
            background: #f40612;
        }

        /* Filters */
        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-select {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            background: #333;
            color: white;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            background: #555;
        }

        /* Results */
        .results-section {
            padding: 0 4% 2rem;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .results-count {
            font-size: 1.2rem;
            color: #b3b3b3;
        }

        .sort-select {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            background: #333;
            color: white;
            cursor: pointer;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 2rem;
        }

        .content-card {
            cursor: pointer;
            transition: transform 0.3s;
            position: relative;
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

        .content-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .rating-stars {
            color: #ffd700;
        }

        .no-results {
            text-align: center;
            padding: 4rem 2rem;
        }

        .no-results h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #b3b3b3;
        }

        .no-results p {
            font-size: 1.1rem;
            color: #666;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .search-form {
                flex-direction: column;
            }

            .search-input {
                min-width: auto;
            }

            .filters {
                justify-content: flex-start;
            }

            .results-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .results-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
            }

            .content-card img {
                height: 225px;
            }
        }

        @media (max-width: 480px) {
            .search-section {
                padding: 100px 2% 1rem;
            }

            .results-section {
                padding: 0 2% 1rem;
            }

            .search-title {
                font-size: 2rem;
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

    <!-- Search Section -->
    <section class="search-section">
        <div class="search-container">
            <h1 class="search-title">Browse Movies & TV Shows</h1>
            
            <form class="search-form" method="GET" action="">
                <input type="text" class="search-input" name="q" placeholder="Search for movies, TV shows..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-btn">Search</button>
            </form>

            <div class="filters">
                <select class="filter-select" name="genre" onchange="applyFilter('genre', this.value)">
                    <option value="">All Genres</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?php echo $genre; ?>" <?php echo $genre_filter === $genre ? 'selected' : ''; ?>>
                            <?php echo $genre; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select class="filter-select" name="type" onchange="applyFilter('type', this.value)">
                    <option value="">All Types</option>
                    <option value="movie" <?php echo $type_filter === 'movie' ? 'selected' : ''; ?>>Movies</option>
                    <option value="series" <?php echo $type_filter === 'series' ? 'selected' : ''; ?>>TV Shows</option>
                </select>

                <?php if (!empty($search_query) || !empty($genre_filter) || !empty($type_filter)): ?>
                    <button class="btn btn-secondary" onclick="clearFilters()">Clear Filters</button>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    <section class="results-section">
        <div class="results-header">
            <div class="results-count">
                <?php echo count($search_results); ?> results found
                <?php if (!empty($search_query)): ?>
                    for "<?php echo htmlspecialchars($search_query); ?>"
                <?php endif; ?>
            </div>
            
            <select class="sort-select" onchange="sortResults(this.value)">
                <option value="rating">Sort by Rating</option>
                <option value="title">Sort by Title</option>
                <option value="year">Sort by Year</option>
                <option value="type">Sort by Type</option>
            </select>
        </div>

        <?php if (!empty($search_results)): ?>
            <div class="results-grid" id="resultsGrid">
                <?php foreach ($search_results as $content): ?>
                    <div class="content-card" onclick="redirectTo('watch.php?id=<?php echo $content['id']; ?>')" 
                         data-rating="<?php echo $content['rating']; ?>" 
                         data-title="<?php echo htmlspecialchars($content['title']); ?>"
                         data-year="<?php echo $content['release_year']; ?>"
                         data-type="<?php echo $content['type']; ?>">
                        <img src="<?php echo $content['thumbnail_url']; ?>" alt="<?php echo htmlspecialchars($content['title']); ?>">
                        <div class="content-overlay">
                            <div class="content-title"><?php echo htmlspecialchars($content['title']); ?></div>
                            <div class="content-meta">
                                <?php echo $content['release_year']; ?> • <?php echo ucfirst($content['type']); ?> • <?php echo $content['duration']; ?> min
                            </div>
                            <div class="content-rating">
                                <span class="rating-stars">⭐</span>
                                <span><?php echo $content['rating']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <h2>No results found</h2>
                <p>Try adjusting your search terms or filters</p>
            </div>
        <?php endif; ?>
    </section>

    <script>
        function redirectTo(url) {
            window.location.href = url;
        }

        function applyFilter(filterType, value) {
            const url = new URL(window.location);
            if (value) {
                url.searchParams.set(filterType, value);
            } else {
                url.searchParams.delete(filterType);
            }
            window.location.href = url.toString();
        }

        function clearFilters() {
            const url = new URL(window.location);
            url.searchParams.delete('genre');
            url.searchParams.delete('type');
            url.searchParams.delete('q');
            window.location.href = url.toString();
        }

        function sortResults(sortBy) {
            const grid = document.getElementById('resultsGrid');
            const cards = Array.from(grid.children);
            
            cards.sort((a, b) => {
                let aValue, bValue;
                
                switch(sortBy) {
                    case 'rating':
                        aValue = parseFloat(a.dataset.rating);
                        bValue = parseFloat(b.dataset.rating);
                        return bValue - aValue; // Descending
                    case 'title':
                        aValue = a.dataset.title.toLowerCase();
                        bValue = b.dataset.title.toLowerCase();
                        return aValue.localeCompare(bValue);
                    case 'year':
                        aValue = parseInt(a.dataset.year);
                        bValue = parseInt(b.dataset.year);
                        return bValue - aValue; // Descending
                    case 'type':
                        aValue = a.dataset.type;
                        bValue = b.dataset.type;
                        return aValue.localeCompare(bValue);
                    default:
                        return 0;
                }
            });
            
            // Clear and re-append sorted cards
            grid.innerHTML = '';
            cards.forEach(card => grid.appendChild(card));
        }

        // Search form enhancement
        document.querySelector('.search-form').addEventListener('submit', function(e) {
            const searchInput = document.querySelector('.search-input');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                searchInput.focus();
            }
        });

        // Auto-search on input (debounced)
        let searchTimeout;
        document.querySelector('.search-input').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    const url = new URL(window.location);
                    if (this.value) {
                        url.searchParams.set('q', this.value);
                    } else {
                        url.searchParams.delete('q');
                    }
                    window.location.href = url.toString();
                }
            }, 500);
        });
    </script>
</body>
</html>
