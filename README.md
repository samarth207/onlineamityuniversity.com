# Amity Online University - Website

A professional, modern, responsive website for an online university platform inspired by Amity Online's design and structure.

## 🎯 Features

- **Fully Responsive Design** - Mobile-first approach with breakpoints for all devices
- **Modern UI/UX** - Clean typography, smooth animations, and intuitive navigation
- **SEO Optimized** - Meta tags, semantic HTML, and proper heading hierarchy
- **Interactive Elements** - Filterable programs, testimonial sliders, form validation
- **Accessibility Compliant** - ARIA labels, keyboard navigation, contrast ratios
- **Fast Performance** - Optimized CSS, lazy loading, minimal dependencies

## 📁 Project Structure

```
amityonlineuniversity_2/
├── index.html              # Homepage
├── programs.html           # Programs listing page
├── about.html             # About Us page
├── blog.html              # Blog articles page
├── contact.html           # Contact form page
├── css/
│   └── styles.css         # Main stylesheet
├── js/
│   └── script.js          # JavaScript functionality
└── assets/
    └── images/            # Image assets folder
```

## 🚀 Quick Start

1. **Download/Clone** the project to your local machine

2. **Add Images** (Optional):
   - Add a hero background image as `assets/images/hero-bg.jpg`
   - Recommended size: 1920x1080px
   - Or use the gradient background that's already set up

3. **Open in Browser**:
   - Simply open `index.html` in any modern web browser
   - No build process or server required

4. **Customize**:
   - Update brand name in all HTML files (currently "Amity Online")
   - Replace contact information with your actual details
   - Customize colors in `css/styles.css` (CSS variables at top)
   - Add your own images and content

## 🎨 Design System

### Colors
- **Navy Blue**: `#0a2e73` (Primary brand color)
- **Gold**: `#ffcc00` (Accent color)
- **White**: `#ffffff` (Background)
- **Light Gray**: `#f5f5f5` (Section backgrounds)
- **Dark Gray**: `#333333` (Text)

### Typography
- **Headings**: Montserrat (Google Fonts)
- **Body Text**: Open Sans (Google Fonts)

### Breakpoints
- **Mobile**: 375px - 480px
- **Tablet**: 481px - 768px
- **Desktop**: 769px+

## 📄 Pages Overview

### 1. Homepage (`index.html`)
- Hero section with CTA buttons
- Trusted company logos carousel
- Programs preview (6 featured programs)
- Why Choose Us section (8 advantage cards)
- Career Services section (6 support features)
- Student testimonials slider (5 reviews)
- Blog preview (3 latest articles)
- CTA section
- Footer with contact info

### 2. Programs (`programs.html`)
- Complete programs catalog (18 programs)
- Filterable by category (All, UG, PG, Certifications)
- Program cards with:
  - Duration
  - Fees
  - Description
  - Apply CTA

### 3. About Us (`about.html`)
- University story and mission
- Vision, Mission, Values cards
- Key achievements (4 milestones)
- Why Students Choose Us (6 reasons)
- Leadership team (3 leaders)
- Accreditations display

### 4. Blog (`blog.html`)
- Featured article section
- Blog grid (9 articles)
- Category browse section
- Newsletter subscription
- Pagination

### 5. Contact (`contact.html`)
- Contact information sidebar
- Detailed enquiry form with validation
- Quick contact options (Call, WhatsApp, Chat)
- Google Maps integration placeholder
- FAQ section

## ⚙️ Functionality

### JavaScript Features
- **Mobile Navigation**: Hamburger menu with smooth toggle
- **Sticky Header**: Enhanced shadow on scroll
- **Programs Filter**: Dynamic filtering by category
- **Scroll Animations**: Reveal elements on scroll
- **Smooth Scrolling**: Anchor link navigation
- **Testimonials Slider**: With navigation buttons
- **Form Validation**: Real-time validation for contact form
- **Counter Animation**: Animated statistics on hero section
- **Back to Top Button**: Appears after scrolling
- **Lazy Loading**: For optimized image loading

### Form Features
- Client-side validation
- Email format validation
- Phone number validation
- Required field checking
- Success/error messages
- Form reset after submission
- URL parameter handling (for enquire/apply)

## 🔧 Customization Guide

### 1. Update Brand Information

In all HTML files, replace:
```html
<a href="index.html" class="logo">Amity<span>Online</span></a>
```
With your brand name.

### 2. Change Colors

In `css/styles.css`, modify CSS variables:
```css
:root {
  --navy-blue: #0a2e73;  /* Your primary color */
  --gold: #ffcc00;        /* Your accent color */
  /* ... other colors ... */
}
```

### 3. Update Contact Information

In footer sections across all pages:
```html
<p><i class="fas fa-phone"></i> +91 YOUR-NUMBER</p>
<p><i class="fas fa-envelope"></i> your@email.com</p>
```

### 4. Add Hero Background Image

Replace the inline gradient in `index.html` hero section with:
```css
background: linear-gradient(135deg, rgba(10, 46, 115, 0.9), rgba(10, 46, 115, 0.7)),
            url('../assets/images/hero-bg.jpg') center/cover;
```

### 5. Integrate Backend

In `js/script.js`, find the `sendFormData()` function and add your API endpoint:
```javascript
fetch('YOUR_API_ENDPOINT', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
})
```

### 6. Add Google Analytics

Replace `GA_MEASUREMENT_ID` in `index.html` with your actual Google Analytics tracking ID.

### 7. Add Google Maps

In `contact.html`, replace the placeholder map section with actual Google Maps embed code from Google Maps Platform.

## 📱 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🎓 Educational Content

The website includes placeholder content for:
- 6 MBA programs
- 6 BCA/MCA programs  
- 6 BBA/B.Com/BA programs
- 6 Professional certifications
- 9 blog articles
- 5 student testimonials

**Replace all content with your actual program details and information.**

## 📧 Email Integration Options

### Option 1: EmailJS
```javascript
emailjs.send("service_id", "template_id", data)
```

### Option 2: Formspree
```html
<form action="https://formspree.io/f/YOUR_FORM_ID" method="POST">
```

### Option 3: Google Apps Script
Deploy a Google Apps Script as a web app to send form data to Google Sheets.

### Option 4: Backend API
Create your own Node.js/PHP backend to handle form submissions.

## 🔐 Security Notes

- Add CAPTCHA (reCAPTCHA) to prevent spam
- Implement rate limiting on form submissions
- Sanitize all user inputs on backend
- Use HTTPS in production
- Add CSRF tokens if using backend

## 🚀 Deployment

### GitHub Pages
1. Push code to GitHub repository
2. Go to Settings > Pages
3. Select branch and root folder
4. Your site will be live at `username.github.io/repository-name`

### Netlify
1. Connect GitHub repository
2. Set build command: (none needed)
3. Set publish directory: `/`
4. Deploy

### Vercel
1. Import GitHub repository
2. Configure project (no build needed)
3. Deploy

## 📝 License

This is a template website. Feel free to use and modify for your projects.

## 🤝 Support

For questions or issues:
- Check the code comments in HTML/CSS/JS files
- Review browser console for JavaScript errors
- Ensure all file paths are correct
- Test in multiple browsers

## 🎉 Credits

- **Fonts**: Google Fonts (Montserrat, Open Sans)
- **Icons**: Font Awesome 6.4.0
- **Design Inspiration**: Amity Online University
- **Framework**: Vanilla HTML/CSS/JavaScript (no dependencies)

---

**Ready to Launch!** 🚀

Simply customize the content, add your images, and deploy to your hosting provider.

For production, consider adding:
- Backend API for form handling
- Google Analytics integration
- Real Google Maps embed
- Actual accreditation badges/logos
- Professional photography
- Content Management System (if needed)

---

**Good luck with your online university platform!** 🎓
