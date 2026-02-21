<?php
/**
 * Blog API - Handles all CRUD operations for the Blog CMS
 * Amity Online University
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/blog-config.php';

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    
    // =====================================================
    // PUBLIC ENDPOINTS
    // =====================================================
    
    case 'get_posts':
        getPublishedPosts($conn);
        break;
    
    case 'get_post':
        getPostBySlug($conn);
        break;
    
    case 'get_categories':
        getCategories($conn);
        break;
    
    case 'get_posts_by_category':
        getPostsByCategory($conn);
        break;
    
    case 'search_posts':
        searchPosts($conn);
        break;
    
    case 'get_recent_posts':
        getRecentPosts($conn);
        break;
    
    // =====================================================
    // ADMIN ENDPOINTS
    // =====================================================
    
    case 'admin_login':
        adminLogin($conn);
        break;
    
    case 'admin_logout':
        adminLogout();
        break;
    
    case 'admin_get_posts':
        apiRequireAuth();
        adminGetPosts($conn);
        break;
    
    case 'admin_get_post':
        apiRequireAuth();
        adminGetPost($conn);
        break;
    
    case 'admin_save_post':
        apiRequireAuth();
        adminSavePost($conn);
        break;
    
    case 'admin_delete_post':
        apiRequireAuth();
        adminDeletePost($conn);
        break;
    
    case 'admin_upload_image':
        apiRequireAuth();
        adminUploadImage($conn);
        break;
    
    case 'admin_get_categories':
        apiRequireAuth();
        adminGetCategories($conn);
        break;
    
    case 'admin_save_category':
        apiRequireAuth();
        adminSaveCategory($conn);
        break;
    
    case 'admin_delete_category':
        apiRequireAuth();
        adminDeleteCategory($conn);
        break;
    
    case 'admin_get_tags':
        apiRequireAuth();
        adminGetTags($conn);
        break;
    
    case 'admin_save_tag':
        apiRequireAuth();
        adminSaveTag($conn);
        break;
    
    case 'admin_get_authors':
        apiRequireAuth();
        adminGetAuthors($conn);
        break;
    
    case 'admin_save_author':
        apiRequireAuth();
        adminSaveAuthor($conn);
        break;
    
    case 'admin_dashboard_stats':
        apiRequireAuth();
        adminDashboardStats($conn);
        break;
    
    case 'generate_slug':
        $title = isset($_GET['title']) ? $_GET['title'] : '';
        echo json_encode(['success' => true, 'slug' => generateSlug($title)]);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// =====================================================
// PUBLIC FUNCTIONS
// =====================================================

function getPublishedPosts($conn) {
    try {
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $limit = BLOG_POSTS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $countStmt = $conn->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published' AND publish_date <= NOW()");
        $total = $countStmt->fetchColumn();
        
        // Get posts
        $stmt = $conn->prepare("
            SELECT p.id, p.title, p.slug, p.excerpt, p.feature_image, p.feature_image_alt, 
                   p.publish_date, p.views, a.name AS author_name, a.image AS author_image
            FROM blog_posts p
            LEFT JOIN blog_authors a ON p.author_id = a.id
            WHERE p.status = 'published' AND p.publish_date <= NOW()
            ORDER BY p.publish_date DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        
        // Get categories for each post
        foreach ($posts as &$post) {
            $catStmt = $conn->prepare("
                SELECT c.name, c.slug FROM blog_categories c
                JOIN blog_post_categories pc ON c.id = pc.category_id
                WHERE pc.post_id = :post_id
            ");
            $catStmt->execute([':post_id' => $post['id']]);
            $post['categories'] = $catStmt->fetchAll();
            $post['reading_time'] = null; // Will be calculated from content if needed
            $post['publish_date_formatted'] = formatBlogDate($post['publish_date']);
        }
        
        echo json_encode([
            'success' => true,
            'posts' => $posts,
            'total' => (int) $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ]);
    } catch (PDOException $e) {
        error_log("Blog get posts error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error. Blog tables may not be created yet.']);
    }
}

function getPostBySlug($conn) {
    try {
        $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
        
        if (empty($slug)) {
            echo json_encode(['success' => false, 'message' => 'Slug is required']);
            return;
        }
        
        $stmt = $conn->prepare("
            SELECT p.*, a.name AS author_name, a.bio AS author_bio, a.image AS author_image, a.author_page_url
            FROM blog_posts p
            LEFT JOIN blog_authors a ON p.author_id = a.id
            WHERE p.slug = :slug AND p.status = 'published' AND p.publish_date <= NOW()
        ");
        $stmt->execute([':slug' => $slug]);
        $post = $stmt->fetch();
        
        if (!$post) {
            echo json_encode(['success' => false, 'message' => 'Post not found']);
            return;
        }
        
        // Increment view count
        $conn->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = :id")->execute([':id' => $post['id']]);
        
        // Add heading IDs to content
        $post['content'] = addHeadingIds($post['content']);
        
        // Extract TOC
        $post['toc'] = extractHeadings($post['content']);
        
        // Reading time
        $post['reading_time'] = getReadingTime($post['content']);
        
        // Categories
        $catStmt = $conn->prepare("
            SELECT c.id, c.name, c.slug FROM blog_categories c
            JOIN blog_post_categories pc ON c.id = pc.category_id
            WHERE pc.post_id = :post_id
        ");
        $catStmt->execute([':post_id' => $post['id']]);
        $post['categories'] = $catStmt->fetchAll();
        
        // Tags
        $tagStmt = $conn->prepare("
            SELECT t.id, t.name, t.slug FROM blog_tags t
            JOIN blog_post_tags pt ON t.id = pt.tag_id
            WHERE pt.post_id = :post_id
        ");
        $tagStmt->execute([':post_id' => $post['id']]);
        $post['tags'] = $tagStmt->fetchAll();
        
        // Related posts (same categories)
        $categoryIds = array_column($post['categories'], 'id');
        if (!empty($categoryIds)) {
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $relatedStmt = $conn->prepare("
                SELECT DISTINCT p.id, p.title, p.slug, p.excerpt, p.feature_image, p.feature_image_alt, p.publish_date
                FROM blog_posts p
                JOIN blog_post_categories pc ON p.id = pc.post_id
                WHERE pc.category_id IN ($placeholders) AND p.id != ? AND p.status = 'published'
                ORDER BY p.publish_date DESC LIMIT 3
            ");
            $params = array_merge($categoryIds, [$post['id']]);
            $relatedStmt->execute($params);
            $post['related_posts'] = $relatedStmt->fetchAll();
        } else {
            $post['related_posts'] = [];
        }
        
        $post['publish_date_formatted'] = formatBlogDate($post['publish_date']);
        $post['updated_date_formatted'] = formatBlogDate($post['updated_at']);
        
        echo json_encode(['success' => true, 'post' => $post]);
    } catch (PDOException $e) {
        error_log("Blog get post error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}

function getCategories($conn) {
    try {
        $stmt = $conn->query("
            SELECT c.*, COUNT(pc.post_id) as post_count
            FROM blog_categories c
            LEFT JOIN blog_post_categories pc ON c.id = pc.category_id
            LEFT JOIN blog_posts p ON pc.post_id = p.id AND p.status = 'published'
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        echo json_encode(['success' => true, 'categories' => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        error_log("Blog get categories error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}

function getPostsByCategory($conn) {
    try {
        $slug = isset($_GET['category']) ? trim($_GET['category']) : '';
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $limit = BLOG_POSTS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        // Get category
        $catStmt = $conn->prepare("SELECT * FROM blog_categories WHERE slug = :slug");
        $catStmt->execute([':slug' => $slug]);
        $category = $catStmt->fetch();
        
        if (!$category) {
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        
        $countStmt = $conn->prepare("
            SELECT COUNT(DISTINCT p.id) FROM blog_posts p
            JOIN blog_post_categories pc ON p.id = pc.post_id
            WHERE pc.category_id = :cat_id AND p.status = 'published' AND p.publish_date <= NOW()
        ");
        $countStmt->execute([':cat_id' => $category['id']]);
        $total = $countStmt->fetchColumn();
        
        $stmt = $conn->prepare("
            SELECT p.id, p.title, p.slug, p.excerpt, p.feature_image, p.feature_image_alt,
                   p.publish_date, p.views, a.name AS author_name
            FROM blog_posts p
            LEFT JOIN blog_authors a ON p.author_id = a.id
            JOIN blog_post_categories pc ON p.id = pc.post_id
            WHERE pc.category_id = :cat_id AND p.status = 'published' AND p.publish_date <= NOW()
            ORDER BY p.publish_date DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':cat_id', $category['id'], PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'category' => $category,
            'posts' => $stmt->fetchAll(),
            'total' => (int) $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ]);
    } catch (PDOException $e) {
        error_log("Blog get posts by category error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}

function searchPosts($conn) {
    try {
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        if (strlen($query) < 2) {
            echo json_encode(['success' => false, 'message' => 'Search query too short']);
            return;
        }
        
        $stmt = $conn->prepare("
            SELECT p.id, p.title, p.slug, p.excerpt, p.feature_image, p.feature_image_alt, p.publish_date,
                   a.name AS author_name
            FROM blog_posts p
            LEFT JOIN blog_authors a ON p.author_id = a.id
            WHERE p.status = 'published' AND p.publish_date <= NOW()
            AND (p.title LIKE :q1 OR p.content LIKE :q2 OR p.focus_keyword LIKE :q3)
            ORDER BY p.publish_date DESC
            LIMIT 20
        ");
        $searchTerm = '%' . $query . '%';
        $stmt->execute([':q1' => $searchTerm, ':q2' => $searchTerm, ':q3' => $searchTerm]);
        
        echo json_encode(['success' => true, 'posts' => $stmt->fetchAll(), 'query' => $query]);
    } catch (PDOException $e) {
        error_log("Blog search posts error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}

function getRecentPosts($conn) {
    try {
        $limit = isset($_GET['limit']) ? min(10, max(1, (int) $_GET['limit'])) : 5;
        
        $stmt = $conn->prepare("
            SELECT p.id, p.title, p.slug, p.feature_image, p.feature_image_alt, p.publish_date
            FROM blog_posts p
            WHERE p.status = 'published' AND p.publish_date <= NOW()
            ORDER BY p.publish_date DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'posts' => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        error_log("Blog get recent posts error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}

// =====================================================
// ADMIN FUNCTIONS
// =====================================================

function adminLogin($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = isset($input['username']) ? trim($input['username']) : '';
        $password = isset($input['password']) ? $input['password'] : '';
        
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password required']);
            return;
        }
        
        $stmt = $conn->prepare("SELECT * FROM blog_admin_users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION[BLOG_ADMIN_SESSION_NAME] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name']
            ];
            echo json_encode(['success' => true, 'message' => 'Login successful', 'user' => ['name' => $user['name']]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } catch (PDOException $e) {
        error_log("Blog admin login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error. Please ensure blog tables are created by running blog-database.sql']);
    }
}

function adminLogout() {
    unset($_SESSION[BLOG_ADMIN_SESSION_NAME]);
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out']);
}

function adminGetPosts($conn) {
    try {
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $where = "1=1";
        $params = [];
        
        if ($status && in_array($status, ['draft', 'pending', 'published', 'scheduled'])) {
            $where .= " AND p.status = :status";
            $params[':status'] = $status;
        }
        
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM blog_posts p WHERE $where");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        $stmt = $conn->prepare("
            SELECT p.id, p.title, p.slug, p.status, p.publish_date, p.views, p.updated_at,
                   a.name AS author_name
            FROM blog_posts p
            LEFT JOIN blog_authors a ON p.author_id = a.id
            WHERE $where
            ORDER BY p.updated_at DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        
        echo json_encode([
            'success' => true,
            'posts' => $stmt->fetchAll(),
            'total' => (int) $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ]);
    } catch (PDOException $e) {
        error_log("Blog admin get posts error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error. Please ensure blog tables are created.']);
    }
}

function adminGetPost($conn) {
    try {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        
        $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $post = $stmt->fetch();
        
        if (!$post) {
            echo json_encode(['success' => false, 'message' => 'Post not found']);
            return;
        }
        
        // Categories
        $catStmt = $conn->prepare("SELECT category_id FROM blog_post_categories WHERE post_id = :id");
        $catStmt->execute([':id' => $id]);
        $post['category_ids'] = $catStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Tags
        $tagStmt = $conn->prepare("
            SELECT t.id, t.name FROM blog_tags t
            JOIN blog_post_tags pt ON t.id = pt.tag_id
            WHERE pt.post_id = :id
        ");
        $tagStmt->execute([':id' => $id]);
        $post['tags'] = $tagStmt->fetchAll();
        
        echo json_encode(['success' => true, 'post' => $post]);
    } catch (PDOException $e) {
        error_log("Blog admin get post error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}

function adminSavePost($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required = ['title', 'focus_keyword', 'excerpt', 'content', 'status'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                return;
            }
        }
        
        $id = isset($input['id']) ? (int) $input['id'] : 0;
        $title = trim($input['title']);
        $slug = !empty($input['slug']) ? generateSlug($input['slug']) : generateSlug($title);
    $metaTitle = !empty($input['meta_title']) ? substr(trim($input['meta_title']), 0, 70) : substr($title, 0, 60);
    $metaDescription = !empty($input['meta_description']) ? substr(trim($input['meta_description']), 0, 300) : substr(trim($input['excerpt']), 0, 250);
    $focusKeyword = trim($input['focus_keyword']);
    $primaryKeyword = isset($input['primary_keyword']) ? trim($input['primary_keyword']) : $focusKeyword;
    $excerpt = substr(trim($input['excerpt']), 0, 300);
    $content = $input['content']; // sanitizeBlogContent is called if needed
    $featureImage = isset($input['feature_image']) ? trim($input['feature_image']) : '';
    $featureImageAlt = isset($input['feature_image_alt']) ? trim($input['feature_image_alt']) : '';
    $featureImageTitle = isset($input['feature_image_title']) ? trim($input['feature_image_title']) : '';
    $authorId = isset($input['author_id']) ? (int) $input['author_id'] : null;
    $status = $input['status'];
    $publishDate = null;
    $scheduledDate = null;
    
    if ($status === 'published') {
        $publishDate = isset($input['publish_date']) && !empty($input['publish_date']) 
            ? $input['publish_date'] 
            : date('Y-m-d H:i:s');
    } elseif ($status === 'scheduled') {
        $scheduledDate = isset($input['scheduled_date']) ? $input['scheduled_date'] : null;
        if (empty($scheduledDate)) {
            echo json_encode(['success' => false, 'message' => 'Scheduled date is required for scheduled posts']);
            return;
        }
    }
    
    // Check slug uniqueness
    $slugCheck = $conn->prepare("SELECT id FROM blog_posts WHERE slug = :slug AND id != :id");
    $slugCheck->execute([':slug' => $slug, ':id' => $id]);
    if ($slugCheck->fetch()) {
        $slug .= '-' . time();
    }
    
        $conn->beginTransaction();
        
        if ($id > 0) {
            // Update existing post
            $stmt = $conn->prepare("
                UPDATE blog_posts SET 
                    title = :title, slug = :slug, meta_title = :meta_title, meta_description = :meta_description,
                    focus_keyword = :focus_keyword, primary_keyword = :primary_keyword, excerpt = :excerpt,
                    content = :content, feature_image = :feature_image, feature_image_alt = :feature_image_alt,
                    feature_image_title = :feature_image_title, author_id = :author_id, status = :status,
                    publish_date = :publish_date, scheduled_date = :scheduled_date, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':title' => $title, ':slug' => $slug, ':meta_title' => $metaTitle,
                ':meta_description' => $metaDescription, ':focus_keyword' => $focusKeyword,
                ':primary_keyword' => $primaryKeyword, ':excerpt' => $excerpt, ':content' => $content,
                ':feature_image' => $featureImage, ':feature_image_alt' => $featureImageAlt,
                ':feature_image_title' => $featureImageTitle, ':author_id' => $authorId,
                ':status' => $status, ':publish_date' => $publishDate, ':scheduled_date' => $scheduledDate,
                ':id' => $id
            ]);
        } else {
            // Create new post
            $stmt = $conn->prepare("
                INSERT INTO blog_posts (title, slug, meta_title, meta_description, focus_keyword, primary_keyword,
                    excerpt, content, feature_image, feature_image_alt, feature_image_title, author_id, status,
                    publish_date, scheduled_date, created_at, updated_at)
                VALUES (:title, :slug, :meta_title, :meta_description, :focus_keyword, :primary_keyword,
                    :excerpt, :content, :feature_image, :feature_image_alt, :feature_image_title, :author_id,
                    :status, :publish_date, :scheduled_date, NOW(), NOW())
            ");
            $stmt->execute([
                ':title' => $title, ':slug' => $slug, ':meta_title' => $metaTitle,
                ':meta_description' => $metaDescription, ':focus_keyword' => $focusKeyword,
                ':primary_keyword' => $primaryKeyword, ':excerpt' => $excerpt, ':content' => $content,
                ':feature_image' => $featureImage, ':feature_image_alt' => $featureImageAlt,
                ':feature_image_title' => $featureImageTitle, ':author_id' => $authorId,
                ':status' => $status, ':publish_date' => $publishDate, ':scheduled_date' => $scheduledDate
            ]);
            $id = $conn->lastInsertId();
        }
        
        // Update categories
        $conn->prepare("DELETE FROM blog_post_categories WHERE post_id = :id")->execute([':id' => $id]);
        if (!empty($input['category_ids'])) {
            $catStmt = $conn->prepare("INSERT INTO blog_post_categories (post_id, category_id) VALUES (:post_id, :cat_id)");
            foreach ($input['category_ids'] as $catId) {
                $catStmt->execute([':post_id' => $id, ':cat_id' => (int) $catId]);
            }
        }
        
        // Update tags
        $conn->prepare("DELETE FROM blog_post_tags WHERE post_id = :id")->execute([':id' => $id]);
        if (!empty($input['tags'])) {
            foreach ($input['tags'] as $tagName) {
                $tagName = trim($tagName);
                if (empty($tagName)) continue;
                
                $tagSlug = generateSlug($tagName);
                // Insert tag if not exists
                $tagCheck = $conn->prepare("SELECT id FROM blog_tags WHERE slug = :slug");
                $tagCheck->execute([':slug' => $tagSlug]);
                $tag = $tagCheck->fetch();
                
                if ($tag) {
                    $tagId = $tag['id'];
                } else {
                    $conn->prepare("INSERT INTO blog_tags (name, slug) VALUES (:name, :slug)")
                        ->execute([':name' => $tagName, ':slug' => $tagSlug]);
                    $tagId = $conn->lastInsertId();
                }
                
                $conn->prepare("INSERT INTO blog_post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)")
                    ->execute([':post_id' => $id, ':tag_id' => $tagId]);
            }
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Post saved successfully', 'id' => $id, 'slug' => $slug]);
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Blog admin save post error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error saving post: ' . $e->getMessage()]);
    }
}

function adminDeletePost($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? (int) $input['id'] : 0;
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
            return;
        }
        
        $conn->prepare("DELETE FROM blog_posts WHERE id = :id")->execute([':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Post deleted']);
    } catch (Exception $e) {
        error_log("Blog admin delete post error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error deleting post: ' . $e->getMessage()]);
    }
}

function adminUploadImage($conn) {
    try {
        if (!isset($_FILES['image'])) {
            echo json_encode(['success' => false, 'message' => 'No image file provided']);
            return;
        }
        
        $altText = isset($_POST['alt_text']) ? trim($_POST['alt_text']) : '';
        if (empty($altText)) {
            echo json_encode(['success' => false, 'message' => 'Alt text is required for all images']);
            return;
        }
        
        $subfolder = isset($_POST['subfolder']) ? trim($_POST['subfolder']) : '';
    $result = uploadBlogImage($_FILES['image'], $subfolder);
    
    if ($result['success']) {
        // Save to blog_images table
        $caption = isset($_POST['caption']) ? trim($_POST['caption']) : '';
        $imageTitle = isset($_POST['image_title']) ? trim($_POST['image_title']) : '';
        $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : null;
        
        $stmt = $conn->prepare("
            INSERT INTO blog_images (post_id, filename, alt_text, caption, image_title) 
            VALUES (:post_id, :filename, :alt_text, :caption, :image_title)
        ");
        $stmt->execute([
            ':post_id' => $postId ?: null,
            ':filename' => $result['filename'],
            ':alt_text' => $altText,
            ':caption' => $caption,
            ':image_title' => $imageTitle
        ]);
        
        $result['image_id'] = $conn->lastInsertId();
        $result['alt_text'] = $altText;
    }
    
    echo json_encode($result);
    } catch (Exception $e) {
        error_log("Blog admin upload image error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error uploading image: ' . $e->getMessage()]);
    }
}

function adminGetCategories($conn) {
    try {
    $stmt = $conn->query("SELECT * FROM blog_categories ORDER BY name ASC");
    echo json_encode(['success' => true, 'categories' => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        error_log("Blog admin get categories error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}

function adminSaveCategory($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? (int) $input['id'] : 0;
        $name = isset($input['name']) ? trim($input['name']) : '';
        $description = isset($input['description']) ? trim($input['description']) : '';
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            return;
        }
        
        $slug = generateSlug($name);
        
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE blog_categories SET name = :name, slug = :slug, description = :desc WHERE id = :id");
            $stmt->execute([':name' => $name, ':slug' => $slug, ':desc' => $description, ':id' => $id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO blog_categories (name, slug, description) VALUES (:name, :slug, :desc)");
            $stmt->execute([':name' => $name, ':slug' => $slug, ':desc' => $description]);
            $id = $conn->lastInsertId();
        }
        echo json_encode(['success' => true, 'message' => 'Category saved', 'id' => $id]);
    } catch (Exception $e) {
        error_log("Blog admin save category error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function adminDeleteCategory($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? (int) $input['id'] : 0;
        
        $conn->prepare("DELETE FROM blog_categories WHERE id = :id")->execute([':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Category deleted']);
    } catch (Exception $e) {
        error_log("Blog admin delete category error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function adminGetTags($conn) {
    try {
    $stmt = $conn->query("SELECT * FROM blog_tags ORDER BY name ASC");
    echo json_encode(['success' => true, 'tags' => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        error_log("Blog admin get tags error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}

function adminSaveTag($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = isset($input['name']) ? trim($input['name']) : '';
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Tag name is required']);
            return;
        }
        
        $slug = generateSlug($name);
        
        $check = $conn->prepare("SELECT id FROM blog_tags WHERE slug = :slug");
        $check->execute([':slug' => $slug]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Tag already exists']);
            return;
        }
        
        $stmt = $conn->prepare("INSERT INTO blog_tags (name, slug) VALUES (:name, :slug)");
        $stmt->execute([':name' => $name, ':slug' => $slug]);
        echo json_encode(['success' => true, 'message' => 'Tag created', 'id' => $conn->lastInsertId(), 'name' => $name]);
    } catch (Exception $e) {
        error_log("Blog admin save tag error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function adminGetAuthors($conn) {
    try {
    $stmt = $conn->query("SELECT * FROM blog_authors ORDER BY name ASC");
    echo json_encode(['success' => true, 'authors' => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        error_log("Blog admin get authors error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}

function adminSaveAuthor($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? (int) $input['id'] : 0;
        $name = isset($input['name']) ? trim($input['name']) : '';
        $bio = isset($input['bio']) ? trim($input['bio']) : '';
        $image = isset($input['image']) ? trim($input['image']) : '';
        $pageUrl = isset($input['author_page_url']) ? trim($input['author_page_url']) : '';
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Author name is required']);
            return;
        }
        
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE blog_authors SET name=:name, bio=:bio, image=:image, author_page_url=:url WHERE id=:id");
            $stmt->execute([':name' => $name, ':bio' => $bio, ':image' => $image, ':url' => $pageUrl, ':id' => $id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO blog_authors (name, bio, image, author_page_url) VALUES (:name, :bio, :image, :url)");
            $stmt->execute([':name' => $name, ':bio' => $bio, ':image' => $image, ':url' => $pageUrl]);
            $id = $conn->lastInsertId();
        }
        echo json_encode(['success' => true, 'message' => 'Author saved', 'id' => $id]);
    } catch (Exception $e) {
        error_log("Blog admin save author error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function adminDashboardStats($conn) {
    try {
        $stats = [];
        
        $stats['total_posts'] = $conn->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
        $stats['published'] = $conn->query("SELECT COUNT(*) FROM blog_posts WHERE status='published'")->fetchColumn();
        $stats['drafts'] = $conn->query("SELECT COUNT(*) FROM blog_posts WHERE status='draft'")->fetchColumn();
        $stats['scheduled'] = $conn->query("SELECT COUNT(*) FROM blog_posts WHERE status='scheduled'")->fetchColumn();
        $stats['total_views'] = $conn->query("SELECT COALESCE(SUM(views), 0) FROM blog_posts")->fetchColumn();
        $stats['categories'] = $conn->query("SELECT COUNT(*) FROM blog_categories")->fetchColumn();
        
        // Recent posts
        $recentStmt = $conn->query("
            SELECT id, title, status, views, publish_date, updated_at 
            FROM blog_posts ORDER BY updated_at DESC LIMIT 5
        ");
        $stats['recent_posts'] = $recentStmt->fetchAll();
        
        echo json_encode(['success' => true, 'stats' => $stats]);
    } catch (PDOException $e) {
        error_log("Blog admin dashboard stats error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error. Please ensure blog tables are created.']);
    }
}

// Require admin auth for API (returns JSON instead of redirect)
function apiRequireAuth() {
    if (!isAdminLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Authentication required', 'auth_required' => true]);
        exit;
    }
}
?>
