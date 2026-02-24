<?php
require_once __DIR__ . '/blog/blog-config.php';
$conn = getDBConnection();

// Get page number
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search = isset($_GET['s']) ? trim($_GET['s']) : '';
$limit = BLOG_POSTS_PER_PAGE;
$offset = ($page - 1) * $limit;

$categoryData = null;
$posts = [];
$total = 0;
$categories = [];
$recentPosts = [];
$pageTitle = 'Blog - Amity Online University';
$pageDesc = 'Read the latest articles, guides and insights on online education, MBA, BBA, BCA, MCA programs, career guidance, and more from Amity Online University.';

if ($conn) {
    try {
    if (!empty($search)) {
        // Search
        $pageTitle = "Search: $search - Blog - Amity Online University";
        $searchTerm = '%' . $search . '%';
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM blog_posts WHERE status='published' AND publish_date<=NOW() AND (title LIKE ? OR content LIKE ? OR focus_keyword LIKE ?)");
        $countStmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $total = $countStmt->fetchColumn();
        
        $stmt = $conn->prepare("SELECT p.*, a.name AS author_name, a.image AS author_image FROM blog_posts p LEFT JOIN blog_authors a ON p.author_id=a.id WHERE p.status='published' AND p.publish_date<=NOW() AND (p.title LIKE ? OR p.content LIKE ? OR p.focus_keyword LIKE ?) ORDER BY p.publish_date DESC, p.id DESC LIMIT ? OFFSET ?")
        $stmt->bindValue(1, $searchTerm);
        $stmt->bindValue(2, $searchTerm);
        $stmt->bindValue(3, $searchTerm);
        $stmt->bindValue(4, $limit, PDO::PARAM_INT);
        $stmt->bindValue(5, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();
    } elseif (!empty($category)) {
        // Category filter
        $catStmt = $conn->prepare("SELECT * FROM blog_categories WHERE slug=?");
        $catStmt->execute([$category]);
        $categoryData = $catStmt->fetch();
        
        if ($categoryData) {
            $pageTitle = $categoryData['name'] . ' - Blog - Amity Online University';
            $pageDesc = $categoryData['description'] ?: "Read articles about {$categoryData['name']} from Amity Online University blog.";
            
            $countStmt = $conn->prepare("SELECT COUNT(DISTINCT p.id) FROM blog_posts p JOIN blog_post_categories pc ON p.id=pc.post_id WHERE pc.category_id=? AND p.status='published' AND p.publish_date<=NOW()");
            $countStmt->execute([$categoryData['id']]);
            $total = $countStmt->fetchColumn();
            
            $stmt = $conn->prepare("SELECT p.*, a.name AS author_name, a.image AS author_image FROM blog_posts p LEFT JOIN blog_authors a ON p.author_id=a.id JOIN blog_post_categories pc ON p.id=pc.post_id WHERE pc.category_id=? AND p.status='published' AND p.publish_date<=NOW() ORDER BY p.publish_date DESC, p.id DESC LIMIT ? OFFSET ?");
            $stmt->bindValue(1, $categoryData['id'], PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $posts = $stmt->fetchAll();
        }
    } else {
        // All posts (only those with at least one category assigned)
        $countStmt = $conn->query("SELECT COUNT(*) FROM blog_posts WHERE status='published' AND publish_date<=NOW() AND id IN (SELECT post_id FROM blog_post_categories)");
        $total = $countStmt->fetchColumn();
        
        $stmt = $conn->prepare("SELECT p.*, a.name AS author_name, a.image AS author_image FROM blog_posts p LEFT JOIN blog_authors a ON p.author_id=a.id WHERE p.status='published' AND p.publish_date<=NOW() AND p.id IN (SELECT post_id FROM blog_post_categories) ORDER BY p.publish_date DESC, p.id DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();
    }
    
    // Get all categories with post counts
    $categories = $conn->query("SELECT c.*, COUNT(pc.post_id) as post_count FROM blog_categories c LEFT JOIN blog_post_categories pc ON c.id=pc.category_id LEFT JOIN blog_posts p ON pc.post_id=p.id AND p.status='published' GROUP BY c.id ORDER BY c.name")->fetchAll();
    
    // Recent posts for sidebar
    $recentPosts = $conn->query("SELECT id, title, slug, feature_image, feature_image_alt, publish_date FROM blog_posts WHERE status='published' AND publish_date<=NOW() ORDER BY publish_date DESC LIMIT 5")->fetchAll();
    
    // Get categories for each post
    foreach ($posts as &$post) {
        $catStmt = $conn->prepare("SELECT c.name, c.slug FROM blog_categories c JOIN blog_post_categories pc ON c.id=pc.category_id WHERE pc.post_id=?");
        $catStmt->execute([$post['id']]);
        $post['categories'] = $catStmt->fetchAll();
    }
    } catch (PDOException $e) {
        error_log("Blog query error: " . $e->getMessage());
        $posts = [];
        $total = 0;
        $categories = [];
        $recentPosts = [];
    }
}

$totalPages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-H2NDY0BJSH"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-H2NDY0BJSH');
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDesc); ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
    <meta name="author" content="Amity Online University">
    
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDesc); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://onlineamityuniversity.com/blog">
    <meta property="og:image" content="https://onlineamityuniversity.com/assets/images/amity-og-image.jpg">
    
    <link rel="canonical" href="https://onlineamityuniversity.com/blog<?php echo $category ? '?category='.$category : ''; ?><?php echo $page > 1 ? ($category ? '&' : '?').'page='.$page : ''; ?>">
    
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css?v=20260221">
    <link rel="stylesheet" href="css/blog.css?v=20260222">
    
    <!-- Blog Schema -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Blog",
      "name": "Amity Online University Blog",
      "description": "<?php echo htmlspecialchars($pageDesc); ?>",
      "url": "https://onlineamityuniversity.com/blog",
      "publisher": {
        "@type": "Organization",
        "name": "Amity Online University",
        "logo": "https://onlineamityuniversity.com/assets/images/amity-logo.svg"
      }
    }
    </script>
    
    <!-- BreadcrumbList Schema -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://onlineamityuniversity.com"},
        {"@type": "ListItem", "position": 2, "name": "Blog", "item": "https://onlineamityuniversity.com/blog"}
        <?php if ($categoryData): ?>
        ,{"@type": "ListItem", "position": 3, "name": "<?php echo htmlspecialchars($categoryData['name']); ?>", "item": "https://onlineamityuniversity.com/blog?category=<?php echo $categoryData['slug']; ?>"}
        <?php endif; ?>
      ]
    }
    </script>
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <nav class="nav container">
            <a href="/" class="logo" style="display: flex; align-items: center; gap: 0.5rem;">
                <img src="assets/images/amity-logo.svg" alt="Amity Online" style="height: 50px; width: auto;">
            </a>

            <!-- Mobile Enquire Button (visible only on mobile) -->
            <a href="/" class="mobile-enquire-btn" style="display: none; background: white; color: var(--navy-blue); border: 2px solid var(--navy-blue); padding: 8px 20px; border-radius: 25px; font-weight: 600; font-size: 0.9rem;">Enquire Now</a>

            <ul class="nav-menu" id="navMenu">
                <li><a href="/programs" class="nav-link" style="display: flex; align-items: center; gap: 0.3rem;">Programs <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i></a></li>
                <li><a href="/scholarship" class="nav-link">Scholarships</a></li>
                <li><a href="/#career-services" class="nav-link">Career Services</a></li>
                <li><a href="/blog" class="nav-link active">Blog</a></li>
                <div class="nav-cta">
                    <a href="/" class="btn" style="background: white; color: var(--navy-blue); border: 2px solid var(--navy-blue);">Enquire Now</a>
                    <a href="/" class="btn btn-primary">Apply Now</a>
                </div>
            </ul>
            <button class="mobile-toggle" id="mobileToggle">
                <span></span><span></span><span></span>
            </button>
        </nav>
    </header>

    <!-- Blog Hero / Breadcrumb -->
    <section class="blog-hero">
        <div class="container">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="/">Home</a>
                <span>/</span>
                <a href="blog">Blog</a>
                <?php if ($categoryData): ?>
                <span>/</span>
                <span><?php echo htmlspecialchars($categoryData['name']); ?></span>
                <?php endif; ?>
                <?php if ($search): ?>
                <span>/</span>
                <span>Search: <?php echo htmlspecialchars($search); ?></span>
                <?php endif; ?>
            </nav>
            <h1 class="blog-hero-title">
                <?php 
                if ($categoryData) echo htmlspecialchars($categoryData['name']);
                elseif ($search) echo 'Search: ' . htmlspecialchars($search);
                else echo 'Amity Online Blog';
                ?>
            </h1>
            <p class="blog-hero-desc">
                <?php 
                if ($categoryData && $categoryData['description']) echo htmlspecialchars($categoryData['description']);
                elseif ($search) echo 'Search results for "' . htmlspecialchars($search) . '"';
                else echo 'Insights, guides & articles on online education, career growth, and academic excellence';
                ?>
            </p>
        </div>
    </section>

    <!-- Blog Content -->
    <section class="blog-listing-section">
        <div class="container">
            <div class="blog-layout">
                <!-- Main Content -->
                <main class="blog-main">
                    <?php if (empty($posts)): ?>
                    <div class="blog-empty">
                        <i class="fas fa-newspaper"></i>
                        <h2>No posts found</h2>
                        <p>Check back soon for new content!</p>
                        <?php if ($search): ?>
                        <a href="blog" class="btn btn-primary">View All Posts</a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="blog-grid">
                        <?php foreach ($posts as $post): ?>
                        <article class="blog-card" itemscope itemtype="https://schema.org/BlogPosting">
                            <a href="blog/<?php echo $post['slug']; ?>" class="blog-card-image">
                                <?php if ($post['feature_image']): ?>
                                <img src="<?php echo htmlspecialchars($post['feature_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($post['feature_image_alt']); ?>"
                                     loading="lazy" itemprop="image">
                                <?php else: ?>
                                <div class="blog-card-placeholder"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </a>
                            <div class="blog-card-body">
                                <?php if (!empty($post['categories'])): ?>
                                <div class="blog-card-cats">
                                    <?php foreach ($post['categories'] as $cat): ?>
                                    <a href="blog?category=<?php echo $cat['slug']; ?>" class="blog-cat-badge"><?php echo htmlspecialchars($cat['name']); ?></a>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <h2 class="blog-card-title" itemprop="headline">
                                    <a href="blog/<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                                </h2>
                                <p class="blog-card-excerpt" itemprop="description"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                <div class="blog-card-meta">
                                    <span itemprop="author"><?php echo htmlspecialchars($post['author_name'] ?: 'Amity Team'); ?></span>
                                    <span>&bull;</span>
                                    <time datetime="<?php echo $post['publish_date']; ?>" itemprop="datePublished">
                                        <?php echo formatBlogDate($post['publish_date']); ?>
                                    </time>
                                    <span>&bull;</span>
                                    <span><?php echo getReadingTime($post['content']); ?> min read</span>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav class="blog-pagination" aria-label="Blog pagination">
                        <?php if ($page > 1): ?>
                        <a href="blog?<?php echo $category ? "category=$category&" : ''; ?>page=<?php echo $page - 1; ?>" class="page-btn" rel="prev">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="blog?<?php echo $category ? "category=$category&" : ''; ?>page=<?php echo $i; ?>" 
                           class="page-num <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <a href="blog?<?php echo $category ? "category=$category&" : ''; ?>page=<?php echo $page + 1; ?>" class="page-btn" rel="next">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </nav>
                    <?php endif; ?>
                    <?php endif; ?>
                </main>

                <!-- Sidebar -->
                <aside class="blog-sidebar">
                    <!-- Search -->
                    <div class="sidebar-widget">
                        <h3>Search Blog</h3>
                        <form action="blog" method="GET" class="blog-search-form">
                            <input type="text" name="s" placeholder="Search articles..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>

                    <!-- Categories -->
                    <?php if (!empty($categories)): ?>
                    <div class="sidebar-widget">
                        <h3>Categories</h3>
                        <ul class="sidebar-categories">
                            <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="blog?category=<?php echo $cat['slug']; ?>" class="<?php echo ($category === $cat['slug']) ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                    <span class="cat-count"><?php echo $cat['post_count']; ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Recent Posts -->
                    <?php if (!empty($recentPosts)): ?>
                    <div class="sidebar-widget">
                        <h3>Recent Posts</h3>
                        <div class="sidebar-recent">
                            <?php foreach ($recentPosts as $rp): ?>
                            <a href="blog/<?php echo $rp['slug']; ?>" class="recent-post-link">
                                <?php if ($rp['feature_image']): ?>
                                <img src="<?php echo htmlspecialchars($rp['feature_image']); ?>" alt="<?php echo htmlspecialchars($rp['feature_image_alt']); ?>" loading="lazy">
                                <?php endif; ?>
                                <div>
                                    <h4><?php echo htmlspecialchars($rp['title']); ?></h4>
                                    <span><?php echo formatBlogDate($rp['publish_date']); ?></span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- CTA Widget -->
                    <div class="sidebar-widget cta-widget">
                        <h3>Start Your Journey</h3>
                        <p>Get 75% Scholarship on Online MBA, BBA, BCA, MCA Programs</p>
                        <a href="/" class="btn btn-primary">Apply Now</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Amity Online</h3>
                    <p>India's leading UGC-approved online university offering world-class education with flexible learning options and comprehensive career support.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Programs</h3>
                    <div class="footer-links">
                        <a href="mba">MBA Programs</a>
                        <a href="bca">BCA Programs</a>
                        <a href="mca">MCA Programs</a>
                        <a href="bba">BBA Programs</a>
                        <a href="programs">All Programs</a>
                        <a href="blog">Blog</a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-phone"></i> +91 8920785477</p>
                    <p><i class="fas fa-envelope"></i> contact@onlineamityuniversity.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Noida, Uttar Pradesh, India</p>
                    <div class="accreditation-badges">
                        <span class="badge">UGC Approved</span>
                        <span class="badge">NAAC A+</span>
                        <span class="badge">QS Ranked</span>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Amity Online University. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms & Conditions</a></p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
