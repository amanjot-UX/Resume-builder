<?php
require_once 'php/config.php';
requireLogin();
$resumeId = (int)($_GET['id'] ?? 0);
if (!$resumeId) { header('Location: dashboard.php'); exit; }
// Verify ownership
$db = getDB();
$stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
$stmt->execute([$resumeId, $_SESSION['user_id']]);
$resume = $stmt->fetch();
if (!$resume) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Resume — ResumeForge</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/editor.css">
<link rel="stylesheet" href="css/templates.css">
</head>
<body class="editor-page">

<!-- Editor Top Bar -->
<header class="editor-header">
  <div class="editor-header-left">
    <a href="dashboard.php" class="back-btn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    </a>
    <span class="logo-icon">◈</span>
    <div class="resume-title-edit">
      <input type="text" id="resumeTitle" value="<?= htmlspecialchars($resume['title']) ?>" class="title-input" onblur="saveTitle()">
    </div>
  </div>
  <div class="editor-header-center">
    <div class="section-tabs">
      <button class="sec-tab active" data-section="personal" onclick="switchSection('personal')">Personal</button>
      <button class="sec-tab" data-section="experience" onclick="switchSection('experience')">Experience</button>
      <button class="sec-tab" data-section="education" onclick="switchSection('education')">Education</button>
      <button class="sec-tab" data-section="skills" onclick="switchSection('skills')">Skills</button>
      <button class="sec-tab" data-section="projects" onclick="switchSection('projects')">Projects</button>
      <button class="sec-tab" data-section="certifications" onclick="switchSection('certifications')">Certs</button>
      <button class="sec-tab" data-section="languages" onclick="switchSection('languages')">Languages</button>
    </div>
  </div>
  <div class="editor-header-right">
    <button class="btn-ghost-sm" onclick="showSettings()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"/></svg>
      Settings
    </button>
    <button class="btn-ghost-sm" onclick="togglePublic()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
      Share
    </button>
    <button class="btn-primary-sm" onclick="printResume()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg>
      Print / PDF
    </button>
  </div>
</header>

<div class="editor-layout">
  <!-- Left Panel: Form -->
  <div class="editor-panel" id="editorPanel">

    <!-- Personal Info Section -->
    <div class="section-content active" id="section-personal">
      <div class="section-heading">
        <h3>Personal Information</h3>
        <p>Your contact details and professional summary</p>
      </div>
      <div class="form-grid">
        <div class="form-group full">
          <label>Full Name</label>
          <input type="text" class="form-input" id="p_full_name" placeholder="John Doe">
        </div>
        <div class="form-group full">
          <label>Professional Title</label>
          <input type="text" class="form-input" id="p_job_title" placeholder="Senior Software Engineer">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" class="form-input" id="p_email" placeholder="john@example.com">
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="text" class="form-input" id="p_phone" placeholder="+1 (555) 000-0000">
        </div>
        <div class="form-group">
          <label>Location</label>
          <input type="text" class="form-input" id="p_location" placeholder="New York, NY">
        </div>
        <div class="form-group">
          <label>Website</label>
          <input type="text" class="form-input" id="p_website" placeholder="https://yoursite.com">
        </div>
        <div class="form-group">
          <label>LinkedIn</label>
          <input type="text" class="form-input" id="p_linkedin" placeholder="linkedin.com/in/johndoe">
        </div>
        <div class="form-group">
          <label>GitHub</label>
          <input type="text" class="form-input" id="p_github" placeholder="github.com/johndoe">
        </div>
        <div class="form-group full">
          <label>Professional Summary</label>
          <textarea class="form-input form-textarea" id="p_summary" rows="4" placeholder="Brief overview of your professional background, key skills, and career goals..."></textarea>
        </div>
      </div>
      <button class="btn-save" onclick="savePersonal()">Save Personal Info</button>
    </div>

    <!-- Experience Section -->
    <div class="section-content" id="section-experience">
      <div class="section-heading">
        <h3>Work Experience</h3>
        <p>Your professional work history</p>
      </div>
      <div id="experience-list"></div>
      <button class="btn-add" onclick="showAddItem('experience')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Add Experience
      </button>
    </div>

    <!-- Education Section -->
    <div class="section-content" id="section-education">
      <div class="section-heading">
        <h3>Education</h3>
        <p>Your academic background</p>
      </div>
      <div id="education-list"></div>
      <button class="btn-add" onclick="showAddItem('education')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Add Education
      </button>
    </div>

    <!-- Skills Section -->
    <div class="section-content" id="section-skills">
      <div class="section-heading">
        <h3>Skills</h3>
        <p>Technical and soft skills</p>
      </div>
      <div id="skills-list"></div>
      <button class="btn-add" onclick="showAddItem('skills')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Add Skill
      </button>
    </div>

    <!-- Projects Section -->
    <div class="section-content" id="section-projects">
      <div class="section-heading">
        <h3>Projects</h3>
        <p>Notable projects and work samples</p>
      </div>
      <div id="projects-list"></div>
      <button class="btn-add" onclick="showAddItem('projects')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Add Project
      </button>
    </div>

    <!-- Certifications Section -->
    <div class="section-content" id="section-certifications">
      <div class="section-heading">
        <h3>Certifications</h3>
        <p>Professional certifications and credentials</p>
      </div>
      <div id="certifications-list"></div>
      <button class="btn-add" onclick="showAddItem('certifications')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Add Certification
      </button>
    </div>

    <!-- Languages Section -->
    <div class="section-content" id="section-languages">
      <div class="section-heading">
        <h3>Languages</h3>
        <p>Languages you speak</p>
      </div>
      <div id="languages-list"></div>
      <button class="btn-add" onclick="showAddItem('languages')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Add Language
      </button>
    </div>

  </div>

  <!-- Right Panel: Preview -->
  <div class="preview-panel" id="previewPanel">
    <div class="preview-controls">
      <span class="preview-label">Live Preview</span>
      <div class="zoom-controls">
        <button onclick="zoomOut()">−</button>
        <span id="zoomLevel">85%</span>
        <button onclick="zoomIn()">+</button>
      </div>
    </div>
    <div class="preview-wrapper" id="previewWrapper">
      <div class="preview-page" id="resumePreview">
        <!-- Resume preview rendered here by JS -->
      </div>
    </div>
  </div>
</div>

<!-- Settings Modal -->
<div class="modal-overlay" id="settingsOverlay" onclick="hideSettings(event)">
  <div class="modal modal-settings">
    <button class="modal-close" onclick="hideSettings()">×</button>
    <h2>Resume Settings</h2>

    <div class="form-group">
      <label>Template</label>
      <div class="template-grid-sm" id="settingsTemplateGrid">
        <?php foreach(['modern','classic','minimal','executive','creative','compact'] as $t): ?>
        <div class="template-option-sm" data-template="<?= $t ?>" onclick="selectSettingsTemplate(this)">
          <div class="template-preview template-<?= $t ?>">
            <div class="tp-header"></div>
          </div>
          <span><?= ucfirst($t) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-group">
      <label>Theme Color</label>
      <div class="color-grid">
        <button class="color-swatch" data-color="#6366f1" style="background:#6366f1" onclick="selectColor(this)"></button>
        <button class="color-swatch" data-color="#0ea5e9" style="background:#0ea5e9" onclick="selectColor(this)"></button>
        <button class="color-swatch" data-color="#10b981" style="background:#10b981" onclick="selectColor(this)"></button>
        <button class="color-swatch" data-color="#f59e0b" style="background:#f59e0b" onclick="selectColor(this)"></button>
        <button class="color-swatch" data-color="#ef4444" style="background:#ef4444" onclick="selectColor(this)"></button>
        <button class="color-swatch" data-color="#8b5cf6" style="background:#8b5cf6" onclick="selectColor(this)"></button>
        <button class="color-swatch" data-color="#ec4899" style="background:#ec4899" onclick="selectColor(this)"></button>
        <button class="color-swatch" data-color="#1e293b" style="background:#1e293b" onclick="selectColor(this)"></button>
        <input type="color" id="customColor" class="color-custom" onchange="selectCustomColor(this.value)">
      </div>
    </div>

    <button class="btn-submit" onclick="saveSettings()">Save Settings</button>
  </div>
</div>

<!-- Share Modal -->
<div class="modal-overlay" id="shareOverlay" onclick="hideShare(event)">
  <div class="modal modal-share">
    <button class="modal-close" onclick="hideShare()">×</button>
    <h2>Share Your Resume</h2>
    <div id="shareContent">
      <p>Make your resume publicly accessible with a unique link.</p>
      <div id="shareStatus"></div>
    </div>
  </div>
</div>

<!-- Item Add/Edit Modal -->
<div class="modal-overlay" id="itemOverlay" onclick="hideItemModal(event)">
  <div class="modal modal-item">
    <button class="modal-close" onclick="hideItemModal()">×</button>
    <h2 id="itemModalTitle">Add Item</h2>
    <div id="itemFormContainer"></div>
  </div>
</div>

<!-- Toast notification -->
<div id="toast" class="toast"></div>

<script>
  const RESUME_ID = <?= $resumeId ?>;
  const RESUME_TEMPLATE = '<?= htmlspecialchars($resume['template']) ?>';
  const RESUME_COLOR = '<?= htmlspecialchars($resume['theme_color']) ?>';
  const IS_PUBLIC = <?= $resume['is_public'] ? 'true' : 'false' ?>;
  const PUBLIC_SLUG = '<?= htmlspecialchars($resume['public_slug'] ?? '') ?>';
</script>
<script src="js/editor.js"></script>
<script src="js/preview.js"></script>
<script src="js/items.js"></script>
</body>
</html>
