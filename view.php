<?php
require_once 'php/config.php';
$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) { header('Location: index.php'); exit; }

$db = getDB();
$stmt = $db->prepare("SELECT * FROM resumes WHERE public_slug = ? AND is_public = 1");
$stmt->execute([$slug]);
$resume = $stmt->fetch();
if (!$resume) { ?>
<!DOCTYPE html><html><head><title>Not Found</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600&display=swap" rel="stylesheet">
<style>body{background:#0a0a0f;color:#94a3b8;font-family:'DM Sans',sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;text-align:center}</style>
</head><body><div><h2 style="font-size:60px;margin-bottom:16px">404</h2><p>Resume not found or no longer public.</p><a href="index.php" style="color:#6366f1">← Back to ResumeForge</a></div></body></html>
<?php exit; }

$resumeId = $resume['id'];
// Load all data
$sections = ['personal_info', 'education', 'experience', 'skills', 'projects', 'certifications', 'languages'];
$data = ['resume' => $resume];
foreach ($sections as $table) {
  $key = $table === 'personal_info' ? 'personal' : $table;
  $orderBy = $table === 'personal_info' ? '' : ' ORDER BY sort_order ASC, id ASC';
  $stmt = $db->prepare("SELECT * FROM $table WHERE resume_id = ?$orderBy");
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
<title><?= htmlspecialchars($p['full_name'] ?? 'Resume') ?> — ResumeForge</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/templates.css">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #e5e7eb; display: flex; flex-direction: column; min-height: 100vh; font-family: 'DM Sans', sans-serif; }
  .view-bar { background: #1e293b; color: white; padding: 10px 24px; display: flex; align-items: center; justify-content: space-between; font-size: 13px; }
  .view-brand { display: flex; align-items: center; gap: 8px; font-weight: 600; }
  .view-actions { display: flex; gap: 12px; }
  .view-btn { padding: 6px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; font-family: inherit; }
  .view-btn-primary { background: #6366f1; color: white; }
  .view-btn-ghost { background: transparent; color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.2); }
  .view-wrapper { flex: 1; padding: 40px; display: flex; justify-content: center; }
  .resume-page { width: 794px; background: white; min-height: 1123px; box-shadow: 0 8px 40px rgba(0,0,0,0.2); }
</style>
</head>
<body>
<div class="view-bar">
  <div class="view-brand">◈ ResumeForge</div>
  <div class="view-actions">
    <button class="view-btn view-btn-ghost" onclick="window.print()">🖨️ Print</button>
    <a href="index.php"><button class="view-btn view-btn-primary">Create My Resume</button></a>
  </div>
</div>
<div class="view-wrapper">
  <div class="resume-page template-<?= htmlspecialchars($resume['template']) ?>" id="resumeEl" style="--theme-color: <?= htmlspecialchars($resume['theme_color']) ?>">
    <!-- Rendered by JS -->
  </div>
</div>

<script>
const resumeData = <?= json_encode($data) ?>;
const currentTemplate = '<?= htmlspecialchars($resume['template']) ?>';
const currentColor = '<?= htmlspecialchars($resume['theme_color']) ?>';
</script>
<script src="js/preview.js"></script>
<script>
window.onload = function() {
  const el = document.getElementById('resumeEl');
  el.style.setProperty('--theme-color', currentColor);
  el.className = 'resume-page template-' + currentTemplate;
  
  const p = resumeData.personal || {};
  let html = '';
  switch(currentTemplate) {
    case 'modern':    html = renderModern(resumeData, p, currentColor); break;
    case 'classic':   html = renderClassic(resumeData, p, currentColor); break;
    case 'minimal':   html = renderMinimal(resumeData, p, currentColor); break;
    case 'executive': html = renderExecutive(resumeData, p, currentColor); break;
    case 'creative':  html = renderCreative(resumeData, p, currentColor); break;
    case 'compact':   html = renderCompact(resumeData, p, currentColor); break;
    default:          html = renderModern(resumeData, p, currentColor);
  }
  el.innerHTML = html;
};
</script>
</body>
</html>
