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

// Load all resume data
$sections = ['personal_info', 'education', 'experience', 'skills', 'projects', 'certifications', 'languages'];
$data = ['resume' => $resume];
foreach ($sections as $table) {
    $key   = $table === 'personal_info' ? 'personal' : $table;
    $order = $table === 'personal_info' ? '' : ' ORDER BY sort_order ASC, id ASC';
    $stmt  = $db->prepare("SELECT * FROM $table WHERE resume_id = ?$order");
    $stmt->execute([$resumeId]);
    $data[$key] = $table === 'personal_info' ? $stmt->fetch() : $stmt->fetchAll();
}
$p = $data['personal'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($resume['title']) ?> — Preview · ResumeForge</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/dashboard.css">
<link rel="stylesheet" href="css/templates.css">
<style>
/* ── Preview Page Layout ─────────────────────────── */
.preview-page-layout {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: var(--bg);
}

/* ── Sidebar (reuse dashboard sidebar) ───────────── */
/* already defined in dashboard.css */

/* ── Right: full preview area ───────────────────── */
.preview-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: var(--bg);
}

/* ── Top bar inside preview area ────────────────── */
.preview-topbar {
  height: 56px;
  flex-shrink: 0;
  background: var(--bg-panel);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  gap: 16px;
  z-index: 10;
}

.preview-topbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.preview-topbar-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 280px;
}

.preview-topbar-meta {
  font-size: 12px;
  color: var(--text-muted);
}

.preview-topbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.preview-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: rgba(99,102,241,0.1);
  color: var(--accent-light);
  border: 1px solid rgba(99,102,241,0.2);
  font-size: 11px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 100px;
  text-transform: capitalize;
}

.preview-public-dot {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  color: var(--success);
  font-weight: 500;
}
.preview-public-dot::before {
  content: '';
  display: inline-block;
  width: 7px; height: 7px;
  background: var(--success);
  border-radius: 50%;
}

/* ── Zoom Controls ───────────────────────────────── */
.zoom-controls {
  display: flex;
  align-items: center;
  gap: 4px;
  background: var(--bg-input);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 3px 6px;
}

.zoom-btn {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  width: 26px; height: 26px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 4px;
  font-size: 16px;
  transition: var(--transition);
}
.zoom-btn:hover { background: var(--border); color: var(--text-primary); }

.zoom-level {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
  min-width: 38px;
  text-align: center;
}

/* ── Canvas / scroll area ────────────────────────── */
.preview-canvas {
  flex: 1;
  overflow: auto;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 40px 32px;
  background: #1c1c28;
  background-image:
    radial-gradient(circle at 20% 80%, rgba(99,102,241,0.04) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(139,92,246,0.03) 0%, transparent 50%);
}

/* ── Resume paper ────────────────────────────────── */
.resume-paper-wrap {
  transform-origin: top center;
  transition: transform 0.2s ease;
}

.resume-paper {
  width: 794px;
  min-height: 1123px;
  background: #ffffff;
  box-shadow:
    0 2px 4px rgba(0,0,0,0.3),
    0 12px 48px rgba(0,0,0,0.5),
    0 32px 80px rgba(0,0,0,0.3);
  border-radius: 2px;
  overflow: hidden;
  position: relative;
}

/* ── Loading / error states ─────────────────────── */
.preview-loading {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.95);
  z-index: 5;
  gap: 12px;
  color: #475569;
  font-family: 'DM Sans', sans-serif;
}

/* ── Bottom info bar ─────────────────────────────── */
.preview-infobar {
  height: 36px;
  flex-shrink: 0;
  background: var(--bg-panel);
  border-top: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  font-size: 11px;
  color: var(--text-muted);
}

.infobar-left { display: flex; align-items: center; gap: 16px; }
.infobar-right { display: flex; align-items: center; gap: 8px; }

/* ── Divider ─────────────────────────────────────── */
.topbar-divider {
  width: 1px;
  height: 20px;
  background: var(--border);
  flex-shrink: 0;
}

/* ── Share popover ───────────────────────────────── */
.share-popover {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: var(--bg-panel);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px;
  width: 340px;
  box-shadow: var(--shadow-lg);
  z-index: 200;
  opacity: 0;
  pointer-events: none;
  transform: translateY(-6px);
  transition: all 0.18s ease;
}
.share-popover.open {
  opacity: 1;
  pointer-events: all;
  transform: translateY(0);
}

.share-popover h4 {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 4px;
  color: var(--text-primary);
}
.share-popover p {
  font-size: 12px;
  color: var(--text-secondary);
  margin-bottom: 14px;
}

.share-toggle-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
  padding: 10px 12px;
  background: var(--bg-input);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
}
.share-toggle-label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-primary);
}

/* Toggle switch */
.toggle-switch {
  position: relative;
  width: 38px; height: 22px;
  display: inline-block;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
  position: absolute;
  inset: 0;
  background: var(--border);
  border-radius: 22px;
  cursor: pointer;
  transition: var(--transition);
}
.toggle-slider::before {
  content: '';
  position: absolute;
  width: 16px; height: 16px;
  left: 3px; bottom: 3px;
  background: white;
  border-radius: 50%;
  transition: var(--transition);
}
input:checked + .toggle-slider { background: var(--accent); }
input:checked + .toggle-slider::before { transform: translateX(16px); }

.share-url-box {
  display: flex;
  gap: 6px;
  margin-bottom: 4px;
}
.share-url-input {
  flex: 1;
  background: var(--bg-input);
  border: 1px solid var(--border);
  color: var(--text-secondary);
  padding: 7px 10px;
  border-radius: var(--radius-sm);
  font-family: var(--font-body);
  font-size: 12px;
  outline: none;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.share-url-input.disabled { opacity: 0.4; pointer-events: none; }

.btn-copy {
  padding: 7px 14px;
  background: var(--accent);
  color: white;
  border: none;
  cursor: pointer;
  border-radius: var(--radius-sm);
  font-family: var(--font-body);
  font-size: 12px;
  font-weight: 600;
  transition: var(--transition);
  white-space: nowrap;
}
.btn-copy:hover { background: var(--accent-light); }
.btn-copy.disabled { opacity: 0.4; pointer-events: none; }

/* ── Relative anchor for share btn ──────────────── */
.share-anchor { position: relative; }

/* ── Keyboard shortcut hints ─────────────────────── */
.kbd {
  display: inline-flex;
  align-items: center;
  background: var(--bg-input);
  border: 1px solid var(--border);
  border-radius: 4px;
  font-size: 10px;
  padding: 1px 5px;
  font-family: monospace;
  color: var(--text-muted);
}

@media (max-width: 900px) {
  .preview-topbar-meta,
  .zoom-controls,
  .preview-infobar { display: none; }
  .preview-canvas { padding: 20px 12px; }
}
</style>
</head>
<body class="preview-page-layout">

<!-- ══════════════════════════════════
     SIDEBAR  (mirrors dashboard.php)
══════════════════════════════════ -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <span class="logo-icon">◈</span>
    <span class="logo-text">ResumeForge</span>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
        <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
      </svg>
      My Resumes
    </a>
    <a href="editor.php?id=<?= $resumeId ?>" class="nav-item">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
      </svg>
      Edit Resume
    </a>
    <a href="#" class="nav-item active">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
      </svg>
      Preview
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
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
      </svg>
      Sign Out
    </button>
  </div>
</aside>

<!-- ══════════════════════════════════
     MAIN PREVIEW AREA
══════════════════════════════════ -->
<div class="preview-area">

  <!-- Top bar -->
  <div class="preview-topbar">

    <!-- Left: back + title -->
    <div class="preview-topbar-left">
      <a href="dashboard.php" class="back-btn" title="Back to Dashboard">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
      </a>
      <div>
        <div class="preview-topbar-title"><?= htmlspecialchars($resume['title']) ?></div>
        <div class="preview-topbar-meta">
          <span class="preview-badge"><?= htmlspecialchars(ucfirst($resume['template'])) ?></span>
          <?php if ($resume['is_public']): ?>
            &nbsp;<span class="preview-public-dot">Public</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right: zoom + actions -->
    <div class="preview-topbar-right">

      <!-- Zoom -->
      <div class="zoom-controls">
        <button class="zoom-btn" onclick="changeZoom(-0.1)" title="Zoom out (-)">−</button>
        <span class="zoom-level" id="zoomLabel">100%</span>
        <button class="zoom-btn" onclick="changeZoom(0.1)" title="Zoom in (+)">+</button>
        <button class="zoom-btn" onclick="resetZoom()" title="Reset zoom">↺</button>
      </div>

      <div class="topbar-divider"></div>

      <!-- Edit -->
      <a href="editor.php?id=<?= $resumeId ?>" class="btn-ghost-sm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
          <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
        </svg>
        Edit
      </a>

      <!-- Print / Download -->
      <button class="btn-ghost-sm" onclick="printResume()" title="Print (Ctrl+P)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="6 9 6 2 18 2 18 9"/>
          <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
          <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Print / PDF
      </button>

      <!-- Share -->
      <div class="share-anchor">
        <button class="btn-primary" id="shareBtn" onclick="toggleShare(event)">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
          </svg>
          Share
        </button>

        <!-- Share popover -->
        <div class="share-popover" id="sharePopover">
          <h4>Share this Resume</h4>
          <p>Toggle public access and copy the shareable link.</p>

          <div class="share-toggle-row">
            <span class="share-toggle-label">
              <?= $resume['is_public'] ? '🔓 Public link active' : '🔒 Link sharing off' ?>
            </span>
            <label class="toggle-switch">
              <input type="checkbox" id="publicToggle"
                     <?= $resume['is_public'] ? 'checked' : '' ?>
                     onchange="togglePublic(this)">
              <span class="toggle-slider"></span>
            </label>
          </div>

          <div class="share-url-box">
            <input type="text" class="share-url-input <?= $resume['is_public'] ? '' : 'disabled' ?>"
                   id="shareUrlInput"
                   value="<?= $resume['is_public'] ? htmlspecialchars(APP_URL . '/view.php?slug=' . $resume['public_slug']) : 'Enable sharing to get a link' ?>"
                   readonly>
            <button class="btn-copy <?= $resume['is_public'] ? '' : 'disabled' ?>"
                    id="copyBtn"
                    onclick="copyShareUrl()">Copy</button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Canvas / paper -->
  <div class="preview-canvas" id="previewCanvas">
    <div class="resume-paper-wrap" id="paperWrap">
      <div class="resume-paper" id="resumeEl"
           style="--theme-color: <?= htmlspecialchars($resume['theme_color']) ?>">
        <!-- Loading state shown until JS renders -->
        <div class="preview-loading" id="previewLoading">
          <div class="spinner"></div>
          <span style="font-size:13px;color:#94a3b8">Rendering preview…</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Info bar -->
  <div class="preview-infobar">
    <div class="infobar-left">
      <span>A4 · 794 × 1123 px</span>
      <span>Template: <strong style="color:var(--text-secondary)"><?= htmlspecialchars(ucfirst($resume['template'])) ?></strong></span>
      <span>Color: <strong style="color:<?= htmlspecialchars($resume['theme_color']) ?>"><?= htmlspecialchars($resume['theme_color']) ?></strong></span>
    </div>
    <div class="infobar-right">
      <span class="kbd">Ctrl</span>&nbsp;<span class="kbd">P</span>&nbsp;to print
      &nbsp;·&nbsp;
      <span class="kbd">−</span>&nbsp;<span class="kbd">+</span>&nbsp;to zoom
    </div>
  </div>

</div><!-- /.preview-area -->

<!-- Toast -->
<div id="toast" class="toast"></div>

<!-- ══════════════════════════════════
     DATA + SCRIPTS
══════════════════════════════════ -->
<script>
// Resume data from PHP
const resumeData    = <?= json_encode($data) ?>;
const currentTemplate = <?= json_encode($resume['template']) ?>;
const currentColor    = <?= json_encode($resume['theme_color']) ?>;
const resumeId        = <?= (int)$resumeId ?>;
</script>
<script src="js/preview.js"></script>
<script>
/* ─── Render ────────────────────────────────────────── */
window.addEventListener('load', function () {
  const el      = document.getElementById('resumeEl');
  const loading = document.getElementById('previewLoading');
  const p       = resumeData.personal || {};

  el.style.setProperty('--theme-color', currentColor);
  el.className = 'resume-paper template-' + currentTemplate;

  let html = '';
  switch (currentTemplate) {
    case 'modern':    html = renderModern(resumeData, p, currentColor);    break;
    case 'classic':   html = renderClassic(resumeData, p, currentColor);   break;
    case 'minimal':   html = renderMinimal(resumeData, p, currentColor);   break;
    case 'executive': html = renderExecutive(resumeData, p, currentColor); break;
    case 'creative':  html = renderCreative(resumeData, p, currentColor);  break;
    case 'compact':   html = renderCompact(resumeData, p, currentColor);   break;
    default:          html = renderModern(resumeData, p, currentColor);
  }

  // Remove loading overlay, inject HTML
  if (loading) loading.remove();
  el.insertAdjacentHTML('beforeend', html);
});

/* ─── Zoom ──────────────────────────────────────────── */
let zoomLevel = 1.0;
const MIN_ZOOM = 0.3;
const MAX_ZOOM = 2.0;

function applyZoom() {
  document.getElementById('paperWrap').style.transform = `scale(${zoomLevel})`;
  document.getElementById('zoomLabel').textContent = Math.round(zoomLevel * 100) + '%';
}
function changeZoom(delta) {
  zoomLevel = Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, +(zoomLevel + delta).toFixed(1)));
  applyZoom();
}
function resetZoom() {
  zoomLevel = 1.0;
  applyZoom();
}

// Auto-fit on load
window.addEventListener('load', function () {
  const canvas = document.getElementById('previewCanvas');
  const paperW = 794 + 80; // paper + padding
  if (canvas.offsetWidth < paperW) {
    const fit = +((canvas.offsetWidth / paperW).toFixed(2));
    zoomLevel  = Math.max(0.4, fit);
    applyZoom();
  }
});

// Keyboard zoom
document.addEventListener('keydown', function (e) {
  if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '=')) { e.preventDefault(); changeZoom(0.1); }
  if ((e.ctrlKey || e.metaKey) && e.key === '-')                     { e.preventDefault(); changeZoom(-0.1); }
  if ((e.ctrlKey || e.metaKey) && e.key === '0')                     { e.preventDefault(); resetZoom(); }
});

/* ─── Print ─────────────────────────────────────────── */
function printResume() {
  window.print();
}

/* ─── Share popover ─────────────────────────────────── */
function toggleShare(e) {
  e.stopPropagation();
  document.getElementById('sharePopover').classList.toggle('open');
}
document.addEventListener('click', function (e) {
  const pop = document.getElementById('sharePopover');
  if (!e.target.closest('.share-anchor')) pop.classList.remove('open');
});

/* ─── Toggle public ──────────────────────────────────── */
async function togglePublic(checkbox) {
  const fd = new FormData();
  fd.append('action',    'toggle_public');
  fd.append('resume_id', resumeId);

  const res  = await fetch('php/resume.php', { method: 'POST', body: fd });
  const data = await res.json();

  const urlInput = document.getElementById('shareUrlInput');
  const copyBtn  = document.getElementById('copyBtn');

  if (data.is_public) {
    urlInput.value = data.public_url || window.location.origin + '/view.php?slug=' + data.slug;
    urlInput.classList.remove('disabled');
    copyBtn.classList.remove('disabled');
    showToast('Resume is now public');
  } else {
    urlInput.value = 'Enable sharing to get a link';
    urlInput.classList.add('disabled');
    copyBtn.classList.add('disabled');
    showToast('Resume is now private');
  }
}

/* ─── Copy share URL ────────────────────────────────── */
function copyShareUrl() {
  const val = document.getElementById('shareUrlInput').value;
  if (!val || val.startsWith('Enable')) return;
  navigator.clipboard.writeText(val).then(() => {
    showToast('Link copied to clipboard!');
    const btn = document.getElementById('copyBtn');
    const orig = btn.textContent;
    btn.textContent = '✓ Copied';
    setTimeout(() => btn.textContent = orig, 1800);
  });
}

/* ─── Auth / logout ─────────────────────────────────── */
async function logout() {
  const fd = new FormData();
  fd.append('action', 'logout');
  await fetch('php/auth.php', { method: 'POST', body: fd });
  window.location.href = 'index.php';
}

/* ─── Toast ─────────────────────────────────────────── */
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `toast ${type} show`;
  setTimeout(() => t.classList.remove('show'), 3000);
}

/* ─── Print styles (injected dynamically) ───────────── */
const printStyle = document.createElement('style');
printStyle.textContent = `
  @media print {
    body, .preview-page-layout { display: block !important; }
    .sidebar, .preview-topbar, .preview-infobar { display: none !important; }
    .preview-area { display: block !important; }
    .preview-canvas {
      padding: 0 !important;
      background: white !important;
      display: block !important;
    }
    .resume-paper-wrap { transform: none !important; }
    .resume-paper {
      width: 100% !important;
      box-shadow: none !important;
      border-radius: 0 !important;
    }
  }
`;
document.head.appendChild(printStyle);
</script>
</body>
</html>
