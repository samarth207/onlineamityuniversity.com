-- ================================================
-- BLOG CMS DATABASE SCHEMA
-- Amity Online University Blog System
-- ================================================

-- Blog Categories Table
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_category_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Tags Table
CREATE TABLE IF NOT EXISTS `blog_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_tag_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Authors Table
CREATE TABLE IF NOT EXISTS `blog_authors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `author_page_url` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Posts Table (Main)
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(250) NOT NULL,
  `meta_title` varchar(70) DEFAULT NULL,
  `meta_description` varchar(300) DEFAULT NULL,
  `focus_keyword` varchar(100) NOT NULL,
  `primary_keyword` varchar(100) DEFAULT NULL,
  `excerpt` varchar(300) NOT NULL,
  `content` longtext NOT NULL,
  `feature_image` varchar(500) NOT NULL,
  `feature_image_alt` varchar(250) NOT NULL,
  `feature_image_title` varchar(250) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('draft','pending','published','scheduled') NOT NULL DEFAULT 'draft',
  `publish_date` datetime DEFAULT NULL,
  `scheduled_date` datetime DEFAULT NULL,
  `cta_data` text DEFAULT NULL COMMENT 'JSON: {enabled, title, text, button_text, button_url}',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `views` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_post_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_publish_date` (`publish_date`),
  KEY `idx_author` (`author_id`),
  KEY `idx_focus_keyword` (`focus_keyword`),
  CONSTRAINT `fk_post_author` FOREIGN KEY (`author_id`) REFERENCES `blog_authors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Post Categories (Many-to-Many)
CREATE TABLE IF NOT EXISTS `blog_post_categories` (
  `post_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`post_id`, `category_id`),
  CONSTRAINT `fk_pc_post` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pc_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Post Tags (Many-to-Many)
CREATE TABLE IF NOT EXISTS `blog_post_tags` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`post_id`, `tag_id`),
  CONSTRAINT `fk_pt_post` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pt_tag` FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Content Images Table
CREATE TABLE IF NOT EXISTS `blog_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) DEFAULT NULL,
  `filename` varchar(500) NOT NULL,
  `alt_text` varchar(250) NOT NULL,
  `caption` varchar(500) DEFAULT NULL,
  `image_title` varchar(250) DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_post_images` (`post_id`),
  CONSTRAINT `fk_image_post` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO `blog_categories` (`name`, `slug`) VALUES
('Online MBA', 'online-mba'),
('Online BBA', 'online-bba'),
('Online BCA', 'online-bca'),
('Online MCA', 'online-mca'),
('Career Guidance', 'career-guidance'),
('Scholarships', 'scholarships'),
('Admission Updates', 'admission-updates'),
('Student Life', 'student-life'),
('Industry Insights', 'industry-insights'),
('Exam Preparation', 'exam-preparation');

-- Insert default author
INSERT INTO `blog_authors` (`name`, `bio`) VALUES
('Amity Online Team', 'The official content team at Amity Online University, bringing you the latest insights on online education, career development, and academic excellence.');

-- Admin users table for blog CMS
CREATE TABLE IF NOT EXISTS `blog_admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add cta_data column to existing databases (safe to run even if table already exists)
ALTER TABLE `blog_posts` ADD COLUMN IF NOT EXISTS `cta_data` text DEFAULT NULL COMMENT 'JSON: {enabled, title, text, button_text, button_url}' AFTER `scheduled_date`;
