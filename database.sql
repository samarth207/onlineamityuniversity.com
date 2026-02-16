-- SQL script to create the form submissions table
-- Run this in your Hostinger phpMyAdmin or MySQL interface

CREATE TABLE IF NOT EXISTS `form_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_type` enum('apply','enquire','brochure') NOT NULL,
  `course` varchar(50) NOT NULL DEFAULT 'General',
  `phone` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `submitted_at` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_form_type` (`form_type`),
  KEY `idx_course` (`course`),
  KEY `idx_email` (`email`),
  KEY `idx_submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Create a view for easy data analysis
CREATE OR REPLACE VIEW `form_submissions_summary` AS
SELECT 
    form_type,
    course,
    COUNT(*) as total_submissions,
    DATE(submitted_at) as submission_date
FROM form_submissions
GROUP BY form_type, course, DATE(submitted_at)
ORDER BY submission_date DESC, total_submissions DESC;
