/**
 * Blog CMS Admin Panel JavaScript
 * Amity Online University
 */

const API_BASE = 'blog-api.php';
let currentTags = [];
let currentEditId = 0;
let isSourceMode = false;

// =====================================================
// INITIALIZATION
// =====================================================

document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
    loadAuthorsDropdown();
    loadCategoriesChecklist();
    setupEventListeners();
    setupEditor();
    setupCharCounters();
    setupSlugGeneration();
    setupSEOPreview();
    setupFeatureImageUpload();
    
    // Set default publish date to today
    document.getElementById('publishDate').valueAsDate = new Date();
});

function setupEventListeners() {
    // Sidebar navigation
    document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            showSection(item.dataset.section);
        });
    });
    
    // Sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', () => {
        document.getElementById('adminSidebar').classList.toggle('open');
    });
    
    // Logout
    document.getElementById('logoutBtn').addEventListener('click', async (e) => {
        e.preventDefault();
        await apiCall('admin_logout');
        window.location.href = 'login.php';
    });
    
    // Filter posts by status
    document.getElementById('filterStatus').addEventListener('change', () => {
        loadPosts();
    });
    
    // Post status change
    document.getElementById('postStatus').addEventListener('change', function() {
        const scheduledGroup = document.getElementById('scheduledDateGroup');
        const publishGroup = document.getElementById('publishDateGroup');
        const timeGroup = document.getElementById('publishTimeGroup');
        if (this.value === 'scheduled') {
            scheduledGroup.style.display = 'block';
            publishGroup.style.display = 'none';
            timeGroup.style.display = 'none';
        } else {
            scheduledGroup.style.display = 'none';
            publishGroup.style.display = 'block';
            timeGroup.style.display = 'block';
        }
    });
    
    // Tag input enter key
    document.getElementById('newTagInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTag();
        }
    });
    
    // Media upload
    document.getElementById('mediaUploadFile').addEventListener('change', function() {
        if (this.files.length > 0) {
            document.getElementById('mediaUploadForm').style.display = 'block';
        }
    });
}

// =====================================================
// SECTION NAVIGATION
// =====================================================

function showSection(section) {
    // Update nav
    document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
        item.classList.toggle('active', item.dataset.section === section);
    });
    
    // Update sections
    document.querySelectorAll('.admin-section').forEach(sec => {
        sec.classList.remove('active');
    });
    document.getElementById('section-' + section).classList.add('active');
    
    // Load section data
    switch(section) {
        case 'dashboard': loadDashboard(); break;
        case 'posts': loadPosts(); break;
        case 'new-post': 
            if (!currentEditId) resetPostForm(); 
            break;
        case 'categories': loadCategoriesManager(); break;
        case 'authors': loadAuthorsManager(); break;
    }
    
    // Close sidebar on mobile
    document.getElementById('adminSidebar').classList.remove('open');
}

// =====================================================
// API HELPER
// =====================================================

async function apiCall(action, method = 'GET', data = null, params = '') {
    const url = `${API_BASE}?action=${action}${params}`;
    const options = { method };
    
    if (data && method !== 'GET') {
        if (data instanceof FormData) {
            options.body = data;
        } else {
            options.headers = { 'Content-Type': 'application/json' };
            options.body = JSON.stringify(data);
        }
    }
    
    try {
        const resp = await fetch(url, options);
        const result = await resp.json();
        
        if (result.auth_required) {
            window.location.href = 'login.php';
            return null;
        }
        
        return result;
    } catch (err) {
        console.error('API Error:', err);
        showToast('Connection error. Please try again.', 'error');
        return null;
    }
}

// =====================================================
// DASHBOARD
// =====================================================

async function loadDashboard() {
    const data = await apiCall('admin_dashboard_stats');
    if (!data || !data.success) return;
    
    const stats = data.stats;
    document.getElementById('statTotal').textContent = stats.total_posts;
    document.getElementById('statPublished').textContent = stats.published;
    document.getElementById('statDrafts').textContent = stats.drafts;
    document.getElementById('statViews').textContent = stats.total_views;
    
    const recentList = document.getElementById('recentPostsList');
    if (stats.recent_posts.length === 0) {
        recentList.innerHTML = '<p class="empty-state">No posts yet. Create your first blog post!</p>';
    } else {
        recentList.innerHTML = stats.recent_posts.map(post => `
            <div class="recent-post-item">
                <div>
                    <h4>${escHtml(post.title)}</h4>
                    <span><span class="status-badge ${post.status}">${post.status}</span> &bull; ${post.updated_at || 'N/A'}</span>
                </div>
                <div>
                    <span>${post.views || 0} views</span>
                </div>
            </div>
        `).join('');
    }
}

// =====================================================
// POSTS LIST
// =====================================================

async function loadPosts(page = 1) {
    const status = document.getElementById('filterStatus').value;
    const params = `&page=${page}${status ? '&status=' + status : ''}`;
    const data = await apiCall('admin_get_posts', 'GET', null, params);
    if (!data || !data.success) return;
    
    const tbody = document.getElementById('postsTableBody');
    
    if (data.posts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px;">No posts found</td></tr>';
    } else {
        tbody.innerHTML = data.posts.map(post => `
            <tr>
                <td><strong>${escHtml(post.title)}</strong><br><small style="color:#64748b;">/blog/${post.slug}</small></td>
                <td><span class="status-badge ${post.status}">${post.status}</span></td>
                <td>${post.author_name || 'N/A'}</td>
                <td>${post.publish_date || post.updated_at || 'N/A'}</td>
                <td>${post.views || 0}</td>
                <td>
                    <div class="action-btns">
                        <button class="edit-btn" onclick="editPost(${post.id})" title="Edit"><i class="fas fa-edit"></i></button>
                        ${post.status === 'published' ? `<button class="view-btn" onclick="window.open('/blog/${post.slug}','_blank')" title="View"><i class="fas fa-eye"></i></button>` : ''}
                        <button class="delete-btn" onclick="deletePost(${post.id}, '${escHtml(post.title).replace(/'/g, "\\'")}' )" title="Delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    // Pagination
    const pagDiv = document.getElementById('postsPagination');
    if (data.pages > 1) {
        let pagHtml = '';
        for (let i = 1; i <= data.pages; i++) {
            pagHtml += `<button class="${i === data.current_page ? 'active' : ''}" onclick="loadPosts(${i})">${i}</button>`;
        }
        pagDiv.innerHTML = pagHtml;
    } else {
        pagDiv.innerHTML = '';
    }
}

async function editPost(id) {
    currentEditId = id;
    const data = await apiCall('admin_get_post', 'GET', null, `&id=${id}`);
    if (!data || !data.success) return;
    
    const post = data.post;
    
    document.getElementById('postFormTitle').textContent = 'Edit Post';
    document.getElementById('postId').value = post.id;
    document.getElementById('metaTitle').value = post.meta_title || '';
    document.getElementById('metaDescription').value = post.meta_description || '';
    document.getElementById('focusKeyword').value = post.focus_keyword || '';
    document.getElementById('primaryKeyword').value = post.primary_keyword || '';
    document.getElementById('postSlug').value = post.slug || '';
    document.getElementById('postTitle').value = post.title || '';
    document.getElementById('postExcerpt').value = post.excerpt || '';
    document.getElementById('contentEditor').innerHTML = post.content || '';
    document.getElementById('postStatus').value = post.status || 'draft';
    document.getElementById('postAuthor').value = post.author_id || '';
    
    // Feature image
    if (post.feature_image) {
        document.getElementById('featureImageUrl').value = post.feature_image;
        document.getElementById('featureImagePreview').src = '../' + post.feature_image;
        document.getElementById('featureImagePreview').style.display = 'block';
        document.getElementById('uploadPlaceholder').style.display = 'none';
        document.getElementById('removeFeatureImage').style.display = 'flex';
        document.getElementById('featureImageUpload').classList.add('has-image');
    }
    document.getElementById('featureImageAlt').value = post.feature_image_alt || '';
    document.getElementById('featureImageTitle').value = post.feature_image_title || '';
    
    // Publish date
    if (post.publish_date) {
        const dt = new Date(post.publish_date);
        document.getElementById('publishDate').value = dt.toISOString().split('T')[0];
        document.getElementById('publishTime').value = dt.toTimeString().slice(0, 5);
    }
    
    if (post.scheduled_date) {
        document.getElementById('scheduledDate').value = post.scheduled_date.replace(' ', 'T').slice(0, 16);
    }
    
    // Categories
    document.querySelectorAll('#categoriesChecklist input[type="checkbox"]').forEach(cb => {
        cb.checked = post.category_ids.includes(parseInt(cb.value));
    });
    
    // Tags
    currentTags = (post.tags || []).map(t => t.name);
    renderTags();
    
    // CTA
    const ctaEnabled = document.getElementById('ctaEnabled');
    if (post.cta_data) {
        const cta = typeof post.cta_data === 'string' ? JSON.parse(post.cta_data) : post.cta_data;
        ctaEnabled.checked = cta.enabled !== false;
        document.getElementById('ctaTitle').value = cta.title || 'Start Your Journey';
        document.getElementById('ctaText').value = cta.text || 'Get 75% Scholarship on Online MBA, BBA, BCA, MCA Programs';
        document.getElementById('ctaButtonText').value = cta.button_text || 'Apply Now';
        document.getElementById('ctaButtonUrl').value = cta.button_url || '/';
    } else {
        ctaEnabled.checked = true;
        document.getElementById('ctaTitle').value = 'Start Your Journey';
        document.getElementById('ctaText').value = 'Get 75% Scholarship on Online MBA, BBA, BCA, MCA Programs';
        document.getElementById('ctaButtonText').value = 'Apply Now';
        document.getElementById('ctaButtonUrl').value = '/';
    }
    toggleCtaFields(ctaEnabled.checked);
    
    // Trigger counters
    updateCharCounters();
    updateSEOPreview();
    
    // Trigger status change
    document.getElementById('postStatus').dispatchEvent(new Event('change'));
    
    showSection('new-post');
}

async function deletePost(id, title) {
    if (!confirm(`Delete post "${title}"? This cannot be undone.`)) return;
    
    const data = await apiCall('admin_delete_post', 'POST', { id });
    if (data && data.success) {
        showToast('Post deleted successfully', 'success');
        loadPosts();
    } else {
        showToast(data?.message || 'Error deleting post', 'error');
    }
}

// =====================================================
// POST FORM
// =====================================================

function resetPostForm() {
    currentEditId = 0;
    document.getElementById('postFormTitle').textContent = 'Create New Post';
    document.getElementById('postId').value = '0';
    document.getElementById('metaTitle').value = '';
    document.getElementById('metaDescription').value = '';
    document.getElementById('focusKeyword').value = '';
    document.getElementById('primaryKeyword').value = '';
    document.getElementById('postSlug').value = '';
    document.getElementById('postTitle').value = '';
    document.getElementById('postExcerpt').value = '';
    document.getElementById('contentEditor').innerHTML = '';
    document.getElementById('contentSource').value = '';
    document.getElementById('postStatus').value = 'draft';
    document.getElementById('publishDate').valueAsDate = new Date();
    document.getElementById('publishTime').value = '09:00';
    document.getElementById('postAuthor').value = '';
    
    // Reset image
    document.getElementById('featureImageUrl').value = '';
    document.getElementById('featureImagePreview').style.display = 'none';
    document.getElementById('uploadPlaceholder').style.display = 'block';
    document.getElementById('removeFeatureImage').style.display = 'none';
    document.getElementById('featureImageUpload').classList.remove('has-image');
    document.getElementById('featureImageAlt').value = '';
    document.getElementById('featureImageTitle').value = '';
    
    // Reset categories
    document.querySelectorAll('#categoriesChecklist input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
    });
    
    // Reset tags
    currentTags = [];
    renderTags();
    
    // Reset CTA
    document.getElementById('ctaEnabled').checked = true;
    document.getElementById('ctaTitle').value = 'Start Your Journey';
    document.getElementById('ctaText').value = 'Get 75% Scholarship on Online MBA, BBA, BCA, MCA Programs';
    document.getElementById('ctaButtonText').value = 'Apply Now';
    document.getElementById('ctaButtonUrl').value = '/';
    toggleCtaFields(true);
    
    updateCharCounters();
    updateSEOPreview();
}

function insertCtaShortcode() {
    const editor = document.getElementById('contentEditor');
    if (!editor) return;
    editor.focus();
    const selection = window.getSelection();
    if (selection && selection.rangeCount > 0 && editor.contains(selection.getRangeAt(0).commonAncestorContainer)) {
        const range = selection.getRangeAt(0);
        range.deleteContents();
        const textNode = document.createTextNode('[cta]');
        range.insertNode(textNode);
        range.setStartAfter(textNode);
        range.setEndAfter(textNode);
        selection.removeAllRanges();
        selection.addRange(range);
    } else {
        // Cursor not in editor — append at end
        editor.focus();
        const range = document.createRange();
        range.selectNodeContents(editor);
        range.collapse(false);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        document.execCommand('insertText', false, '[cta]');
    }
    editor.dispatchEvent(new Event('input'));
}

function toggleCtaFields(enabled) {
    const fields = document.getElementById('ctaFields');
    if (fields) fields.style.display = enabled ? 'block' : 'none';
}

function collectPostData(status) {
    const content = isSourceMode
        ? document.getElementById('contentSource').value
        : document.getElementById('contentEditor').innerHTML;
    
    let publishDatetime = null;
    if (status === 'published') {
        const date = document.getElementById('publishDate').value;
        const time = document.getElementById('publishTime').value;
        publishDatetime = date + ' ' + time + ':00';
    }
    
    const categoryIds = [];
    document.querySelectorAll('#categoriesChecklist input[type="checkbox"]:checked').forEach(cb => {
        categoryIds.push(parseInt(cb.value));
    });
    
    return {
        id: parseInt(document.getElementById('postId').value) || 0,
        title: document.getElementById('postTitle').value.trim(),
        slug: document.getElementById('postSlug').value.trim(),
        meta_title: document.getElementById('metaTitle').value.trim(),
        meta_description: document.getElementById('metaDescription').value.trim(),
        focus_keyword: document.getElementById('focusKeyword').value.trim(),
        primary_keyword: document.getElementById('primaryKeyword').value.trim(),
        excerpt: document.getElementById('postExcerpt').value.trim(),
        content: content,
        feature_image: document.getElementById('featureImageUrl').value,
        feature_image_alt: document.getElementById('featureImageAlt').value.trim(),
        feature_image_title: document.getElementById('featureImageTitle').value.trim(),
        author_id: parseInt(document.getElementById('postAuthor').value) || null,
        status: status,
        publish_date: publishDatetime,
        scheduled_date: status === 'scheduled' ? document.getElementById('scheduledDate').value.replace('T', ' ') + ':00' : null,
        category_ids: categoryIds,
        tags: currentTags,
        cta_data: {
            enabled: document.getElementById('ctaEnabled').checked,
            title: document.getElementById('ctaTitle').value.trim(),
            text: document.getElementById('ctaText').value.trim(),
            button_text: document.getElementById('ctaButtonText').value.trim(),
            button_url: document.getElementById('ctaButtonUrl').value.trim()
        }
    };
}

function validatePostData(data) {
    if (!data.title) { showToast('Blog title is required', 'error'); return false; }
    if (!data.focus_keyword) { showToast('Focus keyword is required', 'error'); return false; }
    if (!data.excerpt) { showToast('Excerpt / short description is required', 'error'); return false; }
    if (!data.content || data.content === '<br>' || data.content.trim() === '') { 
        showToast('Blog content is required', 'error'); return false; 
    }
    if (data.status === 'published' && !data.feature_image) {
        showToast('Feature image is required for publishing', 'error'); return false;
    }
    if (data.status === 'published' && !data.feature_image_alt) {
        showToast('Feature image alt text is required', 'error'); return false;
    }
    if (data.status === 'published' && data.category_ids.length === 0) {
        showToast('Please select at least one category', 'error'); return false;
    }
    return true;
}

async function saveDraft() {
    const data = collectPostData('draft');
    if (!data.title) { showToast('At least a title is required to save draft', 'error'); return; }
    
    const result = await apiCall('admin_save_post', 'POST', data);
    if (result && result.success) {
        document.getElementById('postId').value = result.id;
        document.getElementById('postSlug').value = result.slug;
        currentEditId = result.id;
        showToast('Draft saved successfully!', 'success');
    } else {
        showToast(result?.message || 'Error saving draft', 'error');
    }
}

async function publishPost() {
    const status = document.getElementById('postStatus').value;
    const data = collectPostData(status);
    if (!validatePostData(data)) return;
    
    const result = await apiCall('admin_save_post', 'POST', data);
    if (result && result.success) {
        document.getElementById('postId').value = result.id;
        document.getElementById('postSlug').value = result.slug;
        currentEditId = result.id;
        showToast(`Post ${status === 'published' ? 'published' : 'saved'} successfully!`, 'success');
    } else {
        showToast(result?.message || 'Error saving post', 'error');
    }
}

// =====================================================
// RICH TEXT EDITOR
// =====================================================

function setupEditor() {
    // Toolbar buttons
    document.querySelectorAll('.toolbar-btn[data-command]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.execCommand(btn.dataset.command, false, null);
            document.getElementById('contentEditor').focus();
        });
    });
    
    // Heading select
    document.getElementById('headingSelect').addEventListener('change', function() {
        if (this.value) {
            document.execCommand('formatBlock', false, this.value);
        } else {
            document.execCommand('formatBlock', false, 'p');
        }
        document.getElementById('contentEditor').focus();
        this.value = '';
    });
    
    // Link button
    document.getElementById('btnInsertLink').addEventListener('click', () => {
        const selection = window.getSelection();
        if (selection.toString()) {
            document.getElementById('linkText').value = selection.toString();
        }
        document.getElementById('linkModal').style.display = 'flex';
    });
    
    // Image button
    document.getElementById('btnInsertImage').addEventListener('click', () => {
        document.getElementById('imageModal').style.display = 'flex';
    });
    
    // Table button
    document.getElementById('btnInsertTable').addEventListener('click', () => {
        document.getElementById('tableModal').style.display = 'flex';
    });
    
    // FAQ button
    document.getElementById('btnInsertFaq').addEventListener('click', insertFaqBlock);
    
    // Source code toggle
    document.getElementById('btnSourceCode').addEventListener('click', toggleSourceMode);
}

function toggleSourceMode() {
    const editor = document.getElementById('contentEditor');
    const source = document.getElementById('contentSource');
    const btn = document.getElementById('btnSourceCode');
    
    if (isSourceMode) {
        editor.innerHTML = source.value;
        editor.style.display = 'block';
        source.style.display = 'none';
        btn.classList.remove('active');
    } else {
        source.value = editor.innerHTML;
        editor.style.display = 'none';
        source.style.display = 'block';
        btn.classList.add('active');
    }
    isSourceMode = !isSourceMode;
}

// Link Modal
function closeLinkModal() {
    document.getElementById('linkModal').style.display = 'none';
    document.getElementById('linkUrl').value = '';
    document.getElementById('linkText').value = '';
}

function insertLink() {
    const url = document.getElementById('linkUrl').value.trim();
    const text = document.getElementById('linkText').value.trim() || url;
    const newTab = document.getElementById('linkNewTab').checked;
    
    if (!url) { showToast('URL is required', 'error'); return; }
    
    const target = newTab ? ' target="_blank" rel="noopener noreferrer"' : '';
    const html = `<a href="${url}"${target}>${escHtml(text)}</a>`;
    
    document.getElementById('contentEditor').focus();
    document.execCommand('insertHTML', false, html);
    closeLinkModal();
}

// Image Modal
function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
    document.getElementById('contentImageFile').value = '';
    document.getElementById('contentImageUrl').value = '';
    document.getElementById('contentImageAlt').value = '';
    document.getElementById('contentImageCaption').value = '';
    document.getElementById('contentImageTitle').value = '';
}

async function insertImage() {
    const altText = document.getElementById('contentImageAlt').value.trim();
    if (!altText) { showToast('Alt text is required for all images', 'error'); return; }
    
    let imageUrl = document.getElementById('contentImageUrl').value.trim();
    const file = document.getElementById('contentImageFile').files[0];
    const caption = document.getElementById('contentImageCaption').value.trim();
    const imageTitle = document.getElementById('contentImageTitle').value.trim();
    
    // Upload file if provided
    if (file) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('alt_text', altText);
        formData.append('caption', caption);
        formData.append('image_title', imageTitle);
        formData.append('subfolder', 'content');
        
        const postId = document.getElementById('postId').value;
        if (postId && postId !== '0') {
            formData.append('post_id', postId);
        }
        
        const result = await apiCall('admin_upload_image', 'POST', formData);
        if (!result || !result.success) {
            showToast(result?.message || 'Failed to upload image', 'error');
            return;
        }
        imageUrl = '../' + result.url;
    }
    
    if (!imageUrl) { showToast('Please provide an image file or URL', 'error'); return; }
    
    const titleAttr = imageTitle ? ` title="${escHtml(imageTitle)}"` : '';
    let html = '';
    if (caption) {
        html = `<figure><img src="${imageUrl}" alt="${escHtml(altText)}"${titleAttr} style="max-width:100%;"><figcaption>${escHtml(caption)}</figcaption></figure>`;
    } else {
        html = `<img src="${imageUrl}" alt="${escHtml(altText)}"${titleAttr} style="max-width:100%;">`;
    }
    
    document.getElementById('contentEditor').focus();
    document.execCommand('insertHTML', false, html);
    closeImageModal();
}

// Table Modal
function closeTableModal() {
    document.getElementById('tableModal').style.display = 'none';
}

function insertTable() {
    const rows = parseInt(document.getElementById('tableRows').value) || 3;
    const cols = parseInt(document.getElementById('tableCols').value) || 3;
    const hasHeader = document.getElementById('tableHeader').checked;
    
    let html = '<table>';
    if (hasHeader) {
        html += '<thead><tr>';
        for (let c = 0; c < cols; c++) html += `<th>Header ${c + 1}</th>`;
        html += '</tr></thead>';
    }
    html += '<tbody>';
    const dataRows = hasHeader ? rows - 1 : rows;
    for (let r = 0; r < dataRows; r++) {
        html += '<tr>';
        for (let c = 0; c < cols; c++) html += '<td>&nbsp;</td>';
        html += '</tr>';
    }
    html += '</tbody></table><p><br></p>';
    
    document.getElementById('contentEditor').focus();
    document.execCommand('insertHTML', false, html);
    closeTableModal();
}

// FAQ Block
function insertFaqBlock() {
    const html = `
        <div class="faq-block">
            <h3>Frequently Asked Questions</h3>
            <div class="faq-item">
                <h4>Question 1?</h4>
                <p>Answer to question 1.</p>
            </div>
            <div class="faq-item">
                <h4>Question 2?</h4>
                <p>Answer to question 2.</p>
            </div>
            <div class="faq-item">
                <h4>Question 3?</h4>
                <p>Answer to question 3.</p>
            </div>
        </div>
        <p><br></p>
    `;
    document.getElementById('contentEditor').focus();
    document.execCommand('insertHTML', false, html);
}

// =====================================================
// CHARACTER COUNTERS & SLUG
// =====================================================

function setupCharCounters() {
    bindCounter('metaTitle', 'metaTitleCount', 60);
    bindCounter('metaDescription', 'metaDescCount', 250);
    bindCounter('postTitle', 'titleCount', 70);
    bindCounter('postExcerpt', 'excerptCount', 250);
}

function bindCounter(inputId, countId, max) {
    const input = document.getElementById(inputId);
    const count = document.getElementById(countId);
    input.addEventListener('input', () => {
        const len = input.value.length;
        count.textContent = `${len}/${max}`;
        count.style.color = len > max * 0.9 ? '#dc2626' : '#64748b';
    });
}

function updateCharCounters() {
    ['metaTitle', 'metaDescription', 'postTitle', 'postExcerpt'].forEach(id => {
        document.getElementById(id).dispatchEvent(new Event('input'));
    });
}

function setupSlugGeneration() {
    document.getElementById('postTitle').addEventListener('input', function() {
        const slug = this.value.toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-|-$/g, '');
        document.getElementById('postSlug').value = slug;
        updateSEOPreview();
    });
}

function setupSEOPreview() {
    ['metaTitle', 'metaDescription', 'postTitle', 'postSlug', 'postExcerpt'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateSEOPreview);
    });
}

function updateSEOPreview() {
    const metaTitle = document.getElementById('metaTitle').value || document.getElementById('postTitle').value || 'Blog Post Title';
    const slug = document.getElementById('postSlug').value || 'post-slug';
    const metaDesc = document.getElementById('metaDescription').value || document.getElementById('postExcerpt').value || 'Meta description will appear here...';
    
    document.getElementById('seoPreviewTitle').textContent = metaTitle;
    document.getElementById('seoPreviewUrl').textContent = `onlineamityuniversity.com/blog/${slug}`;
    document.getElementById('seoPreviewDesc').textContent = metaDesc;
}

// =====================================================
// FEATURE IMAGE UPLOAD
// =====================================================

function setupFeatureImageUpload() {
    const uploadArea = document.getElementById('featureImageUpload');
    const fileInput = document.getElementById('featureImageFile');
    
    uploadArea.addEventListener('click', (e) => {
        if (e.target.closest('.remove-image')) return;
        fileInput.click();
    });
    
    fileInput.addEventListener('change', async function() {
        if (!this.files.length) return;
        
        const altText = document.getElementById('featureImageAlt').value.trim() || 'Blog feature image';
        
        const formData = new FormData();
        formData.append('image', this.files[0]);
        formData.append('alt_text', altText);
        formData.append('subfolder', 'featured');
        
        showToast('Uploading image...', 'info');
        const result = await apiCall('admin_upload_image', 'POST', formData);
        
        if (result && result.success) {
            document.getElementById('featureImageUrl').value = result.url;
            document.getElementById('featureImagePreview').src = '../' + result.url;
            document.getElementById('featureImagePreview').style.display = 'block';
            document.getElementById('uploadPlaceholder').style.display = 'none';
            document.getElementById('removeFeatureImage').style.display = 'flex';
            uploadArea.classList.add('has-image');
            showToast('Image uploaded successfully!', 'success');
        } else {
            showToast(result?.message || 'Upload failed', 'error');
        }
    });
    
    document.getElementById('removeFeatureImage').addEventListener('click', (e) => {
        e.stopPropagation();
        document.getElementById('featureImageUrl').value = '';
        document.getElementById('featureImagePreview').style.display = 'none';
        document.getElementById('uploadPlaceholder').style.display = 'block';
        document.getElementById('removeFeatureImage').style.display = 'none';
        document.getElementById('featureImageFile').value = '';
        uploadArea.classList.remove('has-image');
    });
    
    // Drag & drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '#0a2e73';
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.style.borderColor = '';
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '';
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });
}

// =====================================================
// CATEGORIES
// =====================================================

async function loadCategoriesChecklist() {
    const data = await apiCall('admin_get_categories');
    if (!data || !data.success) return;
    
    const checklist = document.getElementById('categoriesChecklist');
    checklist.innerHTML = data.categories.map(cat => `
        <label>
            <input type="checkbox" value="${cat.id}"> ${escHtml(cat.name)}
        </label>
    `).join('');
}

async function loadCategoriesManager() {
    const data = await apiCall('admin_get_categories');
    if (!data || !data.success) return;
    
    const list = document.getElementById('categoriesList');
    if (data.categories.length === 0) {
        list.innerHTML = '<p class="empty-state">No categories yet</p>';
    } else {
        list.innerHTML = data.categories.map(cat => `
            <div class="category-item">
                <div class="category-item-info">
                    <h4>${escHtml(cat.name)}</h4>
                    <span>/${cat.slug} ${cat.description ? ' · ' + escHtml(cat.description) : ''}</span>
                </div>
                <div class="action-btns">
                    <button class="edit-btn" onclick="editCategory(${cat.id}, '${escHtml(cat.name).replace(/'/g, "\\'")}', '${(cat.description || '').replace(/'/g, "\\'")}')" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="delete-btn" onclick="deleteCategory(${cat.id})" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `).join('');
    }
}

function editCategory(id, name, desc) {
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryDescription').value = desc;
    document.getElementById('categoryFormTitle').textContent = 'Edit Category';
}

async function saveCategory() {
    const name = document.getElementById('categoryName').value.trim();
    if (!name) { showToast('Category name is required', 'error'); return; }
    
    const data = {
        id: parseInt(document.getElementById('categoryId').value) || 0,
        name: name,
        description: document.getElementById('categoryDescription').value.trim()
    };
    
    const result = await apiCall('admin_save_category', 'POST', data);
    if (result && result.success) {
        showToast('Category saved!', 'success');
        document.getElementById('categoryId').value = '0';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryDescription').value = '';
        document.getElementById('categoryFormTitle').textContent = 'Add New Category';
        loadCategoriesManager();
        loadCategoriesChecklist();
    } else {
        showToast(result?.message || 'Error saving category', 'error');
    }
}

async function deleteCategory(id) {
    if (!confirm('Delete this category?')) return;
    const result = await apiCall('admin_delete_category', 'POST', { id });
    if (result && result.success) {
        showToast('Category deleted', 'success');
        loadCategoriesManager();
        loadCategoriesChecklist();
    }
}

async function addNewCategory() {
    const input = document.getElementById('newCategoryInput');
    const name = input.value.trim();
    if (!name) return;
    
    const result = await apiCall('admin_save_category', 'POST', { name });
    if (result && result.success) {
        input.value = '';
        loadCategoriesChecklist();
        showToast('Category added!', 'success');
    }
}

// =====================================================
// TAGS
// =====================================================

function addTag() {
    const input = document.getElementById('newTagInput');
    const tag = input.value.trim();
    if (!tag || currentTags.includes(tag)) return;
    
    currentTags.push(tag);
    renderTags();
    input.value = '';
}

function removeTag(index) {
    currentTags.splice(index, 1);
    renderTags();
}

function renderTags() {
    const container = document.getElementById('tagsContainer');
    container.innerHTML = currentTags.map((tag, i) => `
        <span class="tag-chip">${escHtml(tag)} <span class="remove-tag" onclick="removeTag(${i})"><i class="fas fa-times"></i></span></span>
    `).join('');
}

// =====================================================
// AUTHORS
// =====================================================

async function loadAuthorsDropdown() {
    const data = await apiCall('admin_get_authors');
    if (!data || !data.success) return;
    
    const select = document.getElementById('postAuthor');
    select.innerHTML = '<option value="">Select Author</option>' +
        data.authors.map(a => `<option value="${a.id}">${escHtml(a.name)}</option>`).join('');
}

async function loadAuthorsManager() {
    const data = await apiCall('admin_get_authors');
    if (!data || !data.success) return;
    
    const list = document.getElementById('authorsList');
    if (data.authors.length === 0) {
        list.innerHTML = '<p class="empty-state">No authors yet</p>';
    } else {
        list.innerHTML = data.authors.map(a => `
            <div class="author-item">
                <div class="author-item-info">
                    <h4>${escHtml(a.name)}</h4>
                    <span>${a.bio ? escHtml(a.bio).substring(0, 60) + '...' : 'No bio'}</span>
                </div>
                <div class="action-btns">
                    <button class="edit-btn" onclick="editAuthor(${a.id})" title="Edit"><i class="fas fa-edit"></i></button>
                </div>
            </div>
        `).join('');
    }
}

async function editAuthor(id) {
    const data = await apiCall('admin_get_authors');
    if (!data || !data.success) return;
    
    const author = data.authors.find(a => a.id === id);
    if (!author) return;
    
    document.getElementById('authorId').value = author.id;
    document.getElementById('authorName').value = author.name;
    document.getElementById('authorBio').value = author.bio || '';
    document.getElementById('authorImage').value = author.image || '';
    document.getElementById('authorPageUrl').value = author.author_page_url || '';
    document.getElementById('authorFormTitle').textContent = 'Edit Author';
}

async function saveAuthor() {
    const name = document.getElementById('authorName').value.trim();
    if (!name) { showToast('Author name is required', 'error'); return; }
    
    const data = {
        id: parseInt(document.getElementById('authorId').value) || 0,
        name: name,
        bio: document.getElementById('authorBio').value.trim(),
        image: document.getElementById('authorImage').value.trim(),
        author_page_url: document.getElementById('authorPageUrl').value.trim()
    };
    
    const result = await apiCall('admin_save_author', 'POST', data);
    if (result && result.success) {
        showToast('Author saved!', 'success');
        document.getElementById('authorId').value = '0';
        document.getElementById('authorName').value = '';
        document.getElementById('authorBio').value = '';
        document.getElementById('authorImage').value = '';
        document.getElementById('authorPageUrl').value = '';
        document.getElementById('authorFormTitle').textContent = 'Add New Author';
        loadAuthorsManager();
        loadAuthorsDropdown();
    } else {
        showToast(result?.message || 'Error saving author', 'error');
    }
}

// =====================================================
// MEDIA LIBRARY
// =====================================================

async function uploadMediaImage() {
    const file = document.getElementById('mediaUploadFile').files[0];
    const altText = document.getElementById('mediaAltText').value.trim();
    
    if (!file) { showToast('No file selected', 'error'); return; }
    if (!altText) { showToast('Alt text is required', 'error'); return; }
    
    const formData = new FormData();
    formData.append('image', file);
    formData.append('alt_text', altText);
    formData.append('caption', document.getElementById('mediaCaption').value.trim());
    formData.append('subfolder', 'content');
    
    const result = await apiCall('admin_upload_image', 'POST', formData);
    if (result && result.success) {
        showToast('Image uploaded!', 'success');
        cancelMediaUpload();
        // Add to grid
        const grid = document.getElementById('mediaGrid');
        const emptyState = grid.querySelector('.empty-state');
        if (emptyState) emptyState.remove();
        
        const div = document.createElement('div');
        div.style.cssText = 'background:#f8fafc;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;';
        div.innerHTML = `
            <img src="../${result.url}" alt="${escHtml(altText)}" style="width:100%;height:140px;object-fit:cover;">
            <div style="padding:8px;font-size:0.8rem;">
                <input type="text" value="../${result.url}" readonly onclick="this.select();document.execCommand('copy')" style="width:100%;padding:4px 8px;border:1px solid #e2e8f0;border-radius:4px;font-size:0.75rem;" title="Click to copy URL">
            </div>
        `;
        grid.prepend(div);
    } else {
        showToast(result?.message || 'Upload failed', 'error');
    }
}

function cancelMediaUpload() {
    document.getElementById('mediaUploadForm').style.display = 'none';
    document.getElementById('mediaUploadFile').value = '';
    document.getElementById('mediaAltText').value = '';
    document.getElementById('mediaCaption').value = '';
}

// =====================================================
// UTILITIES
// =====================================================

function escHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
    toast.innerHTML = `<i class="fas fa-${icons[type] || 'info-circle'}"></i> ${message}`;
    
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
