<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ResumeForge — Build Your Perfect Resume</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/landing.css">
</head>
<body class="landing-page">

<!-- Navigation -->
<nav class="nav">
  <div class="nav-inner">
    <div class="logo">
      <span class="logo-icon">◈</span>
      <span class="logo-text">ResumeForge</span>
    </div>
    <div class="nav-actions">
      <button class="btn-ghost" onclick="showAuth('login')">Sign In</button>
      <button class="btn-primary" onclick="showAuth('register')">Get Started Free</button>
    </div>
  </div>
</nav>

<!-- Hero -->
<section class="hero">
  <div class="hero-bg">
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-grid"></div>
  </div>
  <div class="hero-content">
    <div class="hero-badge">✦ Professional Resume Builder</div>
    <h1 class="hero-title">
      Craft Resumes That<br>
      <span class="hero-highlight">Get You Hired</span>
    </h1>
    <p class="hero-sub">Build stunning, ATS-optimized resumes in minutes. Choose from professional templates, customize every detail, and land your dream job.</p>
    <div class="hero-cta">
      <button class="btn-hero" onclick="showAuth('register')">
        Create Your Resume — It's Free
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </button>
      <span class="hero-note">No credit card required</span>
    </div>
    <div class="hero-stats">
      <div class="stat"><span class="stat-num">10K+</span><span class="stat-label">Resumes Created</span></div>
      <div class="stat-divider"></div>
      <div class="stat"><span class="stat-num">6</span><span class="stat-label">Templates</span></div>
      <div class="stat-divider"></div>
      <div class="stat"><span class="stat-num">Free</span><span class="stat-label">Forever</span></div>
    </div>
  </div>
  <div class="hero-visual">
    <div class="resume-mockup">
      <div class="mockup-header">
        <div class="mockup-avatar"></div>
        <div class="mockup-info">
          <div class="mockup-name"></div>
          <div class="mockup-title"></div>
        </div>
      </div>
      <div class="mockup-body">
        <div class="mockup-section">
          <div class="mockup-section-title"></div>
          <div class="mockup-line"></div>
          <div class="mockup-line mockup-line-short"></div>
          <div class="mockup-line"></div>
        </div>
        <div class="mockup-section">
          <div class="mockup-section-title"></div>
          <div class="mockup-tags">
            <div class="mockup-tag"></div>
            <div class="mockup-tag mockup-tag-sm"></div>
            <div class="mockup-tag"></div>
            <div class="mockup-tag mockup-tag-sm"></div>
          </div>
        </div>
        <div class="mockup-section">
          <div class="mockup-section-title"></div>
          <div class="mockup-line"></div>
          <div class="mockup-line mockup-line-med"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Features -->
<section class="features">
  <div class="container">
    <div class="section-header">
      <div class="section-label">Why ResumeForge</div>
      <h2 class="section-title">Everything You Need to<br>Stand Out</h2>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">⚡</div>
        <h3>Lightning Fast Builder</h3>
        <p>Intuitive drag-and-drop sections. Fill in your details and watch your resume come alive in real time.</p>
      </div>
      <div class="feature-card feature-card-accent">
        <div class="feature-icon">🎨</div>
        <h3>Professional Templates</h3>
        <p>6 beautiful, recruiter-approved templates. Customize colors, fonts, and layouts to match your style.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">📋</div>
        <h3>ATS-Optimized</h3>
        <p>Our templates are designed to pass Applicant Tracking Systems, getting your resume in front of humans.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🔗</div>
        <h3>Public Share Link</h3>
        <p>Generate a unique public URL for your resume. Share it on LinkedIn or send it directly to recruiters.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">💾</div>
        <h3>Multiple Resumes</h3>
        <p>Create different resumes for different roles. Tailor each one to your target position.</p>
      </div>
      <div class="feature-card feature-card-accent">
        <div class="feature-icon">🖨️</div>
        <h3>Print Ready</h3>
        <p>Export or print your resume directly from the browser. Pixel-perfect output every time.</p>
      </div>
    </div>
  </div>
</section>

<!-- Auth Modal -->
<div class="modal-overlay" id="authOverlay" onclick="hideAuth(event)">
  <div class="modal" id="authModal">
    <button class="modal-close" onclick="hideAuth()">×</button>
    
    <!-- Login Form -->
    <div id="loginForm" class="auth-form">
      <div class="auth-logo">◈</div>
      <h2>Welcome back</h2>
      <p>Sign in to your ResumeForge account</p>
      <div id="loginError" class="alert alert-error" style="display:none"></div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" id="loginEmail" placeholder="you@example.com" class="form-input">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" id="loginPassword" placeholder="••••••••" class="form-input">
      </div>
      <button class="btn-submit" onclick="doLogin()">Sign In</button>
      <p class="auth-switch">Don't have an account? <a href="#" onclick="showAuth('register')">Create one free</a></p>
      <p class="demo-hint">Demo: demo@example.com / demo1234</p>
    </div>

    <!-- Register Form -->
    <div id="registerForm" class="auth-form" style="display:none">
      <div class="auth-logo">◈</div>
      <h2>Create account</h2>
      <p>Join thousands building amazing resumes</p>
      <div id="registerError" class="alert alert-error" style="display:none"></div>
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" id="regName" placeholder="John Doe" class="form-input">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" id="regEmail" placeholder="you@example.com" class="form-input">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" id="regPassword" placeholder="Min. 6 characters" class="form-input">
      </div>
      <button class="btn-submit" onclick="doRegister()">Create Free Account</button>
      <p class="auth-switch">Already have an account? <a href="#" onclick="showAuth('login')">Sign in</a></p>
    </div>
  </div>
</div>

<script src="js/auth.js"></script>
<script>
  // Check if already logged in
  fetch('php/auth.php?action=check')
    .then(r => r.json())
    .then(d => { if (d.loggedIn) window.location.href = 'dashboard.php'; });
</script>
</body>
</html>
