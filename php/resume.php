<?php
require_once 'config.php';

requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'list':
        listResumes($userId);
        break;
    case 'create':
        createResume($userId);
        break;
    case 'delete':
        deleteResume($userId);
        break;
    case 'get':
        getResume($userId);
        break;
    case 'save_section':
        saveSection($userId);
        break;
    case 'get_section':
        getSection($userId);
        break;
    case 'update_settings':
        updateSettings($userId);
        break;
    case 'add_item':
        addItem($userId);
        break;
    case 'update_item':
        updateItem($userId);
        break;
    case 'delete_item':
        deleteItem($userId);
        break;
    case 'get_full':
        getFullResume($userId);
        break;
    case 'toggle_public':
        togglePublic($userId);
        break;
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}

function listResumes($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT r.*, COUNT(DISTINCT e.id) as exp_count 
                          FROM resumes r 
                          LEFT JOIN experience e ON r.id = e.resume_id
                          WHERE r.user_id = ? 
                          GROUP BY r.id
                          ORDER BY r.updated_at DESC");
    $stmt->execute([$userId]);
    jsonResponse(['resumes' => $stmt->fetchAll()]);
}

function createResume($userId) {
    $title = sanitize($_POST['title'] ?? 'My Resume');
    $template = sanitize($_POST['template'] ?? 'modern');
    $db = getDB();

    $stmt = $db->prepare("INSERT INTO resumes (user_id, title, template) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $title, $template]);
    $resumeId = $db->lastInsertId();

    // Create empty personal info
    $stmt = $db->prepare("INSERT INTO personal_info (resume_id) VALUES (?)");
    $stmt->execute([$resumeId]);

    jsonResponse(['success' => true, 'resume_id' => $resumeId]);
}

function deleteResume($userId) {
    $resumeId = (int)($_POST['resume_id'] ?? 0);
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resumeId, $userId]);
    jsonResponse(['success' => true]);
}

function getResume($userId) {
    $resumeId = (int)($_GET['resume_id'] ?? 0);
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resumeId, $userId]);
    $resume = $stmt->fetch();
    if (!$resume) jsonResponse(['error' => 'Not found'], 404);
    jsonResponse(['resume' => $resume]);
}

function saveSection($userId) {
    $resumeId = (int)($_POST['resume_id'] ?? 0);
    $section = $_POST['section'] ?? '';

    // Verify ownership
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resumeId, $userId]);
    if (!$stmt->fetch()) jsonResponse(['error' => 'Unauthorized'], 403);

    switch ($section) {
        case 'personal':
            savePersonal($db, $resumeId);
            break;
        default:
            jsonResponse(['error' => 'Invalid section'], 400);
    }
}

function savePersonal($db, $resumeId) {
    $fields = ['full_name', 'job_title', 'email', 'phone', 'location', 'website', 'linkedin', 'github', 'summary'];
    $data = [];
    $setParts = [];

    foreach ($fields as $field) {
        $data[] = sanitize($_POST[$field] ?? '');
        $setParts[] = "$field = ?";
    }
    $data[] = $resumeId;

    $sql = "UPDATE personal_info SET " . implode(', ', $setParts) . " WHERE resume_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($data);

    // Update resume updated_at
    $db->prepare("UPDATE resumes SET updated_at = NOW() WHERE id = ?")->execute([$resumeId]);

    jsonResponse(['success' => true]);
}

function getSection($userId) {
    $resumeId = (int)($_GET['resume_id'] ?? 0);
    $section = $_GET['section'] ?? '';
    $db = getDB();

    // Verify ownership
    $stmt = $db->prepare("SELECT id FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resumeId, $userId]);
    if (!$stmt->fetch()) jsonResponse(['error' => 'Unauthorized'], 403);

    $tableMap = [
        'personal' => ['table' => 'personal_info', 'single' => true],
        'education' => ['table' => 'education', 'single' => false],
        'experience' => ['table' => 'experience', 'single' => false],
        'skills' => ['table' => 'skills', 'single' => false],
        'projects' => ['table' => 'projects', 'single' => false],
        'certifications' => ['table' => 'certifications', 'single' => false],
        'languages' => ['table' => 'languages', 'single' => false],
    ];

    if (!isset($tableMap[$section])) jsonResponse(['error' => 'Invalid section'], 400);

    $info = $tableMap[$section];
    $orderBy = $info['single'] ? '' : ' ORDER BY sort_order ASC, id ASC';
    $stmt = $db->prepare("SELECT * FROM {$info['table']} WHERE resume_id = ?{$orderBy}");
    $stmt->execute([$resumeId]);

    if ($info['single']) {
        jsonResponse(['data' => $stmt->fetch()]);
    } else {
        jsonResponse(['data' => $stmt->fetchAll()]);
    }
}

function addItem($userId) {
    $resumeId = (int)($_POST['resume_id'] ?? 0);
    $section = $_POST['section'] ?? '';
    $db = getDB();

    // Verify ownership
    $stmt = $db->prepare("SELECT id FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resumeId, $userId]);
    if (!$stmt->fetch()) jsonResponse(['error' => 'Unauthorized'], 403);

    $tableMap = [
        'education' => 'education',
        'experience' => 'experience',
        'skills' => 'skills',
        'projects' => 'projects',
        'certifications' => 'certifications',
        'languages' => 'languages',
    ];

    if (!isset($tableMap[$section])) jsonResponse(['error' => 'Invalid section'], 400);

    $table = $tableMap[$section];

    // Get max sort order
    $stmt = $db->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM $table WHERE resume_id = ?");
    $stmt->execute([$resumeId]);
    $nextOrder = $stmt->fetch()['next_order'];

    $fields = getFieldsForTable($table);
    $setFields = ['resume_id' => $resumeId, 'sort_order' => $nextOrder];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $setFields[$field] = sanitize($_POST[$field]);
        }
    }

    // Handle description separately (don't sanitize aggressively)
    if (isset($_POST['description'])) {
        $setFields['description'] = strip_tags($_POST['description']);
    }
    if (isset($_POST['technologies'])) {
        $setFields['technologies'] = strip_tags($_POST['technologies']);
    }

    $cols = implode(', ', array_keys($setFields));
    $placeholders = implode(', ', array_fill(0, count($setFields), '?'));
    $stmt = $db->prepare("INSERT INTO $table ($cols) VALUES ($placeholders)");
    $stmt->execute(array_values($setFields));
    $newId = $db->lastInsertId();

    $db->prepare("UPDATE resumes SET updated_at = NOW() WHERE id = ?")->execute([$resumeId]);

    jsonResponse(['success' => true, 'id' => $newId]);
}

function updateItem($userId) {
    $resumeId = (int)($_POST['resume_id'] ?? 0);
    $itemId = (int)($_POST['item_id'] ?? 0);
    $section = $_POST['section'] ?? '';
    $db = getDB();

    // Verify ownership
    $stmt = $db->prepare("SELECT id FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resumeId, $userId]);
    if (!$stmt->fetch()) jsonResponse(['error' => 'Unauthorized'], 403);

    $tableMap = [
        'education' => 'education',
        'experience' => 'experience',
        'skills' => 'skills',
        'projects' => 'projects',
        'certifications' => 'certifications',
        'languages' => 'languages',
    ];

    if (!isset($tableMap[$section])) jsonResponse(['error' => 'Invalid section'], 400);
    $table = $tableMap[$section];
    $fields = getFieldsForTable($table);

    $setParts = [];
    $values = [];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $setParts[] = "$field = ?";
            $values[] = sanitize($_POST[$field]);
        }
    }

    if (isset($_POST['description'])) {
        $setParts[] = "description = ?";
        $values[] = strip_tags($_POST['description']);
    }
    if (isset($_POST['technologies'])) {
        $setParts[] = "technologies = ?";
        $values[] = strip_tags($_POST['technologies']);
    }

    if (empty($setParts)) jsonResponse(['success' => true]);

    $values[] = $itemId;
    $values[] = $resumeId;

    $sql = "UPDATE $table SET " . implode(', ', $setParts) . " WHERE id = ? AND resume_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($values);

    $db->prepare("UPDATE resumes SET updated_at = NOW() WHERE id = ?")->execute([$resumeId]);

    jsonResponse(['success' => true]);
}

function deleteItem($userId) {
    $resumeId = (int)($_POST['resume_id'] ?? 0);
    $itemId = (int)($_POST['item_id'] ?? 0);
    $section = $_POST['section'] ?? '';
    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resumeId, $userId]);
    if (!$stmt->fetch()) jsonResponse(['error' => 'Unauthorized'], 403);

    $tableMap = [
        'education' => 'education',
        'experience' => 'experience',
        'skills' => 'skills',
        'projects' => 'projects',
        'certifications' => 'certifications',
        'languages' => 'languages',
    ];

    if (!isset($tableMap[$section])) jsonResponse(['error' => 'Invalid section'], 400);
    $table = $tableMap[$section];

    $stmt = $db->prepare("DELETE FROM $table WHERE id = ? AND resume_id = ?");
    $stmt->execute([$itemId, $resumeId]);

    jsonResponse(['success' => true]);
}

function getFullResume($userId) {
    $resumeId = (int)($_GET['resume_id'] ?? 0);
    $db = getDB();

    $stmt = $db->prepare("SELECT r.*, u.name as user_name FROM resumes r JOIN users u ON r.user_id = u.id WHERE r.id = ? AND r.user_id = ?");
    $stmt->execute([$resumeId, $userId]);
    $resume = $stmt->fetch();
    if (!$resume) jsonResponse(['error' => 'Not found'], 404);

    $sections = ['personal_info', 'education', 'experience', 'skills', 'projects', 'certifications', 'languages'];
    $data = ['resume' => $resume];

    foreach ($sections as $table) {
        $key = $table === 'personal_info' ? 'personal' : $table;
        $orderBy = $table === 'personal_info' ? '' : ' ORDER BY sort_order ASC, id ASC';
        $stmt = $db->prepare("SELECT * FROM $table WHERE resume_id = ?$orderBy");
        $stmt->execute([$resumeId]);
        if ($table === 'personal_info') {
            $data[$key] = $stmt->fetch();
        } else {
            $data[$key] = $stmt->fetchAll();
        }
    }

    jsonResponse($data);
}

function updateSettings($userId) {
    $resumeId = (int)($_POST['resume_id'] ?? 0);
    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resumeId, $userId]);
    if (!$stmt->fetch()) jsonResponse(['error' => 'Unauthorized'], 403);

    $template = sanitize($_POST['template'] ?? 'modern');
    $themeColor = sanitize($_POST['theme_color'] ?? '#6366f1');
    $title = sanitize($_POST['title'] ?? 'My Resume');

    $stmt = $db->prepare("UPDATE resumes SET template = ?, theme_color = ?, title = ? WHERE id = ?");
    $stmt->execute([$template, $themeColor, $title, $resumeId]);

    jsonResponse(['success' => true]);
}

function togglePublic($userId) {
    $resumeId = (int)($_POST['resume_id'] ?? 0);
    $db = getDB();

    $stmt = $db->prepare("SELECT id, is_public FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resumeId, $userId]);
    $resume = $stmt->fetch();
    if (!$resume) jsonResponse(['error' => 'Unauthorized'], 403);

    $newState = $resume['is_public'] ? 0 : 1;
    $slug = $newState ? bin2hex(random_bytes(8)) : null;

    $stmt = $db->prepare("UPDATE resumes SET is_public = ?, public_slug = ? WHERE id = ?");
    $stmt->execute([$newState, $slug, $resumeId]);

    jsonResponse(['success' => true, 'is_public' => $newState, 'slug' => $slug]);
}

function getFieldsForTable($table) {
    $fieldsMap = [
        'education' => ['institution', 'degree', 'field', 'start_date', 'end_date', 'current', 'gpa'],
        'experience' => ['company', 'position', 'location', 'start_date', 'end_date', 'current'],
        'skills' => ['name', 'level', 'category'],
        'projects' => ['name', 'role', 'url', 'start_date', 'end_date'],
        'certifications' => ['name', 'issuer', 'date', 'credential_id', 'url'],
        'languages' => ['name', 'proficiency'],
    ];
    return $fieldsMap[$table] ?? [];
}
?>
