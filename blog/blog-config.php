<?php
/**
 * Blog Configuration & Helper Functions
 * Amity Online University Blog CMS
 */

require_once __DIR__ . '/../config.php';

// Blog-specific constants
define('BLOG_UPLOAD_DIR', __DIR__ . '/../assets/images/blog/');
define('BLOG_UPLOAD_URL', 'assets/images/blog/');
define('BLOG_POSTS_PER_PAGE', 12);
define('BLOG_ADMIN_SESSION_NAME', 'blog_admin_session');

// Ensure upload directory exists
if (!file_exists(BLOG_UPLOAD_DIR)) {
    mkdir(BLOG_UPLOAD_DIR, 0755, true);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION[BLOG_ADMIN_SESSION_NAME]) && !empty($_SESSION[BLOG_ADMIN_SESSION_NAME]);
}

/**
 * Require admin authentication
 */
function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Generate URL slug from text
 */
function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

/**
 * Upload image with validation
 */
function uploadBlogImage($file, $subfolder = '') {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error: ' . $file['error']];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WebP are allowed.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds 5MB limit.'];
    }
    
    $uploadDir = BLOG_UPLOAD_DIR;
    if ($subfolder) {
        $uploadDir .= $subfolder . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('blog_') . '_' . time() . '.' . strtolower($ext);
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $url = BLOG_UPLOAD_URL . ($subfolder ? $subfolder . '/' : '') . $filename;
        return ['success' => true, 'filename' => $filename, 'url' => $url];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file.'];
}

/**
 * Sanitize HTML content (allow safe tags for blog content)
 */
function sanitizeBlogContent($html) {
    $allowed = '<h2><h3><h4><p><br><strong><b><em><i><ul><ol><li><a><img><table><thead><tbody><tr><th><td><blockquote><figure><figcaption><div><span><sup><sub>';
    return strip_tags($html, $allowed);
}

/**
 * Get reading time estimate
 */
function getReadingTime($content) {
    $wordCount = str_word_count(strip_tags($content));
    $minutes = ceil($wordCount / 200);
    return max(1, $minutes);
}

/**
 * Extract headings from HTML content for Table of Contents
 */
function extractHeadings($content) {
    $headings = [];
    preg_match_all('/<(h[2-4])[^>]*id=["\']([^"\']*)["\'][^>]*>(.*?)<\/\1>/si', $content, $matches, PREG_SET_ORDER);
    
    if (empty($matches)) {
        // If no IDs, find headings and we'll add IDs
        preg_match_all('/<(h[2-4])[^>]*>(.*?)<\/\1>/si', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $text = strip_tags($match[2]);
            $id = generateSlug($text);
            $headings[] = [
                'tag' => $match[1],
                'id' => $id,
                'text' => $text,
                'level' => (int) substr($match[1], 1)
            ];
        }
    } else {
        foreach ($matches as $match) {
            $headings[] = [
                'tag' => $match[1],
                'id' => $match[2],
                'text' => strip_tags($match[3]),
                'level' => (int) substr($match[1], 1)
            ];
        }
    }
    
    return $headings;
}

/**
 * Add IDs to headings in content for TOC linking
 */
function addHeadingIds($content) {
    $usedIds = [];
    $content = preg_replace_callback('/<(h[2-4])([^>]*)>(.*?)<\/\1>/si', function($match) use (&$usedIds) {
        $tag = $match[1];
        $attrs = $match[2];
        $text = strip_tags($match[3]);
        
        // Check if already has an ID
        if (preg_match('/id=["\']([^"\']*)["\']/', $attrs)) {
            return $match[0];
        }
        
        $id = generateSlug($text);
        // Ensure unique ID
        if (isset($usedIds[$id])) {
            $usedIds[$id]++;
            $id .= '-' . $usedIds[$id];
        } else {
            $usedIds[$id] = 0;
        }
        
        return "<{$tag}{$attrs} id=\"{$id}\">{$match[3]}</{$tag}>";
    }, $content);
    
    return $content;
}

/**
 * Format date for display
 */
function formatBlogDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Truncate text to specified length
 */
function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}
?>
