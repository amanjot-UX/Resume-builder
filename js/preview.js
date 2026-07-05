// Preview Renderer

function renderPreview() {
  const preview = document.getElementById('resumePreview');
  const data = resumeData;
  const p = data.personal || {};
  const color = currentColor;

  // Apply template class & color
  preview.style.setProperty('--theme-color', color);
  preview.className = `preview-page template-${currentTemplate}`;

  let html = '';
  switch (currentTemplate) {
    case 'modern':    html = renderModern(data, p, color); break;
    case 'classic':   html = renderClassic(data, p, color); break;
    case 'minimal':   html = renderMinimal(data, p, color); break;
    case 'executive': html = renderExecutive(data, p, color); break;
    case 'creative':  html = renderCreative(data, p, color); break;
    case 'compact':   html = renderCompact(data, p, color); break;
    default:          html = renderModern(data, p, color);
  }

  preview.innerHTML = html;
}

// ---- Helpers ----
function e(s) {
  if (!s) return '';
  const d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}
function dateRange(start, end, current) {
  if (!start && !end) return '';
  return `${start || ''}${(start || end) ? '–' : ''}${current ? 'Present' : (end || '')}`;
}
function contactItem(icon, val) {
  if (!val) return '';
  return `<span class="r-contact">${icon} ${e(val)}</span>`;
}

function renderContacts(p, inline=false) {
  const items = [
    p.email && `📧 ${e(p.email)}`,
    p.phone && `📱 ${e(p.phone)}`,
    p.location && `📍 ${e(p.location)}`,
    p.website && `🌐 ${e(p.website)}`,
    p.linkedin && `in ${e(p.linkedin)}`,
    p.github && `⌨ ${e(p.github)}`,
  ].filter(Boolean);
  if (!items.length) return '';
  if (inline) return items.map(i => `<span class="r-contact">${i}</span>`).join('');
  return items.map(i => `<div class="r-contact">${i}</div>`).join('');
}

function renderSkillsBar(skills) {
  if (!skills || !skills.length) return '';
  return skills.map(s => `
    <div class="r-skill-bar">
      <span class="r-skill-bar-name">${e(s.name)}</span>
      <div class="r-skill-bar-track">
        <div class="r-skill-bar-fill" style="width:${(s.level/5)*100}%"></div>
      </div>
    </div>`).join('');
}

function renderSkillTags(skills) {
  if (!skills || !skills.length) return '';
  return `<div class="r-skills">${skills.map(s => `<span class="r-skill-tag">${e(s.name)}</span>`).join('')}</div>`;
}

// ===== MODERN =====
function renderModern(data, p, color) {
  const exp = data.experience || [];
  const edu = data.education || [];
  const skills = data.skills || [];
  const proj = data.projects || [];
  const certs = data.certifications || [];
  const langs = data.languages || [];

  return `
    <div class="r-header">
      <div class="r-name">${e(p.full_name) || 'Your Name'}</div>
      <div class="r-title">${e(p.job_title) || ''}</div>
      <div class="r-contacts">${renderContacts(p, true)}</div>
    </div>
    <div class="r-body">
      ${p.summary ? `
        <div class="r-section">
          <div class="r-section-title">Summary</div>
          <div class="r-summary">${e(p.summary)}</div>
        </div>` : ''}
      ${exp.length ? `
        <div class="r-section">
          <div class="r-section-title">Experience</div>
          ${exp.map(ex => `
            <div class="r-item">
              <div class="r-item-header">
                <div class="r-item-title">${e(ex.position)}</div>
                <div class="r-item-date">${dateRange(ex.start_date, ex.end_date, ex.current)}</div>
              </div>
              <div class="r-item-sub">${e(ex.company)}${ex.location ? ` · ${e(ex.location)}` : ''}</div>
              ${ex.description ? `<div class="r-item-desc">${e(ex.description)}</div>` : ''}
            </div>`).join('')}
        </div>` : ''}
      ${edu.length ? `
        <div class="r-section">
          <div class="r-section-title">Education</div>
          ${edu.map(ed => `
            <div class="r-item">
              <div class="r-item-header">
                <div class="r-item-title">${e(ed.degree)}${ed.field ? ` in ${e(ed.field)}` : ''}</div>
                <div class="r-item-date">${dateRange(ed.start_date, ed.end_date, ed.current)}</div>
              </div>
              <div class="r-item-sub">${e(ed.institution)}${ed.gpa ? ` · GPA: ${e(ed.gpa)}` : ''}</div>
              ${ed.description ? `<div class="r-item-desc">${e(ed.description)}</div>` : ''}
            </div>`).join('')}
        </div>` : ''}
      ${skills.length ? `
        <div class="r-section">
          <div class="r-section-title">Skills</div>
          ${renderSkillTags(skills)}
        </div>` : ''}
      ${proj.length ? `
        <div class="r-section">
          <div class="r-section-title">Projects</div>
          ${proj.map(pr => `
            <div class="r-item">
              <div class="r-item-header">
                <div class="r-item-title">${e(pr.name)}</div>
                <div class="r-item-date">${dateRange(pr.start_date, pr.end_date, false)}</div>
              </div>
              ${pr.role ? `<div class="r-item-sub">${e(pr.role)}</div>` : ''}
              ${pr.description ? `<div class="r-item-desc">${e(pr.description)}</div>` : ''}
              ${pr.technologies ? `<div style="margin-top:4px;font-size:12px;color:#64748b">Tech: ${e(pr.technologies)}</div>` : ''}
            </div>`).join('')}
        </div>` : ''}
      ${certs.length ? `
        <div class="r-section">
          <div class="r-section-title">Certifications</div>
          ${certs.map(c => `
            <div class="r-item">
              <div class="r-item-header">
                <div class="r-item-title">${e(c.name)}</div>
                <div class="r-item-date">${e(c.date) || ''}</div>
              </div>
              <div class="r-item-sub">${e(c.issuer)}</div>
            </div>`).join('')}
        </div>` : ''}
      ${langs.length ? `
        <div class="r-section">
          <div class="r-section-title">Languages</div>
          <div class="r-two-col">
            ${langs.map(l => `<div class="r-lang"><span class="r-lang-name">${e(l.name)}</span><span class="r-lang-level">${e(l.proficiency)}</span></div>`).join('')}
          </div>
        </div>` : ''}
    </div>`;
}

// ===== CLASSIC =====
function renderClassic(data, p, color) {
  const exp = data.experience || [];
  const edu = data.education || [];
  const skills = data.skills || [];
  const certs = data.certifications || [];
  const langs = data.languages || [];

  function skillDots(level) {
    return Array.from({length:5}, (_,i) => `<div class="r-skill-dot${i < level ? ' filled' : ''}"></div>`).join('');
  }

  return `
    <div class="r-sidebar">
      <div class="r-name">${e(p.full_name) || 'Your Name'}</div>
      <div class="r-title">${e(p.job_title) || ''}</div>
      <div class="r-section">
        <div class="r-section-title">Contact</div>
        ${renderContacts(p)}
      </div>
      ${skills.length ? `
        <div class="r-section">
          <div class="r-section-title">Skills</div>
          ${skills.map(s => `
            <div class="r-skill-item">
              ${e(s.name)}
              <div class="r-skill-dots">${skillDots(s.level || 3)}</div>
            </div>`).join('')}
        </div>` : ''}
      ${langs.length ? `
        <div class="r-section">
          <div class="r-section-title">Languages</div>
          ${langs.map(l => `<div class="r-skill-item">${e(l.name)}<br><small style="opacity:0.65">${e(l.proficiency)}</small></div>`).join('')}
        </div>` : ''}
      ${certs.length ? `
        <div class="r-section">
          <div class="r-section-title">Certifications</div>
          ${certs.map(c => `<div class="r-skill-item">${e(c.name)}<br><small style="opacity:0.65">${e(c.issuer)}</small></div>`).join('')}
        </div>` : ''}
    </div>
    <div class="r-main">
      ${p.summary ? `
        <div class="r-section">
          <div class="r-section-title">About Me</div>
          <div class="r-summary">${e(p.summary)}</div>
        </div>` : ''}
      ${exp.length ? `
        <div class="r-section">
          <div class="r-section-title">Work Experience</div>
          ${exp.map(ex => `
            <div class="r-item" style="margin-bottom:16px">
              <span class="r-item-date">${dateRange(ex.start_date, ex.end_date, ex.current)}</span>
              <div class="r-item-title">${e(ex.position)}</div>
              <div class="r-item-sub">${e(ex.company)}${ex.location ? ` · ${e(ex.location)}` : ''}</div>
              ${ex.description ? `<div class="r-item-desc">${e(ex.description)}</div>` : ''}
            </div>`).join('')}
        </div>` : ''}
      ${edu.length ? `
        <div class="r-section">
          <div class="r-section-title">Education</div>
          ${edu.map(ed => `
            <div class="r-item" style="margin-bottom:14px">
              <span class="r-item-date">${dateRange(ed.start_date, ed.end_date, ed.current)}</span>
              <div class="r-item-title">${e(ed.degree)}${ed.field ? ` in ${e(ed.field)}` : ''}</div>
              <div class="r-item-sub">${e(ed.institution)}</div>
              ${ed.description ? `<div class="r-item-desc">${e(ed.description)}</div>` : ''}
            </div>`).join('')}
        </div>` : ''}
    </div>`;
}

// ===== MINIMAL =====
function renderMinimal(data, p, color) {
  const exp = data.experience || [];
  const edu = data.education || [];
  const skills = data.skills || [];
  const proj = data.projects || [];

  const nameParts = (p.full_name || 'Your Name').split(' ');
  const first = nameParts.slice(0,-1).join(' ');
  const last = nameParts[nameParts.length - 1];

  return `
    <div class="r-name">${e(first)} <strong>${e(last)}</strong></div>
    <div class="r-title">${e(p.job_title) || ''}</div>
    <div class="r-contacts">${renderContacts(p, true)}</div>
    ${p.summary ? `
      <div class="r-section">
        <div class="r-section-title">Profile</div>
        <div class="r-summary">${e(p.summary)}</div>
      </div>` : ''}
    ${exp.length ? `
      <div class="r-section">
        <div class="r-section-title">Experience</div>
        ${exp.map(ex => `
          <div class="r-item">
            <div class="r-item-title">${e(ex.position)}</div>
            <div class="r-item-sub">${e(ex.company)}</div>
            <div class="r-item-date">${dateRange(ex.start_date, ex.end_date, ex.current)}</div>
            ${ex.description ? `<div class="r-item-desc">${e(ex.description)}</div>` : ''}
          </div>`).join('')}
      </div>` : ''}
    ${edu.length ? `
      <div class="r-section">
        <div class="r-section-title">Education</div>
        ${edu.map(ed => `
          <div class="r-item">
            <div class="r-item-title">${e(ed.degree)}${ed.field ? ` in ${e(ed.field)}` : ''}</div>
            <div class="r-item-sub">${e(ed.institution)}</div>
            <div class="r-item-date">${dateRange(ed.start_date, ed.end_date, ed.current)}</div>
          </div>`).join('')}
      </div>` : ''}
    ${skills.length ? `
      <div class="r-section">
        <div class="r-section-title">Skills</div>
        ${renderSkillTags(skills)}
      </div>` : ''}
    ${proj.length ? `
      <div class="r-section">
        <div class="r-section-title">Projects</div>
        ${proj.map(pr => `
          <div class="r-item">
            <div class="r-item-title">${e(pr.name)}</div>
            ${pr.role ? `<div class="r-item-sub">${e(pr.role)}</div>` : ''}
            <div class="r-item-date">${dateRange(pr.start_date, pr.end_date, false)}</div>
            ${pr.description ? `<div class="r-item-desc">${e(pr.description)}</div>` : ''}
          </div>`).join('')}
      </div>` : ''}`;
}

// ===== EXECUTIVE =====
function renderExecutive(data, p, color) {
  const exp = data.experience || [];
  const edu = data.education || [];
  const skills = data.skills || [];
  const certs = data.certifications || [];

  return `
    <div class="r-header">
      <div class="r-header-top">
        <div>
          <div class="r-name">${e(p.full_name) || 'Your Name'}</div>
          <div class="r-title">${e(p.job_title) || ''}</div>
        </div>
        <div class="r-contacts">${renderContacts(p)}</div>
      </div>
      <div class="r-accent-line"></div>
    </div>
    <div class="r-body">
      ${p.summary ? `
        <div class="r-section">
          <div class="r-section-title">Executive Summary</div>
          <div class="r-summary">${e(p.summary)}</div>
        </div>` : ''}
      ${exp.length ? `
        <div class="r-section">
          <div class="r-section-title">Professional Experience</div>
          ${exp.map(ex => `
            <div class="r-item">
              <div class="r-item-header">
                <div class="r-item-title">${e(ex.position)}</div>
                <div class="r-item-date">${dateRange(ex.start_date, ex.end_date, ex.current)}</div>
              </div>
              <div class="r-item-sub">${e(ex.company)}${ex.location ? ` | ${e(ex.location)}` : ''}</div>
              ${ex.description ? `<div class="r-item-desc">${e(ex.description)}</div>` : ''}
            </div>`).join('')}
        </div>` : ''}
      ${edu.length ? `
        <div class="r-section">
          <div class="r-section-title">Education</div>
          ${edu.map(ed => `
            <div class="r-item">
              <div class="r-item-header">
                <div class="r-item-title">${e(ed.degree)}${ed.field ? ` in ${e(ed.field)}` : ''}</div>
                <div class="r-item-date">${dateRange(ed.start_date, ed.end_date, ed.current)}</div>
              </div>
              <div class="r-item-sub">${e(ed.institution)}</div>
            </div>`).join('')}
        </div>` : ''}
      ${skills.length ? `
        <div class="r-section">
          <div class="r-section-title">Core Competencies</div>
          ${renderSkillTags(skills)}
        </div>` : ''}
      ${certs.length ? `
        <div class="r-section">
          <div class="r-section-title">Certifications</div>
          ${certs.map(c => `
            <div class="r-item">
              <div class="r-item-header">
                <div class="r-item-title">${e(c.name)}</div>
                <div class="r-item-date">${e(c.date) || ''}</div>
              </div>
              <div class="r-item-sub">${e(c.issuer)}</div>
            </div>`).join('')}
        </div>` : ''}
    </div>`;
}

// ===== CREATIVE =====
function renderCreative(data, p, color) {
  const exp = data.experience || [];
  const edu = data.education || [];
  const skills = data.skills || [];
  const langs = data.languages || [];
  const proj = data.projects || [];

  return `
    <div class="r-left">
      <div class="r-photo"></div>
      <div class="r-name">${e(p.full_name) || 'Your Name'}</div>
      <div class="r-title">${e(p.job_title) || ''}</div>
      <div class="r-section-title">Contact</div>
      ${renderContacts(p)}
      ${skills.length ? `
        <div class="r-section-title">Skills</div>
        ${skills.map(s => `
          <div class="r-skill-item">
            <div class="r-skill-name">${e(s.name)}</div>
            <div class="r-skill-bar"><div class="r-skill-bar-fill" style="width:${(s.level/5)*100}%"></div></div>
          </div>`).join('')}` : ''}
      ${langs.length ? `
        <div class="r-section-title">Languages</div>
        ${langs.map(l => `
          <div class="r-skill-item">
            <div class="r-skill-name">${e(l.name)}</div>
            <div style="font-size:11px;opacity:0.75">${e(l.proficiency)}</div>
          </div>`).join('')}` : ''}
    </div>
    <div class="r-right">
      ${p.summary ? `
        <div class="r-section">
          <div class="r-section-title">About Me</div>
          <div class="r-summary">${e(p.summary)}</div>
        </div>` : ''}
      ${exp.length ? `
        <div class="r-section">
          <div class="r-section-title">Experience</div>
          ${exp.map(ex => `
            <div class="r-item" style="margin-bottom:16px">
              <div class="r-item-title">${e(ex.position)}</div>
              <div class="r-item-sub">${e(ex.company)}</div>
              <div class="r-item-date">${dateRange(ex.start_date, ex.end_date, ex.current)}</div>
              ${ex.description ? `<div class="r-item-desc">${e(ex.description)}</div>` : ''}
            </div>`).join('')}
        </div>` : ''}
      ${edu.length ? `
        <div class="r-section">
          <div class="r-section-title">Education</div>
          ${edu.map(ed => `
            <div class="r-item" style="margin-bottom:14px">
              <div class="r-item-title">${e(ed.degree)}${ed.field ? ` in ${e(ed.field)}` : ''}</div>
              <div class="r-item-sub">${e(ed.institution)}</div>
              <div class="r-item-date">${dateRange(ed.start_date, ed.end_date, ed.current)}</div>
            </div>`).join('')}
        </div>` : ''}
      ${proj.length ? `
        <div class="r-section">
          <div class="r-section-title">Projects</div>
          ${proj.map(pr => `
            <div class="r-item" style="margin-bottom:14px">
              <div class="r-item-title">${e(pr.name)}</div>
              ${pr.role ? `<div class="r-item-sub">${e(pr.role)}</div>` : ''}
              ${pr.description ? `<div class="r-item-desc">${e(pr.description)}</div>` : ''}
            </div>`).join('')}
        </div>` : ''}
    </div>`;
}

// ===== COMPACT =====
function renderCompact(data, p, color) {
  const exp = data.experience || [];
  const edu = data.education || [];
  const skills = data.skills || [];
  const certs = data.certifications || [];
  const langs = data.languages || [];

  return `
    <div class="r-header">
      <div>
        <div class="r-name">${e(p.full_name) || 'Your Name'}</div>
        <div class="r-title">${e(p.job_title) || ''}</div>
      </div>
      <div class="r-contacts">${renderContacts(p)}</div>
    </div>
    <div class="r-color-bar"></div>
    <div class="r-body">
      ${p.summary ? `
        <div class="r-section r-section-full">
          <div class="r-section-title">Summary</div>
          <div class="r-summary">${e(p.summary)}</div>
        </div>` : ''}
      ${exp.length ? `
        <div class="r-section r-section-full">
          <div class="r-section-title">Experience</div>
          ${exp.map(ex => `
            <div class="r-item">
              <div class="r-item-title">${e(ex.position)} <span style="font-weight:400;color:#475569">@ ${e(ex.company)}</span></div>
              <div class="r-item-date">${dateRange(ex.start_date, ex.end_date, ex.current)}</div>
              ${ex.description ? `<div class="r-item-desc">${e(ex.description)}</div>` : ''}
            </div>`).join('')}
        </div>` : ''}
      ${edu.length ? `
        <div class="r-section">
          <div class="r-section-title">Education</div>
          ${edu.map(ed => `
            <div class="r-item">
              <div class="r-item-title">${e(ed.degree)}</div>
              <div class="r-item-sub">${e(ed.institution)}</div>
              <div class="r-item-date">${dateRange(ed.start_date, ed.end_date, ed.current)}</div>
            </div>`).join('')}
        </div>` : ''}
      ${skills.length ? `
        <div class="r-section">
          <div class="r-section-title">Skills</div>
          ${renderSkillTags(skills)}
        </div>` : ''}
      ${certs.length ? `
        <div class="r-section">
          <div class="r-section-title">Certifications</div>
          ${certs.map(c => `<div class="r-item"><div class="r-item-title">${e(c.name)}</div><div class="r-item-sub">${e(c.issuer)}</div></div>`).join('')}
        </div>` : ''}
      ${langs.length ? `
        <div class="r-section">
          <div class="r-section-title">Languages</div>
          ${langs.map(l => `<div class="r-item"><div class="r-item-title">${e(l.name)}</div><div class="r-item-sub">${e(l.proficiency)}</div></div>`).join('')}
        </div>` : ''}
    </div>`;
}
