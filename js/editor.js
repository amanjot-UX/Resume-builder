// Editor JS — Main Controller

let currentSection = 'personal';
let currentTemplate = RESUME_TEMPLATE;
let currentColor = RESUME_COLOR;
let zoomScale = 0.85;
let resumeData = {};

// Init
window.addEventListener('load', () => {
  loadFullResume();
  applyZoom();
  initSettings();
});

// Section switching
function switchSection(section) {
  currentSection = section;
  document.querySelectorAll('.sec-tab').forEach(t => t.classList.remove('active'));
  document.querySelector(`[data-section="${section}"]`).classList.add('active');
  document.querySelectorAll('.section-content').forEach(s => s.classList.remove('active'));
  document.getElementById(`section-${section}`).classList.add('active');

  if (section !== 'personal') {
    loadSectionItems(section);
  }
}

// Load full resume
async function loadFullResume() {
  try {
    const res = await fetch(`php/resume.php?action=get_full&resume_id=${RESUME_ID}`);
    const data = await res.json();
    if (data.error) { window.location.href = 'dashboard.php'; return; }

    resumeData = data;
    fillPersonalForm(data.personal || {});
    renderPreview();
  } catch (e) {
    showToast('Error loading resume', 'error');
  }
}

// Fill personal form
function fillPersonalForm(p) {
  const fields = ['full_name','job_title','email','phone','location','website','linkedin','github','summary'];
  fields.forEach(f => {
    const el = document.getElementById(`p_${f}`);
    if (el) el.value = p[f] || '';
  });
}

// Save personal info
async function savePersonal() {
  const fields = ['full_name','job_title','email','phone','location','website','linkedin','github','summary'];
  const fd = new FormData();
  fd.append('action', 'save_section');
  fd.append('resume_id', RESUME_ID);
  fd.append('section', 'personal');
  fields.forEach(f => {
    const el = document.getElementById(`p_${f}`);
    if (el) fd.append(f, el.value);
  });

  const btn = document.querySelector('#section-personal .btn-save');
  btn.textContent = 'Saving...'; btn.disabled = true;

  try {
    const res = await fetch('php/resume.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      // Update resumeData
      const personal = resumeData.personal || {};
      fields.forEach(f => {
        const el = document.getElementById(`p_${f}`);
        if (el) personal[f] = el.value;
      });
      resumeData.personal = personal;
      renderPreview();
      showToast('Personal info saved!');
    } else {
      showToast(data.error || 'Save failed', 'error');
    }
  } finally {
    btn.textContent = 'Save Personal Info'; btn.disabled = false;
  }
}

// Load section items
async function loadSectionItems(section) {
  const listEl = document.getElementById(`${section}-list`);
  if (!listEl) return;
  listEl.innerHTML = '<div style="color:var(--text-muted);font-size:13px;padding:8px 0">Loading...</div>';

  const res = await fetch(`php/resume.php?action=get_section&resume_id=${RESUME_ID}&section=${section}`);
  const data = await res.json();
  resumeData[section] = data.data || [];
  renderSectionList(section, resumeData[section]);
  renderPreview();
}

function renderSectionList(section, items) {
  const listEl = document.getElementById(`${section}-list`);
  if (!listEl) return;

  if (!items || !items.length) {
    listEl.innerHTML = '<div style="color:var(--text-muted);font-size:13px;padding:8px 0">No items yet. Add your first one below.</div>';
    return;
  }

  listEl.innerHTML = items.map(item => {
    const title = getItemTitle(section, item);
    const sub = getItemSub(section, item);
    return `
      <div class="item-card" id="item-${section}-${item.id}">
        <div class="item-card-header">
          <div>
            <div class="item-card-title">${escHtml(title)}</div>
            ${sub ? `<div class="item-card-sub">${escHtml(sub)}</div>` : ''}
          </div>
          <div class="item-card-actions">
            <button class="item-btn" onclick="editItem('${section}', ${item.id})" title="Edit">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button class="item-btn delete" onclick="deleteItem('${section}', ${item.id})" title="Delete">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
            </button>
          </div>
        </div>
      </div>`;
  }).join('');
}

function getItemTitle(section, item) {
  switch(section) {
    case 'experience': return item.position || item.company || 'Position';
    case 'education': return item.degree || item.institution || 'Degree';
    case 'skills': return item.name || 'Skill';
    case 'projects': return item.name || 'Project';
    case 'certifications': return item.name || 'Certification';
    case 'languages': return item.name || 'Language';
    default: return 'Item';
  }
}

function getItemSub(section, item) {
  switch(section) {
    case 'experience': return item.company + (item.start_date ? ` · ${item.start_date}–${item.current ? 'Present' : item.end_date}` : '');
    case 'education': return item.institution + (item.start_date ? ` · ${item.start_date}–${item.current ? 'Present' : item.end_date}` : '');
    case 'skills': return `${item.category || 'Technical'} · Level ${item.level}/5`;
    case 'projects': return item.role || '';
    case 'certifications': return item.issuer || '';
    case 'languages': return item.proficiency || '';
    default: return '';
  }
}

// Delete item
async function deleteItem(section, itemId) {
  if (!confirm('Delete this item?')) return;

  const fd = new FormData();
  fd.append('action', 'delete_item');
  fd.append('resume_id', RESUME_ID);
  fd.append('section', section);
  fd.append('item_id', itemId);

  const res = await fetch('php/resume.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (data.success) {
    showToast('Item deleted');
    loadSectionItems(section);
  }
}

// Edit item
function editItem(section, itemId) {
  const items = resumeData[section] || [];
  const item = items.find(i => i.id == itemId);
  if (!item) return;
  showItemModal(section, item);
}

// Zoom controls
function zoomIn() {
  zoomScale = Math.min(zoomScale + 0.1, 1.5);
  applyZoom();
}
function zoomOut() {
  zoomScale = Math.max(zoomScale - 0.1, 0.4);
  applyZoom();
}
function applyZoom() {
  document.getElementById('resumePreview').style.transform = `scale(${zoomScale})`;
  document.getElementById('zoomLevel').textContent = Math.round(zoomScale * 100) + '%';
}

// Title save
async function saveTitle() {
  const title = document.getElementById('resumeTitle').value.trim() || 'My Resume';
  const fd = new FormData();
  fd.append('action', 'update_settings');
  fd.append('resume_id', RESUME_ID);
  fd.append('title', title);
  fd.append('template', currentTemplate);
  fd.append('theme_color', currentColor);
  await fetch('php/resume.php', { method: 'POST', body: fd });
}

// Settings modal
function showSettings() {
  document.getElementById('settingsOverlay').classList.add('active');
}
function hideSettings(e) {
  if (!e || e.target === document.getElementById('settingsOverlay'))
    document.getElementById('settingsOverlay').classList.remove('active');
}
function initSettings() {
  // Select current template
  document.querySelectorAll('#settingsTemplateGrid .template-option-sm').forEach(el => {
    el.classList.toggle('selected', el.dataset.template === currentTemplate);
  });
  // Select current color
  document.querySelectorAll('.color-swatch').forEach(el => {
    el.classList.toggle('selected', el.dataset.color === currentColor);
  });
}
function selectSettingsTemplate(el) {
  document.querySelectorAll('#settingsTemplateGrid .template-option-sm').forEach(t => t.classList.remove('selected'));
  el.classList.add('selected');
  currentTemplate = el.dataset.template;
  renderPreview();
}
function selectColor(el) {
  document.querySelectorAll('.color-swatch').forEach(t => t.classList.remove('selected'));
  el.classList.add('selected');
  currentColor = el.dataset.color;
  renderPreview();
}
function selectCustomColor(val) {
  document.querySelectorAll('.color-swatch').forEach(t => t.classList.remove('selected'));
  currentColor = val;
  renderPreview();
}
async function saveSettings() {
  const title = document.getElementById('resumeTitle').value.trim() || 'My Resume';
  const fd = new FormData();
  fd.append('action', 'update_settings');
  fd.append('resume_id', RESUME_ID);
  fd.append('title', title);
  fd.append('template', currentTemplate);
  fd.append('theme_color', currentColor);
  await fetch('php/resume.php', { method: 'POST', body: fd });
  hideSettings();
  showToast('Settings saved!');
}

// Share modal
async function togglePublic() {
  document.getElementById('shareOverlay').classList.add('active');
  const statusEl = document.getElementById('shareStatus');

  const fd = new FormData();
  fd.append('action', 'toggle_public');
  fd.append('resume_id', RESUME_ID);

  const res = await fetch('php/resume.php', { method: 'POST', body: fd });
  const data = await res.json();

  if (data.is_public) {
    const url = `${window.location.origin}/${window.location.pathname.split('/').slice(0,-1).join('/')}/view.php?slug=${data.slug}`;
    statusEl.innerHTML = `
      <div style="color:var(--success);margin-bottom:12px;font-weight:600">✓ Resume is now public</div>
      <div class="share-link-box">
        <input type="text" value="${url}" id="shareUrl" readonly>
        <button onclick="copyShareLink()">Copy</button>
      </div>
      <p style="font-size:12px;color:var(--text-muted)">Anyone with this link can view your resume.</p>`;
  } else {
    statusEl.innerHTML = `<div style="color:var(--text-muted);padding:16px 0">Resume is now private.</div>`;
  }
}
function hideShare(e) {
  if (!e || e.target === document.getElementById('shareOverlay'))
    document.getElementById('shareOverlay').classList.remove('active');
}
function copyShareLink() {
  const input = document.getElementById('shareUrl');
  if (!input) return;
  input.select();
  document.execCommand('copy');
  showToast('Link copied!');
}

// Print
function printResume() {
  const content = document.getElementById('resumePreview').innerHTML;
  const printWin = window.open('', '_blank');
  printWin.document.write(`
    <!DOCTYPE html><html><head>
    <title>Resume</title>
    <link rel="stylesheet" href="${window.location.origin}/${window.location.pathname.split('/').slice(0,-1).join('/')}/css/templates.css">
    <style>
      body { margin: 0; padding: 0; background: white; }
      .preview-page { width: 794px; min-height: 1123px; margin: 0 auto; }
    </style>
    </head><body>
    <div class="preview-page" style="--theme-color:${currentColor}">${content}</div>
    <script>window.onload=function(){window.print();window.close();}<\/script>
    </body></html>`);
  printWin.document.close();
}

// Helpers
function escHtml(s) {
  if (!s) return '';
  const d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}

let toastTimer;
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  clearTimeout(toastTimer);
  t.textContent = msg; t.className = `toast ${type} show`;
  toastTimer = setTimeout(() => t.classList.remove('show'), 3000);
}
