# YashRaj Systems - Project Documentation

This document provides a comprehensive technical reference for the **YashRaj Systems & Services** website. It outlines the project's purpose, folder structure, styling guidelines, interactivity logic, and deployment specifications.

---

### 1. Project Overview
**YashRaj Systems & Services** is a premium corporate website designed for an industrial engineering and system integration firm based in Pune, India. The website acts as a digital storefront and capability brochure, highlighting the company's expertise in industrial automation, PLC/SCADA/DCS systems, control panels fabrication, and electrical integration. It targets industrial clients (e.g., factories, power plants, refineries) looking for high-reliability automation partners, providing detailed information about services, product portfolios, industrial sectors, corporate partners, and a direct inquiry interface.

---

### 2. Tech Stack
The project is built as a highly optimized, single-page, static frontend application:
- **Core Markup**: HTML5
- **Styling**: Modern CSS3 (featuring HSL/RGBA custom properties, GPU-accelerated marquee loops, and CSS transitions)
- **Interactivity**: Vanilla JavaScript (ES6+, zero third-party framework dependencies)
- **Icons**: FontAwesome v6.4.0 (loaded via CDN link)
- **Typography**: Google Fonts (Space Grotesk for copy/sans-serif headers, Unbounded for bold tech-style headers)
- **Assets**: Locally hosted images (JPEG, PNG, WEBP) and a background MP4 video.

---

### 3. File & Folder Structure
Below is the directory tree of the repository:
```text
YashRaj Systems/
├── css/
│   └── style.css                             # Central stylesheet containing CSS variables, layouts, and animations
├── js/
│   └── main.js                               # Main JS file managing scroll reveals, mobile menus, and counters
├── images/
│   ├── ASSOCIATE'S LOGO/                     # Partner manufacturing brand logos (Siemens, ABB, Wika, etc.)
│   ├── CLIENT LOGO'S/                        # Customer logos (Uttam Energy, Bajaj Power, Mojj Eng, etc.)
│   │   └── UNIQUE LOGO (1).png               # Logo for Unique Industrial Solution (highlighted associate)
│   ├── Background Images/                    # Static page assets
│   ├── about-tech-[1-3].jpg                  # Static images for the About section Who We Are gallery
│   ├── website-qr.png                        # 300x300 QR Code linking to the site's deployment URL
│   └── [service/product-names].jpg           # Localized images for services and products
├── index.html                                # Central HTML document containing all page sections and content
├── yashraj-systems (1).html                  # Original legacy backup document (not active)
├── YASHRAJ LOGO 4.png                        # Official YashRaj logo asset (5:4 PNG with white backing)
└── Connections_internet_connectivity...mp4  # Background loop video for the Hero section header
```

---

### 4. Environment Variables & Configuration
- **Environment Variables**: N/A (Static frontend website with no server-side compilation)
- **API Configuration**: N/A
- **Build Configuration**: None. Deploys directly as static files.

---

### 5. Database Schema & Models
- **Database**: N/A (This is a static frontend website. User inquiries are submitted via a standard HTML mailto or a redirect form action).

---

### 6. API Routes & Endpoints
- **API Endpoints**: N/A
- **Third-Party API Integrations**:
  - **QR Code Generator API** (used during build to generate the QR code asset):
    - URL: `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=https://vikashsingh9999.github.io/YashRaj-Systems/`

---

### 7. Authentication & Authorization Flow
- **Authentication**: N/A (Public promotional website)

---

### 8. Frontend Pages & User Flow
#### Pages/Routes:
- **`index.html` (Home Route: `/`)**:
  - **Hero Section (`#hero`)**: Premium video background with a grid overlay and visual HUD telemetries.
  - **About Section (`#about`)**: Lists Managing Director bio and showcases a static 3-column gallery of industrial engineering.
  - **Services Section (`#services`)**: Displays 9 automation service cards with high-quality local image wrappers.
  - **Products Section (`#products`)**: Displays 12 clean card forms containing **big FontAwesome icons** (`68px x 68px`) with custom crimson glows, titles, and descriptions.
  - **Sectors Section (`#industries`)**: Displays 9 industry icon cards including Power, Steel, Oil, and Cement.
  - **Associates Section (`#associates`)**: Double-row looping marquee showcasing manufacturer partners (e.g., ABB, Siemens, Yokogawa) and the custom-highlighted Unique Industrial Solution logo.
  - **Clients Section (`#clients`)**: Double-row looping marquee showcasing client brands over a solid white background.
  - **Contact Section (`#contact`)**: Includes contact credentials, business address, and an inquiry text form.
  - **Footer**: Incorporates multi-column site links and the QR code for scanning.

#### User Journey:
1. **Landing**: User lands on the landing page, greeted by the background video loops and logo.
2. **Browsing**: User scrolls down or clicks navigation links to jump directly to sections (smooth-scroll enabled).
3. **Product Range Inspection**: Hovering over product range cards scales the custom icons, turning the border red and background solid crimson.
4. **Interaction**: Hovering over partner marquees pauses the scroll track, letting users inspect specific logos.
5. **Enquiry**: Users fill out the contact form or scan the footer QR code to open the site directly on their mobile browsers.

---

### 9. Backend Services & Core Logic
All page interactions are managed client-side in [js/main.js](file:///js/main.js):

#### 1. Header Scrolled Accentuation
Attaches a passive window scroll event listener. Adds the class `.scrolled` when scroll depth is greater than 40px, scaling/centering navbar elements:
```javascript
window.addEventListener('scroll', () => {
    if (window.scrollY > 40) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
}, { passive: true });
```

#### 2. Responsive Mobile Drawer Navigation
Controls mobile menu slide-in transitions by toggling active classes and managing body overflow styling to disable underlying scroll:
```javascript
function openDrawer() {
    mobileDrawer.classList.add('is-open');
    mobileBackdrop.classList.add('is-open');
    document.body.style.overflow = 'hidden';
}
function closeDrawer() {
    mobileDrawer.classList.remove('is-open');
    mobileBackdrop.classList.remove('is-open');
    document.body.style.overflow = '';
}
```

#### 3. Scroll Reveal Animations
Utilizes `IntersectionObserver` to trigger reveal transitions on sections, stats, and cards as they scroll into view:
```javascript
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.08 });
```

---

### 10. Integration Points & External APIs
- **FontAwesome CDN**: Loads stylesheets for vector icons dynamically in the header.
- **Google Fonts CDN**: Links Google stylesheet files for Unbounded and Space Grotesk typographies.

---

### 11. Dashboard Visualizations & Charts
- **HUD telemetry animations**: Static custom dashboards styled via CSS keyframes (`pulse-ring` and `laserScan`).

---

### 12. Report Generation Feature
- **Report Generation**: N/A

---

### 13. Testing Setup
- **Testing**: Manual visual testing and checking CSS breakpoints across desktop, tablet, and mobile (under 768px).

---

### 14. Deployment & DevOps
- **Hosting**: GitHub Pages
- **Deployment URL**: `https://vikashsingh9999.github.io/YashRaj-Systems/`
- **Deployment Command**:
  ```bash
  git add .
  git commit -m "commit-message"
  git push origin main
  ```
  GitHub Actions or native GitHub Pages builds are triggered on every push to the `main` branch.

---

### 15. Known Issues, TODOs, and Future Enhancements
- **TODO**: Hook up the Contact form action attribute to a serverless backend provider (e.g., Formspree, Netlify Forms) to process email entries dynamically.

---

### 16. Summary for AI Context
**YashRaj Systems & Services** is a responsive, single-page website representing an industrial automation and systems integration company. It is built as a pure static frontend site using **HTML5**, **CSS3**, and **Vanilla JavaScript** (no JS frameworks). 

Key design elements are managed in `css/style.css` via CSS variables (incorporating a deep red, maroon, and white tech aesthetic). Major sections include a video-background Hero header (`#hero`), an About section (`#about`) containing Managing Director biography paragraphs, a Services grid (`#services`), a Product Range grid (`#products`) featuring large custom-styled hover-interactive icons, and infinite-scrolling logo marquees for Associates (`#associates`) and Clients (`#clients`). The footer has a custom QR code column displaying `images/website-qr.png`. 

Page transitions, mobile drawer menus, and intersection-reveals are handled entirely in `js/main.js` using browser-native `IntersectionObserver` APIs. The repository is configured to deploy directly to **GitHub Pages** from the `main` branch.
