// ========================================
// MBA PAGE - JavaScript
// ========================================

// Quick Apply Form Handlers
document.addEventListener('DOMContentLoaded', function() {
    // Hero Form Handler - Now handled by form-validation.js
    // Quick Apply Form Handler - Now handled by form-validation.js
    
    // ========================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#lightbox') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const offset = 80;
                    const targetPosition = target.offsetTop - offset;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // ========================================
    // DOWNLOAD BROCHURE HANDLER - Now handled by form-validation.js
    // ========================================
    
    // ========================================
    // STICKY FORM BEHAVIOR
    // ========================================
    const stickyForm = document.querySelector('.sticky-form');
    const heroSection = document.querySelector('section[style*="linear-gradient"]');
    
    if (stickyForm && heroSection) {
        window.addEventListener('scroll', function() {
            const heroBottom = heroSection.offsetTop + heroSection.offsetHeight;
            
            if (window.scrollY > heroBottom - 100) {
                stickyForm.style.position = 'fixed';
                stickyForm.style.top = '80px';
            } else {
                stickyForm.style.position = 'relative';
                stickyForm.style.top = 'auto';
            }
        });
    }
    
    // ========================================
    // WHAT MAKES THIS MBA DIFFERENT CAROUSEL
    // ========================================
    const mbaCarouselTrack = document.querySelector('.mba-carousel-track');
    const mbaCarouselCards = document.querySelectorAll('.mba-carousel-card');
    const mbaCarouselDots = document.querySelectorAll('.mba-carousel-dot');
    
    if (mbaCarouselTrack && mbaCarouselCards.length > 0) {
        let mbaCurrentIndex = 0;
        let mbaDirection = 1; // 1 for left to right, -1 for right to left
        const mbaCardWidth = 320; // Card width
        const mbaGap = 20; // Gap between cards
        const mbaTotalCards = mbaCarouselCards.length;
        const mbaMaxIndex = mbaTotalCards - 2; // Show 2 cards at a time
        
        function updateMBACarousel() {
            const offset = mbaCurrentIndex * (mbaCardWidth + mbaGap);
            mbaCarouselTrack.style.transition = 'transform 1s ease-in-out';
            mbaCarouselTrack.style.transform = `translateX(-${offset}px)`;
            
            // Update dots
            mbaCarouselDots.forEach((dot, index) => {
                if (index === Math.floor(mbaCurrentIndex)) {
                    dot.style.background = 'white';
                    dot.style.width = '30px';
                } else {
                    dot.style.background = 'rgba(255,255,255,0.5)';
                    dot.style.width = '12px';
                }
            });
        }
        
        function autoPlayMBACarousel() {
            mbaCurrentIndex += mbaDirection;
            
            // Reverse direction at boundaries
            if (mbaCurrentIndex >= mbaMaxIndex) {
                mbaDirection = -1;
            } else if (mbaCurrentIndex <= 0) {
                mbaDirection = 1;
            }
            
            updateMBACarousel();
        }
        
        // Auto-play every 3 seconds
        let mbaAutoPlayInterval = setInterval(autoPlayMBACarousel, 3000);
        
        // Pause on hover
        const mbaCarouselWrapper = document.querySelector('.mba-carousel-wrapper');
        if (mbaCarouselWrapper) {
            mbaCarouselWrapper.addEventListener('mouseenter', () => {
                clearInterval(mbaAutoPlayInterval);
            });
            
            mbaCarouselWrapper.addEventListener('mouseleave', () => {
                mbaAutoPlayInterval = setInterval(autoPlayMBACarousel, 3000);
            });
        }
        
        // Dot click functionality
        mbaCarouselDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                mbaCurrentIndex = index;
                updateMBACarousel();
            });
        });
        
        // Initialize
        updateMBACarousel();
    }
});

// ========================================
// AMITY ONLINE UNIVERSITY - MAIN SCRIPT
// Modern, Interactive, Responsive
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // MOBILE NAVIGATION TOGGLE
    // ========================================
    const mobileToggle = document.getElementById('mobileToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            mobileToggle.classList.toggle('active');
            
            // Animate hamburger icon (fallback for inline styles)
            const isActive = navMenu.classList.contains('active');
            const spans = mobileToggle.querySelectorAll('span');
            spans[0].style.transform = isActive ? 'rotate(45deg) translate(6px, 6px)' : 'none';
            spans[1].style.opacity = isActive ? '0' : '1';
            spans[2].style.transform = isActive ? 'rotate(-45deg) translate(6px, -6px)' : 'none';
        });
        
        // Close menu when clicking a link
        const navLinks = navMenu.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                mobileToggle.classList.remove('active');
                const spans = mobileToggle.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!navMenu.contains(event.target) && !mobileToggle.contains(event.target)) {
                navMenu.classList.remove('active');
                mobileToggle.classList.remove('active');
                const spans = mobileToggle.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
    }
    
    // ========================================
    // STICKY HEADER ON SCROLL
    // ========================================
    const header = document.querySelector('.header');
    let lastScroll = 0;
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
        } else {
            header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
        }
        
        lastScroll = currentScroll;
    });
    
    // ========================================
    // PROGRAMS FILTER FUNCTIONALITY
    // ========================================
    const filterButtons = document.querySelectorAll('.filter-btn');
    const programCards = document.querySelectorAll('.program-card');
    
    if (filterButtons.length > 0 && programCards.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Get filter value
                const filterValue = this.getAttribute('data-filter');
                
                // Filter programs
                programCards.forEach(card => {
                    const category = card.getAttribute('data-category');
                    
                    if (filterValue === 'all' || category === filterValue) {
                        card.style.display = 'block';
                        card.style.animation = 'fadeInUp 0.5s ease-out';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }
    
    // ========================================
    // SCROLL REVEAL ANIMATIONS
    // ========================================
    const revealElements = document.querySelectorAll('.scroll-reveal');
    
    if (revealElements.length > 0) {
        const revealOnScroll = function() {
            const windowHeight = window.innerHeight;
            const revealPoint = 150;
            
            revealElements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                
                if (elementTop < windowHeight - revealPoint) {
                    element.classList.add('active');
                }
            });
        };
        
        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll(); // Initial check
    }
    
    // ========================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ========================================
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Skip if href is just "#"
            if (href === '#') return;
            
            const target = document.querySelector(href);
            
            if (target) {
                e.preventDefault();
                const headerHeight = header.offsetHeight;
                const targetPosition = target.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // ========================================
    // TESTIMONIALS SLIDER AUTO-SCROLL
    // ========================================
    const testimonialsSlider = document.querySelector('.testimonials-slider');
    
    if (testimonialsSlider) {
        let scrollAmount = 0;
        const scrollSpeed = 1;
        const cardWidth = 370; // card width + gap
        
        // Auto scroll functionality (optional - can be enabled)
        // setInterval(() => {
        //     if (scrollAmount < testimonialsSlider.scrollWidth - testimonialsSlider.clientWidth) {
        //         scrollAmount += scrollSpeed;
        //         testimonialsSlider.scrollLeft = scrollAmount;
        //     } else {
        //         scrollAmount = 0;
        //         testimonialsSlider.scrollLeft = 0;
        //     }
        // }, 30);
        
        // Add navigation buttons (optional enhancement)
        const sliderContainer = testimonialsSlider.parentElement;
        
        // Create navigation buttons
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevBtn.style.cssText = 'position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: var(--gold); border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.2);';
        
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextBtn.style.cssText = 'position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: var(--gold); border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.2);';
        
        sliderContainer.style.position = 'relative';
        sliderContainer.appendChild(prevBtn);
        sliderContainer.appendChild(nextBtn);
        
        prevBtn.addEventListener('click', () => {
            testimonialsSlider.scrollBy({
                left: -cardWidth,
                behavior: 'smooth'
            });
        });
        
        nextBtn.addEventListener('click', () => {
            testimonialsSlider.scrollBy({
                left: cardWidth,
                behavior: 'smooth'
            });
        });
    }
    
    // ========================================
    // CONTACT FORM VALIDATION & SUBMISSION
    // ========================================
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(contactForm);
            const data = Object.fromEntries(formData);
            
            // Basic validation
            if (!data.fullName || !data.email || !data.phone || !data.message) {
                showFormMessage('Please fill in all required fields.', 'error');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(data.email)) {
                showFormMessage('Please enter a valid email address.', 'error');
                return;
            }
            
            // Phone validation (basic)
            const phoneRegex = /^[\d\s\+\-\(\)]+$/;
            if (!phoneRegex.test(data.phone) || data.phone.replace(/\D/g, '').length < 10) {
                showFormMessage('Please enter a valid phone number.', 'error');
                return;
            }
            
            // Consent check
            if (!data.consent) {
                showFormMessage('Please accept the consent to proceed.', 'error');
                return;
            }
            
            // Show loading state
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;
            
            // Simulate form submission (replace with actual API call)
            setTimeout(() => {
                // Here you would typically send data to your backend
                console.log('Form Data:', data);
                
                // Show success message
                showFormMessage('Thank you for contacting us! We will get back to you within 24 hours.', 'success');
                
                // Reset form
                contactForm.reset();
                
                // Reset button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                
                // Optional: Send to Google Sheets or Email API
                // sendFormData(data);
                
            }, 2000);
        });
    }
    
    function showFormMessage(message, type) {
        const messageDiv = document.getElementById('formMessage');
        
        if (messageDiv) {
            messageDiv.textContent = message;
            messageDiv.style.display = 'block';
            
            if (type === 'success') {
                messageDiv.style.background = '#d4edda';
                messageDiv.style.color = '#155724';
                messageDiv.style.border = '1px solid #c3e6cb';
            } else {
                messageDiv.style.background = '#f8d7da';
                messageDiv.style.color = '#721c24';
                messageDiv.style.border = '1px solid #f5c6cb';
            }
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }
    }
    
    // ========================================
    // URL PARAMETER HANDLING (for form type)
    // ========================================
    const urlParams = new URLSearchParams(window.location.search);
    const formType = urlParams.get('type');
    
    if (formType && contactForm) {
        const formTypeSelect = document.getElementById('formType');
        if (formTypeSelect) {
            if (formType === 'enquire') {
                formTypeSelect.value = 'enquire';
            } else if (formType === 'apply') {
                formTypeSelect.value = 'apply';
            }
        }
    }
    
    // ========================================
    // ANIMATE NUMBERS (Counter Effect)
    // ========================================
    const animateNumbers = function() {
        const counters = document.querySelectorAll('.hero-stat h3');
        
        counters.forEach(counter => {
            const target = counter.textContent;
            const numericValue = parseInt(target.replace(/\D/g, ''));
            
            if (!isNaN(numericValue) && !counter.dataset.animated) {
                counter.dataset.animated = 'true';
                let current = 0;
                const increment = numericValue / 50;
                const suffix = target.replace(/[\d,]/g, '');
                
                const updateCounter = () => {
                    current += increment;
                    if (current < numericValue) {
                        counter.textContent = Math.floor(current).toLocaleString() + suffix;
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                updateCounter();
            }
        });
    };
    
    // Trigger counter animation when hero section is visible
    const heroSection = document.querySelector('.hero');
    if (heroSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateNumbers();
                }
            });
        });
        
        observer.observe(heroSection);
    }
    
    // ========================================
    // BACK TO TOP BUTTON
    // ========================================
    const backToTopBtn = document.createElement('button');
    backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopBtn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--gold);
        color: var(--navy-blue);
        border: none;
        border-radius: 50%;
        font-size: 1.2rem;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    `;
    
    document.body.appendChild(backToTopBtn);
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.opacity = '1';
            backToTopBtn.style.visibility = 'visible';
        } else {
            backToTopBtn.style.opacity = '0';
            backToTopBtn.style.visibility = 'hidden';
        }
    });
    
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    backToTopBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
    });
    
    backToTopBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
    
    // ========================================
    // LAZY LOADING FOR IMAGES (if needed)
    // ========================================
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });
        
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }
    
    // ========================================
    // CONSOLE MESSAGE
    // ========================================
    console.log('%cAmity Online University', 'color: #0a2e73; font-size: 24px; font-weight: bold;');
    console.log('%cWebsite developed with modern web technologies', 'color: #666; font-size: 14px;');
    console.log('%cðŸŽ“ Empowering Lives Through Education', 'color: #ffcc00; font-size: 16px; font-weight: bold;');
    
});

// ========================================
// UTILITY FUNCTIONS
// ========================================

// Email sending function (integrate with your backend or service)
function sendFormData(data) {
    // Example: Send to backend API
    /*
    fetch('/api/contact', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        console.log('Success:', result);
    })
    .catch(error => {
        console.error('Error:', error);
    });
    */
    
    // Example: Send to Google Sheets via Google Apps Script
    /*
    const scriptURL = 'YOUR_GOOGLE_APPS_SCRIPT_URL';
    fetch(scriptURL, {
        method: 'POST',
        body: new FormData(document.getElementById('contactForm'))
    })
    .then(response => console.log('Success!', response))
    .catch(error => console.error('Error!', error.message));
    */
}

// Debounce function for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function for scroll events
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ========================================
// NEW PROGRAMS SECTION FILTERS
// ========================================
const filterBtnsNew = document.querySelectorAll('.filter-btn-new');
const programCardsNew = document.querySelectorAll('.program-card-new');

if (filterBtnsNew.length > 0) {
    filterBtnsNew.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterBtnsNew.forEach(b => {
                b.classList.remove('active');
                b.style.background = 'white';
                b.style.color = '#666';
                b.style.borderColor = '#ccc';
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            this.style.background = 'var(--navy-blue)';
            this.style.color = 'white';
            this.style.borderColor = 'var(--navy-blue)';
            
            // Get filter value
            const filterValue = this.getAttribute('data-filter');
            
            // Filter programs
            programCardsNew.forEach(card => {
                const category = card.getAttribute('data-category');
                
                if (filterValue === 'all' || category === filterValue) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeInUp 0.5s ease-out';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
}

// Hover effects for new program cards
if (programCardsNew.length > 0) {
    programCardsNew.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 8px 24px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 12px rgba(0,0,0,0.08)';
        });
        
        const btn = card.querySelector('button');
        if (btn) {
            btn.addEventListener('mouseenter', function() {
                this.style.background = '#0a4ba8';
                this.style.transform = 'scale(1.02)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.background = 'var(--navy-blue)';
                this.style.transform = 'scale(1)';
            });
        }
    });
}

// Carousel arrows functionality
const carouselLeft = document.querySelector('.carousel-arrow-left');
const carouselRight = document.querySelector('.carousel-arrow-right');
const carousel = document.querySelector('.programs-carousel');

if (carouselLeft && carouselRight && carousel) {
    carouselLeft.addEventListener('click', function() {
        carousel.scrollBy({ left: -400, behavior: 'smooth' });
    });
    
    carouselRight.addEventListener('click', function() {
        carousel.scrollBy({ left: 400, behavior: 'smooth' });
    });
}

// Category tabs active state and filtering
const categoryTabs = document.querySelectorAll('.category-tab');
if (categoryTabs.length > 0) {
    categoryTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active state from all tabs
            categoryTabs.forEach(t => {
                t.classList.remove('active');
                t.style.color = '#666';
                t.style.borderBottom = 'none';
            });
            
            // Add active state to clicked tab
            this.classList.add('active');
            this.style.color = '#8B0000';
            this.style.borderBottom = '2px solid #8B0000';
            
            // Get category type
            const categoryType = this.getAttribute('data-category-type');
            
            // Filter courses by category type
            if (programCardsNew.length > 0) {
                programCardsNew.forEach(card => {
                    const cardType = card.getAttribute('data-type');
                    
                    if (cardType === categoryType) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Reset carousel position
                if (carousel) {
                    carousel.scrollTo({ left: 0, behavior: 'smooth' });
                }
            }
        });
    });
    
    // Initialize with management category on page load
    setTimeout(() => {
        const firstTab = document.querySelector('.category-tab.active');
        if (firstTab) {
            firstTab.click();
        }
    }, 100);
}

// Set carousel to show only 4 cards at a time
if (carousel && programCardsNew.length > 0) {
    // Wait for DOM to be fully loaded
    setTimeout(() => {
        const carouselWidth = carousel.offsetWidth;
        const gap = 24; // 1.5rem = 24px
        const cardWidth = (carouselWidth - (3 * gap)) / 4; // 4 cards with gaps
        
        programCardsNew.forEach(card => {
            card.style.minWidth = cardWidth + 'px';
            card.style.flex = '0 0 ' + cardWidth + 'px';
        });
    }, 200);
}

// ========================================
// WHY AMITY CAROUSEL
// ========================================
const whyCarouselPrev = document.querySelector('.why-carousel-prev');
const whyCarouselNext = document.querySelector('.why-carousel-next');
const whyCarouselInner = document.querySelector('.why-carousel-inner');
const whyCards = document.querySelectorAll('.why-card');

if (whyCarouselPrev && whyCarouselNext && whyCarouselInner && whyCards.length > 0) {
    let whyCurrentIndex = 0;
    const whyCardsToShow = 4;
    const whyTotalCards = whyCards.length;
    const whyMaxIndex = Math.max(0, whyTotalCards - whyCardsToShow);
    
    function updateWhyCarousel() {
        const whyCardWidth = whyCards[0].offsetWidth;
        const whyGap = 30;
        const whyOffset = whyCurrentIndex * (whyCardWidth + whyGap);
        whyCarouselInner.style.transform = `translateX(-${whyOffset}px)`;
        
        // Update button states
        whyCarouselPrev.style.opacity = whyCurrentIndex === 0 ? '0.5' : '1';
        whyCarouselPrev.style.cursor = whyCurrentIndex === 0 ? 'not-allowed' : 'pointer';
        whyCarouselNext.style.opacity = whyCurrentIndex >= whyMaxIndex ? '0.5' : '1';
        whyCarouselNext.style.cursor = whyCurrentIndex >= whyMaxIndex ? 'not-allowed' : 'pointer';
    }
    
    whyCarouselPrev.addEventListener('click', () => {
        if (whyCurrentIndex > 0) {
            whyCurrentIndex--;
            updateWhyCarousel();
        }
    });
    
    whyCarouselNext.addEventListener('click', () => {
        if (whyCurrentIndex < whyMaxIndex) {
            whyCurrentIndex++;
            updateWhyCarousel();
        }
    });
    
    // Initialize carousel
    updateWhyCarousel();
    
    // Update on window resize
    window.addEventListener('resize', updateWhyCarousel);
    
    // Add hover effects to navigation buttons
    whyCarouselPrev.addEventListener('mouseenter', function() {
        if (whyCurrentIndex > 0) {
            this.style.borderColor = 'var(--navy-blue)';
            this.style.transform = 'translateY(-50%) scale(1.1)';
        }
    });
    
    whyCarouselPrev.addEventListener('mouseleave', function() {
        this.style.borderColor = '#ddd';
        this.style.transform = 'translateY(-50%) scale(1)';
    });
    
    whyCarouselNext.addEventListener('mouseenter', function() {
        if (whyCurrentIndex < whyMaxIndex) {
            this.style.borderColor = 'var(--navy-blue)';
            this.style.transform = 'translateY(-50%) scale(1.1)';
        }
    });
    
    whyCarouselNext.addEventListener('mouseleave', function() {
        this.style.borderColor = '#ddd';
        this.style.transform = 'translateY(-50%) scale(1)';
    });
}

// ========================================
// REVIEWS CAROUSEL
// ========================================
const reviewsCarouselTrack = document.querySelector('.reviews-carousel-track');
const reviewsCards = document.querySelectorAll('.review-card');
const reviewsPrevBtn = document.querySelector('.reviews-prev-arrow');
const reviewsNextBtn = document.querySelector('.reviews-next-arrow');

if (reviewsCarouselTrack && reviewsCards.length > 0) {
    let reviewsCurrentIndex = 0;
    const reviewsCardsToShow = 4;
    const reviewsTotalCards = reviewsCards.length;
    const reviewsMaxIndex = Math.max(0, reviewsTotalCards - reviewsCardsToShow);
    
    function updateReviewsCarousel() {
        const reviewsCardWidth = reviewsCards[0].offsetWidth;
        const reviewsGap = 20;
        const reviewsOffset = reviewsCurrentIndex * (reviewsCardWidth + reviewsGap);
        reviewsCarouselTrack.style.transform = `translateX(-${reviewsOffset}px)`;
        
        // Update button states
        if (reviewsPrevBtn) {
            reviewsPrevBtn.style.opacity = reviewsCurrentIndex === 0 ? '0.5' : '1';
            reviewsPrevBtn.style.cursor = reviewsCurrentIndex === 0 ? 'not-allowed' : 'pointer';
            reviewsPrevBtn.disabled = reviewsCurrentIndex === 0;
        }
        
        if (reviewsNextBtn) {
            reviewsNextBtn.style.opacity = reviewsCurrentIndex >= reviewsMaxIndex ? '0.5' : '1';
            reviewsNextBtn.style.cursor = reviewsCurrentIndex >= reviewsMaxIndex ? 'not-allowed' : 'pointer';
            reviewsNextBtn.disabled = reviewsCurrentIndex >= reviewsMaxIndex;
        }
    }
    
    if (reviewsPrevBtn) {
        reviewsPrevBtn.addEventListener('click', () => {
            if (reviewsCurrentIndex > 0) {
                reviewsCurrentIndex--;
                updateReviewsCarousel();
            }
        });
    }
    
    if (reviewsNextBtn) {
        reviewsNextBtn.addEventListener('click', () => {
            if (reviewsCurrentIndex < reviewsMaxIndex) {
                reviewsCurrentIndex++;
                updateReviewsCarousel();
            }
        });
    }
    
    // Initialize carousel
    updateReviewsCarousel();
    
    // Update on window resize
    window.addEventListener('resize', updateReviewsCarousel);
    
    // Video play functionality
    const videoCards = document.querySelectorAll('.video-card');
    const allVideos = document.querySelectorAll('.testimonial-video');
    
    // Function to pause all videos and show thumbnails
    function pauseAllVideos(exceptVideo) {
        videoCards.forEach(card => {
            const video = card.querySelector('.testimonial-video');
            const thumbnail = card.querySelector('.video-thumbnail');
            const playBtn = card.querySelector('.video-play-btn');
            
            if (video && video !== exceptVideo && !video.paused) {
                video.pause();
                video.currentTime = 0;
                video.style.display = 'none';
                if (thumbnail) thumbnail.style.display = 'block';
                if (playBtn) playBtn.style.display = 'flex';
            }
        });
    }
    
    videoCards.forEach(card => {
        const playBtn = card.querySelector('.video-play-btn');
        const thumbnail = card.querySelector('.video-thumbnail');
        const video = card.querySelector('.testimonial-video');
        
        if (playBtn && video && thumbnail) {
            // Handle both click and touch events for mobile
            const handlePlay = (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Pause all other videos first
                pauseAllVideos(video);
                
                thumbnail.style.display = 'none';
                playBtn.style.display = 'none';
                video.style.display = 'block';
                
                // Use play() with promise for mobile compatibility
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(error => {
                        console.log('Auto-play was prevented:', error);
                        // Show controls for manual play on mobile
                        video.setAttribute('controls', 'controls');
                    });
                }
            };
            
            playBtn.addEventListener('click', handlePlay);
            playBtn.addEventListener('touchend', handlePlay);
            
            // Also make thumbnail clickable to play video
            thumbnail.addEventListener('click', handlePlay);
            thumbnail.addEventListener('touchend', handlePlay);
            
            // Make entire card clickable for video cards
            card.addEventListener('click', (e) => {
                if (e.target === card || e.target === thumbnail || e.target === playBtn || playBtn.contains(e.target)) {
                    handlePlay(e);
                }
            });
            
            // Show thumbnail and play button when video ends
            video.addEventListener('ended', () => {
                video.style.display = 'none';
                thumbnail.style.display = 'block';
                playBtn.style.display = 'flex';
            });
            
            // Pause video when clicking outside or on video controls
            video.addEventListener('pause', () => {
                if (video.currentTime === 0 || video.ended) {
                    video.style.display = 'none';
                    thumbnail.style.display = 'block';
                    playBtn.style.display = 'flex';
                }
            });
        }
    });
}

// ========================================
// FAQ ACCORDION
// ========================================
const faqItems = document.querySelectorAll('.faq-item');
const faqTabBtns = document.querySelectorAll('.faq-tab-btn');

if (faqItems.length > 0) {
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        const icon = item.querySelector('.faq-icon');
        
        if (question && answer && icon) {
            question.addEventListener('click', () => {
                const isOpen = answer.style.display === 'block';
                
                // Close all other FAQs first
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        const otherAnswer = otherItem.querySelector('.faq-answer');
                        const otherIcon = otherItem.querySelector('.faq-icon');
                        if (otherAnswer && otherIcon) {
                            otherAnswer.style.display = 'none';
                            otherIcon.style.transform = 'rotate(0deg)';
                        }
                    }
                });
                
                // Toggle current FAQ
                if (isOpen) {
                    answer.style.display = 'none';
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    answer.style.display = 'block';
                    icon.style.transform = 'rotate(180deg)';
                }
            });
        }
    });
}

// FAQ Tab Switching
if (faqTabBtns.length > 0) {
    faqTabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const category = btn.getAttribute('data-category');
            
            // Update active tab button
            faqTabBtns.forEach(b => {
                b.style.background = 'white';
                b.style.color = '#4b5563';
                b.style.border = '1px solid #e5e7eb';
                b.classList.remove('active');
            });
            btn.style.background = '#1e3a8a';
            btn.style.color = 'white';
            btn.style.border = '1px solid #1e3a8a';
            btn.classList.add('active');
            
            // Show/hide FAQ items based on category
            faqItems.forEach(item => {
                const itemCategory = item.getAttribute('data-category');
                if (itemCategory === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
}

// ========================================
// APPLY NOW FORM VALIDATION
// ========================================
const applyForm = document.getElementById('apply-form');
const fullNameInput = document.getElementById('full_name');
const phoneNumberInput = document.getElementById('phone_number');
const emailInput = document.getElementById('email_id');
const consentCheckbox = document.getElementById('consent');

if (applyForm) {
    const submitBtn = applyForm.querySelector('button[type="submit"]');
    
    // Function to validate form
    function validateForm() {
        const isValid = 
            fullNameInput.value.trim() !== '' &&
            phoneNumberInput.value.trim() !== '' &&
            emailInput.value.trim() !== '' &&
            consentCheckbox.checked;
        
        if (isValid) {
            submitBtn.style.background = 'var(--gold)';
            submitBtn.style.color = '#1e3a8a';
            submitBtn.style.cursor = 'pointer';
            submitBtn.style.border = '1px solid var(--gold)';
            submitBtn.disabled = false;
        } else {
            submitBtn.style.background = '#e7e7e7';
            submitBtn.style.color = '#6b7280';
            submitBtn.style.cursor = 'not-allowed';
            submitBtn.style.border = '1px solid #9ca3af';
            submitBtn.disabled = true;
        }
    }
    
    // Add event listeners
    fullNameInput.addEventListener('input', validateForm);
    phoneNumberInput.addEventListener('input', validateForm);
    emailInput.addEventListener('input', validateForm);
    consentCheckbox.addEventListener('change', validateForm);
    
    // Handle form submission
    applyForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Show loading state
        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;
        
        try {
            // Get course from dropdown if available
            const courseSelect = applyForm.querySelector('select[name="course"]');
            const course = courseSelect ? courseSelect.value : (document.title.split('|')[0].trim() || 'General Inquiry');
            
            // Prepare form data
            const formData = {
                formType: 'apply',
                name: fullNameInput.value.trim(),
                phone: phoneNumberInput.value.trim(),
                email: emailInput.value.trim(),
                consent: consentCheckbox.checked,
                course: course
            };
            
            // Submit form
            const response = await fetch('submit-form.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Store submission flag in session
                sessionStorage.setItem('formSubmitted', 'true');
                if (window.NotificationSystem) {
                    NotificationSystem.success('Thank you! Your application has been submitted successfully. Redirecting...');
                }
                // Redirect to thank you page
                setTimeout(() => {
                    window.location.href = 'thank-you';
                }, 1000);
            } else {
                if (window.NotificationSystem) {
                    NotificationSystem.error('We apologize for the inconvenience. There was an issue submitting your application. Please try again or contact us directly at +91 8920785477.');
                } else {
                    alert('We apologize for the inconvenience. There was an issue submitting your application. Please try again or contact us directly at +91 8920785477.');
                }
                submitBtn.textContent = 'Submit Application';
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Form submission error:', error);
            if (window.NotificationSystem) {
                NotificationSystem.error('We apologize for the inconvenience. There was a connection error. Please check your internet connection and try again, or contact us directly at +91 8920785477.');
            } else {
                alert('We apologize for the inconvenience. There was a connection error. Please check your internet connection and try again, or contact us directly at +91 8920785477.');
            }
            submitBtn.textContent = 'Submit Application';
            submitBtn.disabled = false;
        }
    });
}

// ========================================
// REVIEW MODAL
// ========================================
const reviewModal = document.getElementById('reviewModal');
const closeModalBtn = document.getElementById('closeModal');
const readMoreButtons = document.querySelectorAll('.review-read-more');

if (reviewModal && closeModalBtn && readMoreButtons.length > 0) {
    // Open modal when Read More is clicked
    readMoreButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const reviewCard = e.target.closest('.review-card');
            
            // Get review data from data attributes
            const reviewerName = reviewCard.getAttribute('data-reviewer');
            const reviewerPosition = reviewCard.getAttribute('data-position');
            const rating = reviewCard.getAttribute('data-rating');
            const fullReview = reviewCard.getAttribute('data-full-review');
            const reviewerImage = reviewCard.querySelector('img').src;
            
            // Populate modal with review data
            document.getElementById('modalReviewerName').textContent = reviewerName;
            document.getElementById('modalReviewerPosition').textContent = reviewerPosition;
            document.getElementById('modalReviewerRating').textContent = rating;
            document.getElementById('modalReviewText').textContent = fullReview;
            
            // Set reviewer image
            const modalImage = document.getElementById('modalReviewerImage');
            modalImage.style.backgroundImage = `url('${reviewerImage}')`;
            modalImage.style.backgroundSize = 'cover';
            modalImage.style.backgroundPosition = 'center';
            
            // Show modal
            reviewModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Close modal when close button is clicked
    closeModalBtn.addEventListener('click', () => {
        reviewModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
    
    // Close modal when clicking outside the modal content
    reviewModal.addEventListener('click', (e) => {
        if (e.target === reviewModal) {
            reviewModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && reviewModal.style.display === 'flex') {
            reviewModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
}

// ========================================
// MOBILE TOUCH SWIPE FOR CAROUSELS
// ========================================
function initTouchSwipe() {
    const carousels = document.querySelectorAll('.programs-carousel-inner, .why-amity-carousel-inner, .testimonials-slider, .brand-logos-container');
    
    carousels.forEach(carousel => {
        if (!carousel) return;
        
        let startX = 0;
        let scrollLeft = 0;
        let isDown = false;
        let isDragging = false;
        
        carousel.addEventListener('touchstart', (e) => {
            isDown = true;
            startX = e.touches[0].pageX - carousel.offsetLeft;
            scrollLeft = carousel.scrollLeft;
        }, { passive: true });
        
        carousel.addEventListener('touchmove', (e) => {
            if (!isDown) return;
            isDragging = true;
            const x = e.touches[0].pageX - carousel.offsetLeft;
            const walk = (x - startX) * 2;
            carousel.scrollLeft = scrollLeft - walk;
        }, { passive: true });
        
        carousel.addEventListener('touchend', () => {
            isDown = false;
            isDragging = false;
        });
        
        // Mouse drag support for desktop testing
        carousel.addEventListener('mousedown', (e) => {
            isDown = true;
            carousel.classList.add('active-dragging');
            startX = e.pageX - carousel.offsetLeft;
            scrollLeft = carousel.scrollLeft;
        });
        
        carousel.addEventListener('mouseleave', () => {
            isDown = false;
            carousel.classList.remove('active-dragging');
        });
        
        carousel.addEventListener('mouseup', () => {
            isDown = false;
            carousel.classList.remove('active-dragging');
        });
        
        carousel.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - carousel.offsetLeft;
            const walk = (x - startX) * 2;
            carousel.scrollLeft = scrollLeft - walk;
        });
    });
}

// Initialize touch swipe on load
initTouchSwipe();

// ========================================
// RESPONSIVE WINDOW RESIZE HANDLER
// ========================================
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        // Reset mobile menu state on resize
        const navMenu = document.getElementById('navMenu');
        const mobileToggle = document.getElementById('mobileToggle');
        
        if (window.innerWidth > 768 && navMenu && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            if (mobileToggle) {
                const spans = mobileToggle.querySelectorAll('span');
                spans.forEach(span => span.style.transform = 'none');
                spans[1].style.opacity = '1';
            }
        }
        
        // Re-initialize touch swipe
        initTouchSwipe();
    }, 250);
});

// ========================================
// VIEWPORT HEIGHT FIX FOR MOBILE
// ========================================
function setVH() {
    let vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
}

setVH();
window.addEventListener('resize', setVH);
window.addEventListener('orientationchange', function() {
    setTimeout(setVH, 100);
});

// ========================================
// PREVENT SCROLL WHEN MODAL OPEN
// ========================================
function toggleBodyScroll(disable) {
    if (disable) {
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.body.style.top = `-${window.scrollY}px`;
    } else {
        const scrollY = document.body.style.top;
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
        document.body.style.top = '';
        window.scrollTo(0, parseInt(scrollY || '0') * -1);
    }
}

