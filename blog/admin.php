<?php
require_once __DIR__ . '/blog-config.php';
requireAdminAuth();
$adminUser = $_SESSION[BLOG_ADMIN_SESSION_NAME];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Blog CMS Admin | Amity Online University</title>
    <link rel="icon" href="../assets/images/favicon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <img src="../assets/images/amity-logo.svg" alt="Amity" class="sidebar-logo">
            <h2>Blog CMS</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-item active" data-section="dashboard">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="#" class="nav-item" data-section="posts">
                <i class="fas fa-newspaper"></i> All Posts
            </a>
            <a href="#" class="nav-item" data-section="new-post">
                <i class="fas fa-plus-circle"></i> New Post
            </a>
            <a href="#" class="nav-item" data-section="categories">
                <i class="fas fa-folder"></i> Categories
            </a>
            <a href="#" class="nav-item" data-section="authors">
                <i class="fas fa-users"></i> Authors
            </a>
            <a href="#" class="nav-item" data-section="media">
                <i class="fas fa-images"></i> Media Library
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="/" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
            <a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main" id="adminMain">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-right">
                <span class="admin-name"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($adminUser['name']); ?></span>
            </div>
        </header>

        <!-- Dashboard Section -->
        <section class="admin-section active" id="section-dashboard">
            <div class="section-header">
                <h1>Dashboard</h1>
            </div>
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dbeafe;"><i class="fas fa-newspaper" style="color: #2563eb;"></i></div>
                    <div class="stat-info">
                        <span class="stat-number" id="statTotal">0</span>
                        <span class="stat-label">Total Posts</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #d1fae5;"><i class="fas fa-check-circle" style="color: #059669;"></i></div>
                    <div class="stat-info">
                        <span class="stat-number" id="statPublished">0</span>
                        <span class="stat-label">Published</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef3c7;"><i class="fas fa-pencil-alt" style="color: #d97706;"></i></div>
                    <div class="stat-info">
                        <span class="stat-number" id="statDrafts">0</span>
                        <span class="stat-label">Drafts</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ede9fe;"><i class="fas fa-eye" style="color: #7c3aed;"></i></div>
                    <div class="stat-info">
                        <span class="stat-number" id="statViews">0</span>
                        <span class="stat-label">Total Views</span>
                    </div>
                </div>
            </div>
            <div class="recent-posts-admin">
                <h2>Recent Posts</h2>
                <div id="recentPostsList"></div>
            </div>
        </section>

        <!-- All Posts Section -->
        <section class="admin-section" id="section-posts">
            <div class="section-header">
                <h1>All Posts</h1>
                <div class="section-actions">
                    <select id="filterStatus" class="admin-select">
                        <option value="">All Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="pending">Pending</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                    <button class="admin-btn primary" onclick="showSection('new-post')">
                        <i class="fas fa-plus"></i> New Post
                    </button>
                </div>
            </div>
            <div class="posts-table-wrap">
                <table class="admin-table" id="postsTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Author</th>
                            <th>Date</th>
                            <th>Views</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="postsTableBody"></tbody>
                </table>
            </div>
            <div class="pagination" id="postsPagination"></div>
        </section>

        <!-- New/Edit Post Section -->
        <section class="admin-section" id="section-new-post">
            <div class="section-header">
                <h1 id="postFormTitle">Create New Post</h1>
                <div class="section-actions">
                    <button class="admin-btn outline" onclick="saveDraft()">
                        <i class="fas fa-save"></i> Save Draft
                    </button>
                    <button class="admin-btn primary" onclick="publishPost()">
                        <i class="fas fa-paper-plane"></i> Publish
                    </button>
                </div>
            </div>
            
            <form id="postForm" class="post-editor-layout">
                <input type="hidden" id="postId" value="0">
                
                <!-- Left Column - Main Content -->
                <div class="editor-main">
                    <!-- SEO Meta Section -->
                    <div class="editor-card">
                        <div class="card-header">
                            <h3><i class="fas fa-search"></i> SEO Meta Data</h3>
                            <span class="card-toggle"><i class="fas fa-chevron-up"></i></span>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Meta Title <span class="char-count" id="metaTitleCount">0/60</span></label>
                                    <input type="text" id="metaTitle" maxlength="60" placeholder="SEO Meta Title (max 60 chars)">
                                    <small>Leave empty to auto-generate from blog title</small>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Meta Description <span class="char-count" id="metaDescCount">0/250</span></label>
                                    <textarea id="metaDescription" maxlength="250" rows="3" placeholder="SEO Meta Description (200-250 chars)"></textarea>
                                </div>
                            </div>
                            <div class="form-row two-col">
                                <div class="form-group">
                                    <label>Focus Keyword <span class="required">*</span></label>
                                    <input type="text" id="focusKeyword" required placeholder="Primary focus keyword">
                                </div>
                                <div class="form-group">
                                    <label>Primary Keyword</label>
                                    <input type="text" id="primaryKeyword" placeholder="Analytics primary keyword">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>URL Slug</label>
                                    <div class="slug-input">
                                        <span class="slug-prefix">/blog/</span>
                                        <input type="text" id="postSlug" placeholder="auto-generated-from-title">
                                    </div>
                                    <small>Auto-generated from title. Edit if needed.</small>
                                </div>
                            </div>
                            <!-- SEO Preview -->
                            <div class="seo-preview">
                                <h4>Google Search Preview</h4>
                                <div class="seo-preview-box">
                                    <div class="seo-preview-title" id="seoPreviewTitle">Blog Post Title</div>
                                    <div class="seo-preview-url" id="seoPreviewUrl">onlineamityuniversity.com/blog/post-slug</div>
                                    <div class="seo-preview-desc" id="seoPreviewDesc">Meta description will appear here...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Feature Image -->
                    <div class="editor-card">
                        <div class="card-header">
                            <h3><i class="fas fa-image"></i> Feature Image</h3>
                        </div>
                        <div class="card-body">
                            <div class="feature-image-upload" id="featureImageUpload">
                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Click to upload or drag & drop</p>
                                    <small>JPG, WebP - Recommended: 1200x628px</small>
                                </div>
                                <img id="featureImagePreview" src="" alt="" style="display:none;">
                                <button type="button" class="remove-image" id="removeFeatureImage" style="display:none;">
                                    <i class="fas fa-times"></i>
                                </button>
                                <input type="file" id="featureImageFile" accept="image/jpeg,image/jpg,image/webp,image/png" style="display:none;">
                                <input type="hidden" id="featureImageUrl" value="">
                            </div>
                            <div class="form-row two-col" style="margin-top: 12px;">
                                <div class="form-group">
                                    <label>Image Alt Text <span class="required">*</span></label>
                                    <input type="text" id="featureImageAlt" placeholder="Describe the image for accessibility">
                                </div>
                                <div class="form-group">
                                    <label>Image Title</label>
                                    <input type="text" id="featureImageTitle" placeholder="Optional image title">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Blog Title & Content -->
                    <div class="editor-card">
                        <div class="card-header">
                            <h3><i class="fas fa-pen-fancy"></i> Blog Content</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Blog Title (H1) <span class="required">*</span> <span class="char-count" id="titleCount">0/70</span></label>
                                <input type="text" id="postTitle" maxlength="70" required placeholder="Enter blog title (max 70 characters)" class="title-input">
                            </div>
                            <div class="form-group">
                                <label>Short Description / Excerpt <span class="required">*</span> <span class="char-count" id="excerptCount">0/250</span></label>
                                <textarea id="postExcerpt" maxlength="250" rows="3" required placeholder="Brief description for blog listing and meta"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Blog Content <span class="required">*</span></label>
                                <!-- Rich Text Editor Toolbar -->
                                <div class="editor-toolbar">
                                    <div class="toolbar-group">
                                        <select id="headingSelect" class="toolbar-select" title="Heading Level">
                                            <option value="">Paragraph</option>
                                            <option value="h2">H2</option>
                                            <option value="h3">H3</option>
                                            <option value="h4">H4</option>
                                        </select>
                                    </div>
                                    <div class="toolbar-group">
                                        <button type="button" class="toolbar-btn" data-command="bold" title="Bold (Ctrl+B)"><i class="fas fa-bold"></i></button>
                                        <button type="button" class="toolbar-btn" data-command="italic" title="Italic (Ctrl+I)"><i class="fas fa-italic"></i></button>
                                    </div>
                                    <div class="toolbar-group">
                                        <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List"><i class="fas fa-list-ul"></i></button>
                                        <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered List"><i class="fas fa-list-ol"></i></button>
                                    </div>
                                    <div class="toolbar-group">
                                        <button type="button" class="toolbar-btn" id="btnInsertLink" title="Insert Link"><i class="fas fa-link"></i></button>
                                        <button type="button" class="toolbar-btn" id="btnInsertImage" title="Insert Image"><i class="fas fa-image"></i></button>
                                        <button type="button" class="toolbar-btn" id="btnInsertTable" title="Insert Table"><i class="fas fa-table"></i></button>
                                    </div>
                                    <div class="toolbar-group">
                                        <button type="button" class="toolbar-btn" id="btnInsertFaq" title="Insert FAQ Block"><i class="fas fa-question-circle"></i></button>
                                    </div>
                                    <div class="toolbar-group">
                                        <button type="button" class="toolbar-btn" id="btnSourceCode" title="Toggle HTML Source"><i class="fas fa-code"></i></button>
                                    </div>
                                </div>
                                <!-- Content Editable Area -->
                                <div class="content-editor" id="contentEditor" contenteditable="true"></div>
                                <textarea id="contentSource" class="content-source" style="display:none;" placeholder="HTML source code..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Sidebar -->
                <div class="editor-sidebar">
                    <!-- Publish Settings -->
                    <div class="editor-card">
                        <div class="card-header">
                            <h3><i class="fas fa-cog"></i> Publish Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Status</label>
                                <select id="postStatus" class="admin-select">
                                    <option value="draft">Draft</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="published">Published</option>
                                    <option value="scheduled">Scheduled</option>
                                </select>
                            </div>
                            <div class="form-group" id="publishDateGroup">
                                <label>Publish Date</label>
                                <input type="date" id="publishDate">
                            </div>
                            <div class="form-group" id="publishTimeGroup">
                                <label>Publish Time</label>
                                <input type="time" id="publishTime" value="09:00">
                            </div>
                            <div class="form-group" id="scheduledDateGroup" style="display:none;">
                                <label>Scheduled Date & Time</label>
                                <input type="datetime-local" id="scheduledDate">
                            </div>
                        </div>
                    </div>

                    <!-- Author -->
                    <div class="editor-card">
                        <div class="card-header">
                            <h3><i class="fas fa-user"></i> Author</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <select id="postAuthor" class="admin-select">
                                    <option value="">Select Author</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="editor-card">
                        <div class="card-header">
                            <h3><i class="fas fa-folder"></i> Categories <span class="required">*</span></h3>
                        </div>
                        <div class="card-body">
                            <div class="categories-checklist" id="categoriesChecklist"></div>
                            <div class="add-new-inline">
                                <input type="text" id="newCategoryInput" placeholder="New category name">
                                <button type="button" onclick="addNewCategory()"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="editor-card">
                        <div class="card-header">
                            <h3><i class="fas fa-tags"></i> Tags</h3>
                        </div>
                        <div class="card-body">
                            <div class="tags-container" id="tagsContainer"></div>
                            <div class="add-new-inline">
                                <input type="text" id="newTagInput" placeholder="Add tag & press Enter">
                                <button type="button" onclick="addTag()"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Block -->
                    <div class="editor-card">
                        <div class="card-header">
                            <h3><i class="fas fa-bullhorn"></i> CTA Block</h3>
                            <span class="card-toggle"><i class="fas fa-chevron-up"></i></span>
                        </div>
                        <div class="card-body">
                            <div class="form-group" style="margin-bottom:12px;">
                                <button type="button" onclick="insertCtaShortcode()" style="width:100%;padding:8px 14px;background:#0a2e73;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:0.85rem;display:flex;align-items:center;justify-content:center;gap:6px;">
                                    <i class="fas fa-plus-circle"></i> Insert [cta] at Cursor in Content
                                </button>
                                <small style="display:block;margin-top:5px;color:#6b7280;font-size:0.78rem;">Inserts a CTA block inline wherever your cursor is in the editor. Configure the CTA fields below.</small>
                            </div>
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label class="checkbox-label" style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                    <input type="checkbox" id="ctaEnabled" checked>
                                    <span>Also show CTA in sidebar</span>
                                </label>
                            </div>
                            <div id="ctaFields">
                                <div class="form-group">
                                    <label>CTA Title</label>
                                    <input type="text" id="ctaTitle" placeholder="e.g. Start Your Journey" value="Start Your Journey">
                                </div>
                                <div class="form-group">
                                    <label>CTA Text</label>
                                    <textarea id="ctaText" rows="2" placeholder="Promotional message">Get 75% Scholarship on Online MBA, BBA, BCA, MCA Programs</textarea>
                                </div>
                                <div class="form-group">
                                    <label>Button Text</label>
                                    <input type="text" id="ctaButtonText" placeholder="Apply Now" value="Apply Now">
                                </div>
                                <div class="form-group">
                                    <label>Button URL</label>
                                    <input type="text" id="ctaButtonUrl" placeholder="/" value="/">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <!-- Categories Management Section -->
        <section class="admin-section" id="section-categories">
            <div class="section-header">
                <h1>Categories</h1>
            </div>
            <div class="categories-manager">
                <div class="category-form-card">
                    <h3 id="categoryFormTitle">Add New Category</h3>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="categoryName" placeholder="Category name">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="categoryDescription" rows="3" placeholder="Optional description"></textarea>
                    </div>
                    <input type="hidden" id="categoryId" value="0">
                    <button class="admin-btn primary" onclick="saveCategory()"><i class="fas fa-save"></i> Save Category</button>
                </div>
                <div class="categories-list-card">
                    <h3>All Categories</h3>
                    <div id="categoriesList"></div>
                </div>
            </div>
        </section>

        <!-- Authors Management Section -->
        <section class="admin-section" id="section-authors">
            <div class="section-header">
                <h1>Authors</h1>
            </div>
            <div class="categories-manager">
                <div class="category-form-card">
                    <h3 id="authorFormTitle">Add New Author</h3>
                    <div class="form-group">
                        <label>Name <span class="required">*</span></label>
                        <input type="text" id="authorName" placeholder="Author name">
                    </div>
                    <div class="form-group">
                        <label>Bio</label>
                        <textarea id="authorBio" rows="3" placeholder="Brief author bio"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Image URL</label>
                        <input type="text" id="authorImage" placeholder="Author image URL">
                    </div>
                    <div class="form-group">
                        <label>Author Page URL</label>
                        <input type="text" id="authorPageUrl" placeholder="Optional author page URL">
                    </div>
                    <input type="hidden" id="authorId" value="0">
                    <button class="admin-btn primary" onclick="saveAuthor()"><i class="fas fa-save"></i> Save Author</button>
                </div>
                <div class="categories-list-card">
                    <h3>All Authors</h3>
                    <div id="authorsList"></div>
                </div>
            </div>
        </section>

        <!-- Media Library Section -->
        <section class="admin-section" id="section-media">
            <div class="section-header">
                <h1>Media Library</h1>
                <div class="section-actions">
                    <button class="admin-btn primary" onclick="document.getElementById('mediaUploadFile').click()">
                        <i class="fas fa-upload"></i> Upload Image
                    </button>
                    <input type="file" id="mediaUploadFile" accept="image/*" style="display:none;">
                </div>
            </div>
            <div id="mediaUploadForm" style="display:none;" class="editor-card">
                <div class="card-body">
                    <div class="form-row two-col">
                        <div class="form-group">
                            <label>Alt Text <span class="required">*</span></label>
                            <input type="text" id="mediaAltText" placeholder="Image alt text (required)">
                        </div>
                        <div class="form-group">
                            <label>Caption</label>
                            <input type="text" id="mediaCaption" placeholder="Optional caption">
                        </div>
                    </div>
                    <button class="admin-btn primary" onclick="uploadMediaImage()"><i class="fas fa-upload"></i> Upload</button>
                    <button class="admin-btn outline" onclick="cancelMediaUpload()">Cancel</button>
                </div>
            </div>
            <div class="media-grid" id="mediaGrid">
                <p class="empty-state"><i class="fas fa-images"></i> Media library will show uploaded blog images</p>
            </div>
        </section>
    </main>

    <!-- Link Insert Modal -->
    <div class="modal-overlay" id="linkModal" style="display:none;">
        <div class="modal-content small">
            <div class="modal-header">
                <h3>Insert Link</h3>
                <button class="modal-close" onclick="closeLinkModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>URL</label>
                    <input type="url" id="linkUrl" placeholder="https://example.com">
                </div>
                <div class="form-group">
                    <label>Link Text</label>
                    <input type="text" id="linkText" placeholder="Display text">
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="linkNewTab" checked> Open in new tab
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="admin-btn primary" onclick="insertLink()">Insert Link</button>
            </div>
        </div>
    </div>

    <!-- Image Insert Modal -->
    <div class="modal-overlay" id="imageModal" style="display:none;">
        <div class="modal-content small">
            <div class="modal-header">
                <h3>Insert Image</h3>
                <button class="modal-close" onclick="closeImageModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Upload Image</label>
                    <input type="file" id="contentImageFile" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Or paste Image URL</label>
                    <input type="url" id="contentImageUrl" placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group">
                    <label>Alt Text <span class="required">*</span></label>
                    <input type="text" id="contentImageAlt" placeholder="Image alt text (required)">
                </div>
                <div class="form-group">
                    <label>Caption</label>
                    <input type="text" id="contentImageCaption" placeholder="Optional image caption">
                </div>
                <div class="form-group">
                    <label>Image Title</label>
                    <input type="text" id="contentImageTitle" placeholder="Optional image title">
                </div>
            </div>
            <div class="modal-footer">
                <button class="admin-btn primary" onclick="insertImage()">Insert Image</button>
            </div>
        </div>
    </div>

    <!-- Table Insert Modal -->
    <div class="modal-overlay" id="tableModal" style="display:none;">
        <div class="modal-content small">
            <div class="modal-header">
                <h3>Insert Table</h3>
                <button class="modal-close" onclick="closeTableModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-row two-col">
                    <div class="form-group">
                        <label>Rows</label>
                        <input type="number" id="tableRows" value="3" min="1" max="20">
                    </div>
                    <div class="form-group">
                        <label>Columns</label>
                        <input type="number" id="tableCols" value="3" min="1" max="10">
                    </div>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="tableHeader" checked> Include header row
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="admin-btn primary" onclick="insertTable()">Insert Table</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="admin-script.js"></script>
</body>
</html>
