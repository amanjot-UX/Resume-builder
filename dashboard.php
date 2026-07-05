<?php
require_once 'php/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — ResumeForge</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="app-page">

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <span class="logo-icon">◈</span>
    <span class="logo-text">ResumeForge</span>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item active">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      My Resumes
    </a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
      <div class="user-details">
        <div class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
        <div class="user-email"><?= htmlspecialchars($_SESSION['user_email']) ?></div>
      </div>
    </div>
    <button class="btn-logout" onclick="logout()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
      Sign Out
    </button>
  </div>
</aside>

<!-- Main Content -->
<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title">My Resumes</h1>
      <p class="page-sub">Create and manage your professional resumes</p>
    </div>
    <button class="btn-primary" onclick="showCreateModal()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
      New Resume
    </button>
  </div>

  <div id="resumeGrid" class="resume-grid">
    <div class="loading-state">
      <div class="spinner"></div>
      <p>Loading your resumes...</p>
    </div>
  </div>
</main>

<!-- Create Resume Modal -->
<div class="modal-overlay" id="createOverlay" onclick="hideCreateModal(event)">
  <div class="modal modal-create">
    <button class="modal-close" onclick="hideCreateModal()">×</button>
    <h2>Create New Resume</h2>
    <p>Choose a starting template for your resume</p>

    <div class="form-group">
      <label>Resume Title</label>
      <input type="text" id="resumeTitle" class="form-input" placeholder="e.g. Software Engineer Resume" value="My Resume">
    </div>

    <div class="form-group">
      <label>Choose Template</label>
      <div class="template-grid" id="templateGrid">
        <div class="template-option selected" data-template="modern" onclick="selectTemplate(this)">
          <div class="template-preview template-modern">
            <div class="tp-header"></div>
            <div class="tp-body"><div class="tp-line"></div><div class="tp-line tp-short"></div></div>
          </div>
          <span>Modern</span>
        </div>
        <div class="template-option" data-template="classic" onclick="selectTemplate(this)">
          <div class="template-preview template-classic">
            <div class="tp-sidebar"></div>
            <div class="tp-main"><div class="tp-line"></div><div class="tp-line tp-short"></div></div>
          </div>
          <span>Classic</span>
        </div>
        <div class="template-option" data-template="minimal" onclick="selectTemplate(this)">
          <div class="template-preview template-minimal">
            <div class="tp-top"></div>
            <div class="tp-body"><div class="tp-line"></div><div class="tp-line"></div></div>
          </div>
          <span>Minimal</span>
        </div>
        <div class="template-option" data-template="executive" onclick="selectTemplate(this)">
          <div class="template-preview template-executive">
            <div class="tp-banner"></div>
            <div class="tp-body"><div class="tp-line"></div><div class="tp-line tp-short"></div></div>
          </div>
          <span>Executive</span>
        </div>
        <div class="template-option" data-template="creative" onclick="selectTemplate(this)">
          <div class="template-preview template-creative">
            <div class="tp-accent"></div>
            <div class="tp-main-cr"><div class="tp-line"></div><div class="tp-line tp-short"></div></div>
          </div>
          <span>Creative</span>
        </div>
        <div class="template-option" data-template="compact" onclick="selectTemplate(this)">
          <div class="template-preview template-compact">
            <div class="tp-compact-h"></div>
            <div class="tp-two-col"><div class="tp-col"></div><div class="tp-col"></div></div>
          </div>
          <span>Compact</span>
        </div>
      </div>
    </div>

    <button class="btn-submit" onclick="createResume()">Create Resume</button>
  </div>
</div>

<script src="js/dashboard.js"></script>
</body>
</html>
