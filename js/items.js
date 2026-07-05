// Items Modal — Add/Edit Forms

function showAddItem(section) {
  showItemModal(section, null);
}

function showItemModal(section, item) {
  const isEdit = !!item;
  document.getElementById('itemModalTitle').textContent = isEdit ? `Edit ${ucFirst(section)}` : `Add ${ucFirst(section)}`;
  document.getElementById('itemFormContainer').innerHTML = getForm(section, item || {});
  document.getElementById('itemOverlay').classList.add('active');

  // Initialize skill level buttons if needed
  if (section === 'skills') {
    const level = item ? (item.level || 3) : 3;
    document.querySelectorAll('.skill-level-btn').forEach(btn => {
      btn.classList.toggle('active', parseInt(btn.dataset.level) === level);
    });
  }
}

function hideItemModal(e) {
  if (!e || e.target === document.getElementById('itemOverlay'))
    document.getElementById('itemOverlay').classList.remove('active');
}

function getForm(section, item) {
  switch(section) {
    case 'experience': return experienceForm(item);
    case 'education': return educationForm(item);
    case 'skills': return skillsForm(item);
    case 'projects': return projectsForm(item);
    case 'certifications': return certForm(item);
    case 'languages': return languageForm(item);
    default: return '';
  }
}

function experienceForm(item) {
  return `
    <input type="hidden" id="f_section" value="experience">
    <input type="hidden" id="f_item_id" value="${item.id || ''}">
    <div class="form-grid">
      <div class="form-group full"><label>Job Title / Position *</label>
        <input class="form-input" id="f_position" value="${esc(item.position)}" placeholder="Software Engineer"></div>
      <div class="form-group full"><label>Company *</label>
        <input class="form-input" id="f_company" value="${esc(item.company)}" placeholder="Acme Inc."></div>
      <div class="form-group"><label>Location</label>
        <input class="form-input" id="f_location" value="${esc(item.location)}" placeholder="New York, NY"></div>
      <div class="form-group"><label>Start Date</label>
        <input class="form-input" id="f_start_date" value="${esc(item.start_date)}" placeholder="Jan 2022"></div>
      <div class="form-group"><label>End Date</label>
        <input class="form-input" id="f_end_date" value="${esc(item.end_date)}" placeholder="Dec 2023" ${item.current ? 'disabled' : ''}></div>
      <div class="form-group">
        <div class="form-check" style="margin-top:22px">
          <input type="checkbox" id="f_current" ${item.current ? 'checked' : ''} onchange="toggleCurrent()">
          <label for="f_current" style="text-transform:none;font-size:13px">Currently working here</label>
        </div>
      </div>
      <div class="form-group full"><label>Description</label>
        <textarea class="form-input form-textarea" id="f_description" rows="4" placeholder="Describe your responsibilities and achievements...">${esc(item.description)}</textarea></div>
    </div>
    <button class="btn-submit" onclick="submitItem()">Save</button>`;
}

function educationForm(item) {
  return `
    <input type="hidden" id="f_section" value="education">
    <input type="hidden" id="f_item_id" value="${item.id || ''}">
    <div class="form-grid">
      <div class="form-group full"><label>Institution *</label>
        <input class="form-input" id="f_institution" value="${esc(item.institution)}" placeholder="MIT"></div>
      <div class="form-group"><label>Degree</label>
        <input class="form-input" id="f_degree" value="${esc(item.degree)}" placeholder="Bachelor's"></div>
      <div class="form-group"><label>Field of Study</label>
        <input class="form-input" id="f_field" value="${esc(item.field)}" placeholder="Computer Science"></div>
      <div class="form-group"><label>Start Date</label>
        <input class="form-input" id="f_start_date" value="${esc(item.start_date)}" placeholder="Sep 2018"></div>
      <div class="form-group"><label>End Date</label>
        <input class="form-input" id="f_end_date" value="${esc(item.end_date)}" placeholder="Jun 2022" ${item.current ? 'disabled' : ''}></div>
      <div class="form-group">
        <div class="form-check" style="margin-top:22px">
          <input type="checkbox" id="f_current" ${item.current ? 'checked' : ''} onchange="toggleCurrent()">
          <label for="f_current" style="text-transform:none;font-size:13px">Currently enrolled</label>
        </div>
      </div>
      <div class="form-group"><label>GPA (optional)</label>
        <input class="form-input" id="f_gpa" value="${esc(item.gpa)}" placeholder="3.8/4.0"></div>
      <div class="form-group full"><label>Additional Info</label>
        <textarea class="form-input form-textarea" id="f_description" rows="3" placeholder="Honors, activities, achievements...">${esc(item.description)}</textarea></div>
    </div>
    <button class="btn-submit" onclick="submitItem()">Save</button>`;
}

function skillsForm(item) {
  const cats = ['Technical', 'Soft Skills', 'Tools', 'Languages', 'Other'];
  return `
    <input type="hidden" id="f_section" value="skills">
    <input type="hidden" id="f_item_id" value="${item.id || ''}">
    <div class="form-group"><label>Skill Name *</label>
      <input class="form-input" id="f_name" value="${esc(item.name)}" placeholder="JavaScript"></div>
    <div class="form-group"><label>Category</label>
      <select class="form-input" id="f_category">
        ${cats.map(c => `<option value="${c}" ${item.category === c ? 'selected' : ''}>${c}</option>`).join('')}
      </select>
    </div>
    <div class="form-group"><label>Proficiency Level</label>
      <div class="skill-level-selector">
        ${['Beginner','Elementary','Intermediate','Advanced','Expert'].map((l,i) => `
          <button class="skill-level-btn" data-level="${i+1}" onclick="setSkillLevel(this)">${l}</button>`).join('')}
      </div>
    </div>
    <input type="hidden" id="f_level" value="${item.level || 3}">
    <button class="btn-submit" onclick="submitItem()">Save</button>`;
}

function projectsForm(item) {
  return `
    <input type="hidden" id="f_section" value="projects">
    <input type="hidden" id="f_item_id" value="${item.id || ''}">
    <div class="form-grid">
      <div class="form-group full"><label>Project Name *</label>
        <input class="form-input" id="f_name" value="${esc(item.name)}" placeholder="E-Commerce Platform"></div>
      <div class="form-group"><label>Your Role</label>
        <input class="form-input" id="f_role" value="${esc(item.role)}" placeholder="Lead Developer"></div>
      <div class="form-group"><label>Project URL</label>
        <input class="form-input" id="f_url" value="${esc(item.url)}" placeholder="https://github.com/..."></div>
      <div class="form-group"><label>Start Date</label>
        <input class="form-input" id="f_start_date" value="${esc(item.start_date)}" placeholder="Jan 2023"></div>
      <div class="form-group"><label>End Date</label>
        <input class="form-input" id="f_end_date" value="${esc(item.end_date)}" placeholder="Mar 2023"></div>
      <div class="form-group full"><label>Technologies Used</label>
        <input class="form-input" id="f_technologies" value="${esc(item.technologies)}" placeholder="React, Node.js, PostgreSQL"></div>
      <div class="form-group full"><label>Description</label>
        <textarea class="form-input form-textarea" id="f_description" rows="4" placeholder="What did you build and what was the impact?">${esc(item.description)}</textarea></div>
    </div>
    <button class="btn-submit" onclick="submitItem()">Save</button>`;
}

function certForm(item) {
  return `
    <input type="hidden" id="f_section" value="certifications">
    <input type="hidden" id="f_item_id" value="${item.id || ''}">
    <div class="form-grid">
      <div class="form-group full"><label>Certification Name *</label>
        <input class="form-input" id="f_name" value="${esc(item.name)}" placeholder="AWS Solutions Architect"></div>
      <div class="form-group"><label>Issuing Organization</label>
        <input class="form-input" id="f_issuer" value="${esc(item.issuer)}" placeholder="Amazon Web Services"></div>
      <div class="form-group"><label>Date Issued</label>
        <input class="form-input" id="f_date" value="${esc(item.date)}" placeholder="Dec 2023"></div>
      <div class="form-group"><label>Credential ID</label>
        <input class="form-input" id="f_credential_id" value="${esc(item.credential_id)}" placeholder="ABC-12345"></div>
      <div class="form-group full"><label>Credential URL</label>
        <input class="form-input" id="f_url" value="${esc(item.url)}" placeholder="https://..."></div>
    </div>
    <button class="btn-submit" onclick="submitItem()">Save</button>`;
}

function languageForm(item) {
  const levels = ['Native','Fluent','Advanced','Intermediate','Elementary','Beginner'];
  return `
    <input type="hidden" id="f_section" value="languages">
    <input type="hidden" id="f_item_id" value="${item.id || ''}">
    <div class="form-group"><label>Language *</label>
      <input class="form-input" id="f_name" value="${esc(item.name)}" placeholder="Spanish"></div>
    <div class="form-group"><label>Proficiency</label>
      <select class="form-input" id="f_proficiency">
        ${levels.map(l => `<option value="${l}" ${item.proficiency === l ? 'selected' : ''}>${l}</option>`).join('')}
      </select>
    </div>
    <button class="btn-submit" onclick="submitItem()">Save</button>`;
}

// Helpers
function toggleCurrent() {
  const checked = document.getElementById('f_current').checked;
  const endDate = document.getElementById('f_end_date');
  if (endDate) endDate.disabled = checked;
}

function setSkillLevel(btn) {
  document.querySelectorAll('.skill-level-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('f_level').value = btn.dataset.level;
}

// Submit
async function submitItem() {
  const section = document.getElementById('f_section').value;
  const itemId = document.getElementById('f_item_id').value;
  const isEdit = !!itemId;

  const fd = new FormData();
  fd.append('action', isEdit ? 'update_item' : 'add_item');
  fd.append('resume_id', RESUME_ID);
  fd.append('section', section);
  if (isEdit) fd.append('item_id', itemId);

  // Collect all form fields
  document.querySelectorAll('#itemFormContainer input:not([type=hidden]):not([type=checkbox]), #itemFormContainer select, #itemFormContainer textarea').forEach(el => {
    if (el.id && el.id.startsWith('f_')) {
      const key = el.id.slice(2);
      fd.append(key, el.value || '');
    }
  });

  // Handle checkbox
  const currentCheck = document.getElementById('f_current');
  if (currentCheck) fd.append('current', currentCheck.checked ? '1' : '0');

  const btn = document.querySelector('.modal-item .btn-submit');
  btn.disabled = true; btn.textContent = 'Saving...';

  try {
    const res = await fetch('php/resume.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success || data.id) {
      hideItemModal();
      showToast(isEdit ? 'Updated!' : 'Added!');
      loadSectionItems(section);
    } else {
      showToast(data.error || 'Save failed', 'error');
    }
  } catch (e) {
    showToast('Connection error', 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Save';
  }
}

function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function ucFirst(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
}
