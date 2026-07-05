// Dashboard JS

async function logout() {
  const fd = new FormData();
  fd.append('action', 'logout');
  await fetch('php/auth.php', { method: 'POST', body: fd });
  window.location.href = 'index.php';
}

async function loadResumes() {
  const grid = document.getElementById('resumeGrid');
  try {
    const res = await fetch('php/resume.php?action=list');
    const data = await res.json();
    const resumes = data.resumes || [];

    if (!resumes.length) {
      grid.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">📄</div>
          <h3>No resumes yet</h3>
          <p>Create your first professional resume to get started</p>
          <button class="btn-primary" onclick="showCreateModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Create Resume
          </button>
        </div>`;
      return;
    }

    grid.innerHTML = resumes.map((r, i) => `
      <div class="resume-card" style="animation-delay: ${i * 0.05}s">
        <div class="resume-card-thumb" onclick="editResume(${r.id})">
          <div class="resume-card-thumb-bg" style="background: linear-gradient(135deg, ${hexToRgba(r.theme_color, 0.1)}, ${hexToRgba(r.theme_color, 0.05)})"></div>
          <div class="resume-card-mini">
            <div class="mini-header" style="background:${r.theme_color}"></div>
            <div class="mini-line"></div>
            <div class="mini-line mini-line-short"></div>
            <div class="mini-line"></div>
            <div class="mini-line mini-line-short"></div>
          </div>
        </div>
        <div class="resume-card-body" onclick="editResume(${r.id})" style="cursor:pointer">
          <div class="resume-card-title">${escHtml(r.title)}</div>
          <div class="resume-card-meta">${formatDate(r.updated_at)} · <span class="resume-card-badge">${ucFirst(r.template)}</span>${r.is_public ? ' · <span style="color:#22c55e;font-size:11px">● Public</span>' : ''}</div>
        </div>
        <div class="resume-card-actions">
          <button class="card-action-btn" onclick="editResume(${r.id})">✏️ Edit</button>
          <button class="card-action-btn" onclick="previewResume(${r.id})">👁 Preview</button>
          <button class="card-action-btn danger" onclick="deleteResume(${r.id}, event)">🗑 Delete</button>
        </div>
      </div>
    `).join('');
  } catch (err) {
    grid.innerHTML = `<div class="empty-state"><p style="color:#ef4444">Error loading resumes. Please ensure database is set up.</p></div>`;
  }
}

function editResume(id) {
  window.location.href = `editor.php?id=${id}`;
}

function previewResume(id) {
  window.open(`preview.php?id=${id}`, '_blank');
}

async function deleteResume(id, e) {
  e.stopPropagation();
  if (!confirm('Delete this resume? This cannot be undone.')) return;

  const fd = new FormData();
  fd.append('action', 'delete');
  fd.append('resume_id', id);

  const res = await fetch('php/resume.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (data.success) {
    showToast('Resume deleted');
    loadResumes();
  }
}

// Create Modal
let selectedTemplate = 'modern';

function showCreateModal() {
  document.getElementById('createOverlay').classList.add('active');
}
function hideCreateModal(e) {
  if (!e || e.target === document.getElementById('createOverlay'))
    document.getElementById('createOverlay').classList.remove('active');
}
function selectTemplate(el) {
  document.querySelectorAll('#templateGrid .template-option').forEach(t => t.classList.remove('selected'));
  el.classList.add('selected');
  selectedTemplate = el.dataset.template;
}

async function createResume() {
  const title = document.getElementById('resumeTitle').value.trim() || 'My Resume';
  const fd = new FormData();
  fd.append('action', 'create');
  fd.append('title', title);
  fd.append('template', selectedTemplate);

  const btn = document.querySelector('.modal-create .btn-submit');
  btn.disabled = true; btn.textContent = 'Creating...';

  const res = await fetch('php/resume.php', { method: 'POST', body: fd });
  const data = await res.json();

  if (data.resume_id) {
    window.location.href = `editor.php?id=${data.resume_id}`;
  } else {
    btn.disabled = false; btn.textContent = 'Create Resume';
    alert('Error creating resume: ' + (data.error || 'Unknown error'));
  }
}

// Utils
function formatDate(dt) {
  return new Date(dt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
function ucFirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
function escHtml(s) {
  const d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}
function hexToRgba(hex, a) {
  const r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${a})`;
}

function showToast(msg, type = 'success') {
  const t = document.getElementById('toast') || createToast();
  t.textContent = msg;
  t.className = `toast ${type} show`;
  setTimeout(() => t.classList.remove('show'), 3000);
}
function createToast() {
  const t = document.createElement('div');
  t.id = 'toast'; t.className = 'toast';
  document.body.appendChild(t);
  return t;
}

loadResumes();
