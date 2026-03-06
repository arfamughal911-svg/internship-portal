<?php
// register.php  –  Server-side validation + registration

session_start();
require_once 'config.php';

/* ──────────────────────────────────────────
   Helper: safely escape output for HTML
   ────────────────────────────────────────── */
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ──────────────────────────────────────────
   Redirect on GET
   ────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$errors = [];

/* ══════════════════════════════════════════
   1.  COLLECT & SANITISE RAW INPUT
   ══════════════════════════════════════════ */
$studentId  = trim($_POST['student_id']  ?? '');
$fullName   = trim($_POST['full_name']   ?? '');
$email      = trim($_POST['email']       ?? '');
$password   = $_POST['password']         ?? '';
$confirm    = $_POST['confirm_password'] ?? '';
$cnic       = trim($_POST['cnic']        ?? '');
$phone      = trim($_POST['phone']       ?? '');
$cgpaRaw    = trim($_POST['cgpa']        ?? '');
$department = trim($_POST['department']  ?? '');

/* ══════════════════════════════════════════
   2.  SERVER-SIDE VALIDATION
   ══════════════════════════════════════════ */

// Student ID  – e.g. FA21-BCS-001
if (!preg_match('/^[A-Z]{2}\d{2}-[A-Z]{2,4}-\d{3}$/', $studentId)) {
    $errors['student_id'] = 'Student ID must follow the format FA21-BCS-001.';
}

// Full Name
if ($fullName === '' || strlen($fullName) > 100) {
    $errors['full_name'] = 'Full name is required (max 100 characters).';
} elseif (!preg_match('/^[\p{L}\s\'-]+$/u', $fullName)) {
    $errors['full_name'] = 'Full name contains invalid characters.';
}

// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
    $errors['email'] = 'A valid e-mail address is required.';
}

// Password strength
$pwPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
if (!preg_match($pwPattern, $password)) {
    $errors['password'] = 'Password must be ≥8 characters with uppercase, lowercase, number, and special character.';
}

// Confirm password
if ($password !== $confirm) {
    $errors['confirm_password'] = 'Passwords do not match.';
}

// CNIC  – 12345-1234567-1
if (!preg_match('/^\d{5}-\d{7}-\d$/', $cnic)) {
    $errors['cnic'] = 'CNIC must follow the format 12345-1234567-1.';
}

// Phone  – 03XXXXXXXXX
if (!preg_match('/^03\d{9}$/', $phone)) {
    $errors['phone'] = 'Phone must follow the format 03XXXXXXXXX.';
}

// CGPA
if (!is_numeric($cgpaRaw)) {
    $errors['cgpa'] = 'CGPA must be a number.';
} else {
    $cgpa = (float) $cgpaRaw;
    if ($cgpa < 0.00 || $cgpa > 4.00) {
        $errors['cgpa'] = 'CGPA must be between 0.00 and 4.00.';
    }
}

// Department
$allowedDepts = ['Computer Science','Software Engineering','Information Technology',
                 'Electrical Engineering','Business Administration'];
if (!in_array($department, $allowedDepts, true)) {
    $errors['department'] = 'Please select a valid department.';
}

/* ══════════════════════════════════════════
   3.  FILE UPLOAD VALIDATION (Resume)
   ══════════════════════════════════════════ */
$resumePath = '';

if (!isset($_FILES['resume']) || $_FILES['resume']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors['resume'] = 'Resume upload is required.';
} elseif ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    $errors['resume'] = 'File upload failed (error code ' . (int)$_FILES['resume']['error'] . ').';
} else {
    $file = $_FILES['resume'];

    // Size check
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors['resume'] = 'Resume must be smaller than 2 MB.';
    }

    // Extension check  – never trust the browser-supplied name alone
    $originalName = basename($file['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        $errors['resume'] = 'Only PDF files are accepted.';
    }

    // MIME type check using fileinfo (reads magic bytes, not the header)
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, ALLOWED_MIME, true)) {
        $errors['resume'] = 'File content does not match a valid PDF.';
    }

    // If all checks passed, build a safe storage path
    if (!isset($errors['resume'])) {
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0750, true);
        }
        // Random name to prevent enumeration / path-traversal
        $safeName   = bin2hex(random_bytes(16)) . '.pdf';
        $resumePath = UPLOAD_DIR . $safeName;
    }
}

/* ══════════════════════════════════════════
   4.  DUPLICATE CHECK (before we move file)
   ══════════════════════════════════════════ */
if (empty($errors)) {
    try {
        $pdo  = getPDO();

        $chk  = $pdo->prepare(
            'SELECT
                SUM(student_id = ?) AS sid,
                SUM(email = ?)      AS em,
                SUM(cnic  = ?)      AS cn
             FROM students'
        );
        $chk->execute([$studentId, $email, $cnic]);
        $dup  = $chk->fetch();

        if ((int)$dup['sid'] > 0) $errors['student_id'] = 'Student ID is already registered.';
        if ((int)$dup['em']  > 0) $errors['email']      = 'E-mail is already registered.';
        if ((int)$dup['cn']  > 0) $errors['cnic']       = 'CNIC is already registered.';

    } catch (PDOException $e) {
        $errors['db'] = 'Database error. Please try again.';
    }
}

/* ══════════════════════════════════════════
   5.  PERSIST IF NO ERRORS
   ══════════════════════════════════════════ */
if (empty($errors)) {
    try {
        // Move the uploaded file now (after all validation)
        if (!move_uploaded_file($_FILES['resume']['tmp_name'], $resumePath)) {
            throw new RuntimeException('Could not save the uploaded file.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $pdo->prepare(
            'INSERT INTO students
                (student_id, full_name, email, password_hash, cnic, phone, cgpa, department, resume_path)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $studentId,
            $fullName,
            $email,
            $hash,
            $cnic,
            $phone,
            number_format($cgpa, 2, '.', ''),
            $department,
            // Store only the relative path – never the absolute server path
            'uploads/resumes/' . basename($resumePath),
        ]);

        // Success – redirect with a flash token (PRG pattern)
        $_SESSION['reg_success'] = true;
        header('Location: success.php');
        exit;

    } catch (PDOException | RuntimeException $e) {
        $errors['db'] = 'Registration failed. Please try again later.';
        // Clean up the file if DB insert failed
        if ($resumePath && file_exists($resumePath)) {
            unlink($resumePath);
        }
    }
}

/* ══════════════════════════════════════════
   6.  RETURN ERRORS TO FORM
   ══════════════════════════════════════════ */
$_SESSION['form_errors'] = $errors;
$_SESSION['form_data']   = [
    // Never re-send passwords or the file path
    'student_id'  => $studentId,
    'full_name'   => $fullName,
    'email'       => $email,
    'cnic'        => $cnic,
    'phone'       => $phone,
    'cgpa'        => $cgpaRaw,
    'department'  => $department,
];
header('Location: index.php');
exit;
