<?php
require_once __DIR__ . '/blog-config.php';
$conn = getDBConnection();

// Get slug from URL - try GET param first (from .htaccess), fallback to URI parsing
if (isset($_GET['slug']) && !empty($_GET['slug'])) {
    $slug = trim($_GET['slug']);
} else {
    $requestUri = $_SERVER['REQUEST_URI'];
    $slug = basename(parse_url($requestUri, PHP_URL_PATH));
    $slug = preg_replace('/\.php$/', '', $slug);
}

if (empty($slug) || $slug === 'blog') {
    header('Location: /blog');
    exit;
}

$post = null;
$toc = [];
$relatedPosts = [];
$recentPosts = [];
$categories = [];
$postCategories = [];
$postTags = [];
$readingTime = 0;

if ($conn) {
    try {
    $stmt = $conn->prepare("
        SELECT p.*, a.name AS author_name, a.bio AS author_bio, a.image AS author_image, a.author_page_url
        FROM blog_posts p
        LEFT JOIN blog_authors a ON p.author_id = a.id
        WHERE p.slug = :slug AND p.status = 'published' AND p.publish_date <= NOW()
    ");
    $stmt->execute([':slug' => $slug]);
    $post = $stmt->fetch();
    
    if (!$post) {
        http_response_code(404);
        header('Location: /blog');
        exit;
    }
    
    // Increment views
    $conn->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);
    
    // Build CTA widget data (used for both sidebar and inline [cta] shortcode)
    $ctaWidget = ['title' => 'Start Your Journey', 'text' => 'Get 75% Scholarship on Online MBA, BBA, BCA, MCA Programs', 'button_text' => 'Apply Now', 'button_url' => '/'];
    $showSidebarCta = true; // sidebar flag only
    if (!empty($post['cta_data'])) {
        $ctaFromPost = is_string($post['cta_data']) ? json_decode($post['cta_data'], true) : $post['cta_data'];
        if ($ctaFromPost) {
            if (!empty($ctaFromPost['title'])) $ctaWidget['title'] = $ctaFromPost['title'];
            if (!empty($ctaFromPost['text'])) $ctaWidget['text'] = $ctaFromPost['text'];
            if (!empty($ctaFromPost['button_text'])) $ctaWidget['button_text'] = $ctaFromPost['button_text'];
            if (!empty($ctaFromPost['button_url'])) $ctaWidget['button_url'] = $ctaFromPost['button_url'];
            if (isset($ctaFromPost['enabled']) && $ctaFromPost['enabled'] === false) {
                $showSidebarCta = false;
            }
        }
    }
    // Process [cta] shortcode BEFORE TOC extraction so it never appears as a heading
    if (strpos($post['content'], '[cta]') !== false) {
        $inlineCtaHtml = '<div class="inline-cta-block">'
            . '<p class="cta-inline-title">' . htmlspecialchars($ctaWidget['title']) . '</p>'
            . '<p>' . htmlspecialchars($ctaWidget['text']) . '</p>'
            . '<a href="' . htmlspecialchars($ctaWidget['button_url']) . '" class="btn btn-primary">'
            . htmlspecialchars($ctaWidget['button_text']) . '</a>'
            . '</div>';
        $post['content'] = str_replace('[cta]', $inlineCtaHtml, $post['content']);
    }

    // Add heading IDs for TOC (after [cta] replacement so CTA content is excluded)
    $post['content'] = addHeadingIds($post['content']);
    $toc = extractHeadings($post['content']);
    
    // Reading time
    $readingTime = getReadingTime($post['content']);
    
    // Categories
    $catStmt = $conn->prepare("SELECT c.id, c.name, c.slug FROM blog_categories c JOIN blog_post_categories pc ON c.id=pc.category_id WHERE pc.post_id=?");
    $catStmt->execute([$post['id']]);
    $postCategories = $catStmt->fetchAll();
    
    // Tags
    $tagStmt = $conn->prepare("SELECT t.name, t.slug FROM blog_tags t JOIN blog_post_tags pt ON t.id=pt.tag_id WHERE pt.post_id=?");
    $tagStmt->execute([$post['id']]);
    $postTags = $tagStmt->fetchAll();
    
    // Related posts
    $categoryIds = array_column($postCategories, 'id');
    if (!empty($categoryIds)) {
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $relStmt = $conn->prepare("
            SELECT DISTINCT p.id, p.title, p.slug, p.excerpt, p.feature_image, p.feature_image_alt, p.publish_date, a.name AS author_name
            FROM blog_posts p
            LEFT JOIN blog_authors a ON p.author_id=a.id
            JOIN blog_post_categories pc ON p.id=pc.post_id
            WHERE pc.category_id IN ($placeholders) AND p.id != ? AND p.status='published'
            ORDER BY p.publish_date DESC LIMIT 3
        ");
        $params = array_merge($categoryIds, [$post['id']]);
        $relStmt->execute($params);
        $relatedPosts = $relStmt->fetchAll();
    }
    
    // Recent posts for sidebar
    $recentPosts = $conn->query("SELECT id, title, slug, feature_image, feature_image_alt, publish_date FROM blog_posts WHERE status='published' AND publish_date<=NOW() ORDER BY publish_date DESC LIMIT 5")->fetchAll();
    
    // All categories
    $categories = $conn->query("SELECT c.*, COUNT(pc.post_id) as post_count FROM blog_categories c LEFT JOIN blog_post_categories pc ON c.id=pc.category_id LEFT JOIN blog_posts p ON pc.post_id=p.id AND p.status='published' GROUP BY c.id ORDER BY c.name")->fetchAll();
    } catch (PDOException $e) {
        error_log("Blog post query error: " . $e->getMessage());
        // If tables don't exist, redirect to blog listing
        if (!$post) {
            header('Location: /blog');
            exit;
        }
    }
}

$metaTitle = $post ? ($post['meta_title'] ?: $post['title']) : 'Blog - Amity Online University';
$metaDesc = $post ? ($post['meta_description'] ?: $post['excerpt']) : '';
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
    <meta name="description" content="<?php echo htmlspecialchars($metaDesc); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($post['focus_keyword']); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($post['author_name'] ?: 'Amity Online University'); ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($metaTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDesc); ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="https://onlineamityuniversity.com/blog/<?php echo $post['slug']; ?>">
    <meta property="og:image" content="https://onlineamityuniversity.com/<?php echo $post['feature_image']; ?>">
    <meta property="og:site_name" content="Amity Online University">
    <meta property="article:published_time" content="<?php echo $post['publish_date']; ?>">
    <meta property="article:modified_time" content="<?php echo $post['updated_at']; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($metaTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDesc); ?>">
    <meta name="twitter:image" content="https://onlineamityuniversity.com/<?php echo $post['feature_image']; ?>">
    
    <link rel="canonical" href="https://onlineamityuniversity.com/blog/<?php echo $post['slug']; ?>">
    <link rel="icon" href="../assets/images/favicon.png" type="image/png">
    
    <title><?php echo htmlspecialchars($metaTitle); ?> | Amity Online University</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css?v=20260221">
    <link rel="stylesheet" href="../css/blog.css?v=20260222">
    <style>
        /* Force table header styles – bypasses server-side CSS cache */
        .post-content table th {
            background-color: #0a2e73 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }
    </style>
    
    <!-- Article Schema -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Article",
      "headline": "<?php echo htmlspecialchars($post['title']); ?>",
      "description": "<?php echo htmlspecialchars($metaDesc); ?>",
      "image": "https://onlineamityuniversity.com/<?php echo $post['feature_image']; ?>",
      "author": {
        "@type": "Person",
        "name": "<?php echo htmlspecialchars($post['author_name'] ?: 'Amity Online Team'); ?>"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Amity Online University",
        "logo": {
          "@type": "ImageObject",
          "url": "https://onlineamityuniversity.com/assets/images/amity-logo.svg"
        }
      },
      "datePublished": "<?php echo $post['publish_date']; ?>",
      "dateModified": "<?php echo $post['updated_at']; ?>",
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "https://onlineamityuniversity.com/blog/<?php echo $post['slug']; ?>"
      },
      "keywords": "<?php echo htmlspecialchars($post['focus_keyword']); ?>"
    }
    </script>
    
    <!-- FAQ Schema (if FAQ exists in content) -->
    <?php
    // Extract FAQ items from content for schema
    preg_match_all('/<div class="faq-item">\s*<h4>(.*?)<\/h4>\s*<p>(.*?)<\/p>/si', $post['content'], $faqMatches, PREG_SET_ORDER);
    if (!empty($faqMatches)):
    ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        <?php foreach ($faqMatches as $i => $faq): ?>
        <?php echo $i > 0 ? ',' : ''; ?>
        {
          "@type": "Question",
          "name": "<?php echo htmlspecialchars(strip_tags($faq[1])); ?>",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "<?php echo htmlspecialchars(strip_tags($faq[2])); ?>"
          }
        }
        <?php endforeach; ?>
      ]
    }
    </script>
    <?php endif; ?>
    
    <!-- BreadcrumbList Schema -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://onlineamityuniversity.com"},
        {"@type": "ListItem", "position": 2, "name": "Blog", "item": "https://onlineamityuniversity.com/blog"},
        {"@type": "ListItem", "position": 3, "name": "<?php echo htmlspecialchars($post['title']); ?>", "item": "https://onlineamityuniversity.com/blog/<?php echo $post['slug']; ?>"}
      ]
    }
    </script>
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <nav class="nav container">
            <a href="/" class="logo" style="display: flex; align-items: center; gap: 0.5rem;">
                <img src="../assets/images/amity-logo.svg" alt="Amity Online" style="height: 50px; width: auto;">
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
                <span></span>
                <span></span>
                <span></span>
            </button>
        </nav>
    </header>

    <!-- Breadcrumb -->
    <section class="blog-breadcrumb-bar">
        <div class="container">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="/">Home</a>
                <span>/</span>
                <a href="/blog">Blog</a>
                <span>/</span>
                <span><?php echo htmlspecialchars(truncateText($post['title'], 50)); ?></span>
            </nav>
        </div>
    </section>

    <!-- Blog Post Content -->
    <article class="blog-post-section" itemscope itemtype="https://schema.org/Article">
        <div class="container">
            <div class="blog-post-layout">
                <!-- Main Content -->
                <main class="blog-post-main">
                    <!-- Post Header -->
                    <header class="post-header">
                        <?php if (!empty($postCategories)): ?>
                        <div class="post-categories">
                            <?php foreach ($postCategories as $cat): ?>
                            <a href="/blog?category=<?php echo $cat['slug']; ?>" class="blog-cat-badge"><?php echo htmlspecialchars($cat['name']); ?></a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <h1 class="post-title" itemprop="headline"><?php echo htmlspecialchars($post['title']); ?></h1>
                        
                        <div class="post-meta">
                            <?php if ($post['author_image']): ?>
                            <img src="../<?php echo $post['author_image']; ?>" alt="<?php echo htmlspecialchars($post['author_name']); ?>" class="author-avatar">
                            <?php else: ?>
                            <div class="author-avatar-placeholder"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                            <div class="post-meta-info">
                                <span class="post-author" itemprop="author">
                                    <?php if ($post['author_page_url']): ?>
                                    <a href="<?php echo htmlspecialchars($post['author_page_url']); ?>"><?php echo htmlspecialchars($post['author_name'] ?: 'Amity Team'); ?></a>
                                    <?php else: ?>
                                    <?php echo htmlspecialchars($post['author_name'] ?: 'Amity Team'); ?>
                                    <?php endif; ?>
                                </span>
                                <div class="post-meta-details">
                                    <time datetime="<?php echo $post['publish_date']; ?>" itemprop="datePublished">
                                        <?php echo formatBlogDate($post['publish_date']); ?>
                                    </time>
                                    <span>&bull;</span>
                                    <span><?php echo $readingTime; ?> min read</span>
                                    <?php if ($post['updated_at'] !== $post['publish_date']): ?>
                                    <span>&bull;</span>
                                    <span>Updated: <time datetime="<?php echo $post['updated_at']; ?>" itemprop="dateModified"><?php echo formatBlogDate($post['updated_at']); ?></time></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </header>
                    
                    <!-- Feature Image -->
                    <?php if ($post['feature_image']): ?>
                    <figure class="post-feature-image">
                        <img src="../<?php echo htmlspecialchars($post['feature_image']); ?>" 
                             alt="<?php echo htmlspecialchars($post['feature_image_alt']); ?>"
                             <?php if ($post['feature_image_title']): ?>title="<?php echo htmlspecialchars($post['feature_image_title']); ?>"<?php endif; ?>
                             itemprop="image"
                             width="1200" height="628">
                    </figure>
                    <?php endif; ?>

                    <!-- Table of Contents -->
                    <?php if (!empty($toc) && count($toc) >= 2): ?>
                    <nav class="table-of-contents" id="tableOfContents" aria-label="Table of Contents">
                        <div class="toc-header" id="tocToggle">
                            <h2><i class="fas fa-list"></i> Table of Contents</h2>
                            <button class="toc-toggle-btn" aria-label="Toggle Table of Contents">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                        <ol class="toc-list" id="tocList">
                            <?php foreach ($toc as $heading): ?>
                            <li class="toc-item toc-level-<?php echo $heading['level']; ?>">
                                <a href="#<?php echo $heading['id']; ?>"><?php echo htmlspecialchars($heading['text']); ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                    <?php endif; ?>

                    <!-- Post Content -->
                    <div class="post-content" itemprop="articleBody">
                        <?php echo $post['content']; ?>
                    </div>

                    <!-- Tags -->
                    <?php if (!empty($postTags)): ?>
                    <div class="post-tags">
                        <span><i class="fas fa-tags"></i> Tags:</span>
                        <?php foreach ($postTags as $tag): ?>
                        <a href="/blog?tag=<?php echo $tag['slug']; ?>" class="post-tag"><?php echo htmlspecialchars($tag['name']); ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Share Buttons -->
                    <div class="post-share">
                        <span>Share this article:</span>
                        <div class="share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=https://onlineamityuniversity.com/blog/<?php echo $post['slug']; ?>" target="_blank" rel="noopener" class="share-btn facebook" title="Share on Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=https://onlineamityuniversity.com/blog/<?php echo $post['slug']; ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" rel="noopener" class="share-btn twitter" title="Share on Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=https://onlineamityuniversity.com/blog/<?php echo $post['slug']; ?>" target="_blank" rel="noopener" class="share-btn linkedin" title="Share on LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($post['title'] . ' - https://onlineamityuniversity.com/blog/' . $post['slug']); ?>" target="_blank" rel="noopener" class="share-btn whatsapp" title="Share on WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <button onclick="copyPostLink()" class="share-btn copy" title="Copy Link">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Author Box -->
                    <?php if ($post['author_name']): ?>
                    <div class="author-box">
                        <?php if ($post['author_image']): ?>
                        <img src="../<?php echo $post['author_image']; ?>" alt="<?php echo htmlspecialchars($post['author_name']); ?>" class="author-box-image">
                        <?php else: ?>
                        <div class="author-box-placeholder"><i class="fas fa-user"></i></div>
                        <?php endif; ?>
                        <div class="author-box-info">
                            <h3>
                                <?php if ($post['author_page_url']): ?>
                                <a href="<?php echo htmlspecialchars($post['author_page_url']); ?>"><?php echo htmlspecialchars($post['author_name']); ?></a>
                                <?php else: ?>
                                <?php echo htmlspecialchars($post['author_name']); ?>
                                <?php endif; ?>
                            </h3>
                            <?php if ($post['author_bio']): ?>
                            <p><?php echo htmlspecialchars($post['author_bio']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Related Posts -->
                    <?php if (!empty($relatedPosts)): ?>
                    <section class="related-posts">
                        <h2>Related Articles</h2>
                        <div class="related-posts-grid">
                            <?php foreach ($relatedPosts as $rp): ?>
                            <a href="/blog/<?php echo $rp['slug']; ?>" class="related-card">
                                <?php if ($rp['feature_image']): ?>
                                <img src="../<?php echo htmlspecialchars($rp['feature_image']); ?>" alt="<?php echo htmlspecialchars($rp['feature_image_alt']); ?>" loading="lazy">
                                <?php endif; ?>
                                <div>
                                    <h3><?php echo htmlspecialchars($rp['title']); ?></h3>
                                    <span><?php echo formatBlogDate($rp['publish_date']); ?></span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>
                </main>

                <!-- Sidebar -->
                <aside class="blog-sidebar blog-post-sidebar">
                    <!-- Sticky TOC for Desktop -->
                    <?php if (!empty($toc) && count($toc) >= 2): ?>
                    <div class="sidebar-widget sidebar-toc" id="sidebarToc">
                        <h3>Contents</h3>
                        <ol class="sidebar-toc-list">
                            <?php foreach ($toc as $heading): ?>
                            <li class="toc-level-<?php echo $heading['level']; ?>">
                                <a href="#<?php echo $heading['id']; ?>" class="toc-sidebar-link"><?php echo htmlspecialchars($heading['text']); ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                    <?php endif; ?>

                    <!-- Categories -->
                    <?php if (!empty($categories)): ?>
                    <div class="sidebar-widget">
                        <h3>Categories</h3>
                        <ul class="sidebar-categories">
                            <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="/blog?category=<?php echo $cat['slug']; ?>">
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
                            <a href="/blog/<?php echo $rp['slug']; ?>" class="recent-post-link">
                                <?php if ($rp['feature_image']): ?>
                                <img src="../<?php echo htmlspecialchars($rp['feature_image']); ?>" alt="<?php echo htmlspecialchars($rp['feature_image_alt']); ?>" loading="lazy">
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

                    <!-- CTA -->
                    <?php if ($showSidebarCta): ?>
                    <div class="sidebar-widget cta-widget">
                        <h3><?php echo htmlspecialchars($ctaWidget['title']); ?></h3>
                        <p><?php echo htmlspecialchars($ctaWidget['text']); ?></p>
                        <a href="<?php echo htmlspecialchars($ctaWidget['button_url']); ?>" class="btn btn-primary"><?php echo htmlspecialchars($ctaWidget['button_text']); ?></a>
                    </div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </article>

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
                        <a href="/mba">MBA Programs</a>
                        <a href="/bca">BCA Programs</a>
                        <a href="/mca">MCA Programs</a>
                        <a href="/bba">BBA Programs</a>
                        <a href="/programs">All Programs</a>
                        <a href="/blog">Blog</a>
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

    <script src="../js/script.js"></script>
    <script>
        // Table of Contents Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const tocToggle = document.getElementById('tocToggle');
            const tocList = document.getElementById('tocList');
            
            if (tocToggle && tocList) {
                tocToggle.addEventListener('click', function() {
                    tocList.classList.toggle('collapsed');
                    const icon = this.querySelector('.toc-toggle-btn i');
                    icon.classList.toggle('fa-chevron-up');
                    icon.classList.toggle('fa-chevron-down');
                });
            }
            
            // Highlight active TOC item on scroll
            const headings = document.querySelectorAll('.post-content h2[id], .post-content h3[id], .post-content h4[id]');
            const tocLinks = document.querySelectorAll('.toc-sidebar-link, .toc-list a');
            
            if (headings.length > 0 && tocLinks.length > 0) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            tocLinks.forEach(link => link.classList.remove('active'));
                            document.querySelectorAll(`a[href="#${entry.target.id}"]`).forEach(link => {
                                link.classList.add('active');
                            });
                        }
                    });
                }, { rootMargin: '-80px 0px -70% 0px' });
                
                headings.forEach(h => observer.observe(h));
            }
            
            // Smooth scroll for TOC links
            document.querySelectorAll('.toc-list a, .sidebar-toc-list a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const offset = 80;
                        const pos = target.getBoundingClientRect().top + window.pageYOffset - offset;
                        window.scrollTo({ top: pos, behavior: 'smooth' });
                    }
                });
            });
        });
        
        // Copy link to clipboard
        function copyPostLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const btn = document.querySelector('.share-btn.copy');
                btn.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => { btn.innerHTML = '<i class="fas fa-link"></i>'; }, 2000);
            });
        }
    </script>
</body>
</html>
