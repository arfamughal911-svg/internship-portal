<?php
// index.php  –  Registration form

session_start();

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_data']   ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Internship Registration Portal</title>
<style>
/* ── Reset ── */
*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* ── Body Background ── */
body{
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:2rem 1rem;

    background:
    radial-gradient(circle at 20% 30%, #4f46e5 0%, transparent 40%),
    radial-gradient(circle at 80% 70%, #06b6d4 0%, transparent 40%),
    #0f172a;

    color:#111;
}

/* ── Card Container ── */
.card{
    width:100%;
    max-width:750px;

    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(18px);

    border-radius:20px;

    border:1px solid rgba(255,255,255,0.35);

    box-shadow:
    0 40px 80px rgba(0,0,0,.35);

    overflow:hidden;

    animation: fadeIn .6s ease;
}

/* ── Header ── */
.card-header{
    padding:2.2rem 2.6rem;

    background:
    linear-gradient(135deg,#4f46e5,#06b6d4);

    color:#fff;

    text-align:center;
}

.card-header h1{
    font-size:1.7rem;
    font-weight:700;
    letter-spacing:.4px;
}

.card-header p{
    margin-top:.35rem;
    opacity:.9;
    font-size:.9rem;
}

/* ── Body ── */
.card-body{
    padding:2.3rem 2.6rem;
}

/* ── Grid Layout ── */
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:1.3rem;
}

.full{
    grid-column:1/-1;
}

/* ── Form Fields ── */
.field{
    display:flex;
    flex-direction:column;
    gap:.35rem;
}

/* Labels */
label{
    font-size:.8rem;
    font-weight:600;
    color:#374151;
    letter-spacing:.02em;
}

/* Inputs */
input,select{
    padding:.7rem .9rem;

    border-radius:10px;

    border:1.5px solid #e2e8f0;

    font-size:.9rem;

    background:#fff;

    transition: all .2s ease;

    outline:none;
}

/* Focus effect */
input:focus,
select:focus{

    border-color:#4f46e5;

    box-shadow:
    0 0 0 3px rgba(79,70,229,.15);

}

/* Valid/Invalid */
input.valid{
    border-color:#22c55e;
}

input.invalid{
    border-color:#ef4444;
}

/* Hint text */
.hint{
    font-size:.74rem;
    color:#6b7280;
}

/* Error message */
.error-msg{
    font-size:.75rem;
    color:#ef4444;
    display:none;
}

.error-msg.visible{
    display:block;
}

/* ── Email Status ── */
.email-status{
    font-size:.75rem;
    font-weight:600;
}

.email-status.checking{ color:#f59e0b; }
.email-status.ok{ color:#22c55e; }
.email-status.taken{ color:#ef4444; }

/* ── Password Strength ── */
.strength-bar-wrap{
    height:6px;
    background:#e5e7eb;
    border-radius:999px;
    margin-top:.4rem;
}

.strength-bar{
    height:100%;
    border-radius:999px;
    width:0;
    transition:.3s;
}

/* ── Drag & Drop Zone ── */
.drop-zone{

    border:2px dashed #cbd5e1;

    border-radius:12px;

    padding:1.6rem;

    text-align:center;

    cursor:pointer;

    background:#f8fafc;

    transition:.2s;

    position:relative;
}

.drop-zone:hover,
.drop-zone.drag{

    border-color:#4f46e5;

    background:#eef2ff;

}

.drop-zone input[type=file]{

    position:absolute;
    inset:0;
    opacity:0;
    cursor:pointer;

}

.drop-zone-icon{
    font-size:2rem;
}

.drop-zone-label{
    font-size:.85rem;
    color:#64748b;
    margin-top:.4rem;
}

#file-name{
    font-size:.8rem;
    color:#4f46e5;
    font-weight:600;
    margin-top:.4rem;
}

/* ── Submit Button ── */
.btn-submit{

    width:100%;

    margin-top:1.7rem;

    padding:.9rem;

    font-size:.95rem;

    font-weight:700;

    border:none;

    border-radius:12px;

    background:
    linear-gradient(135deg,#4f46e5,#06b6d4);

    color:white;

    letter-spacing:.03em;

    cursor:pointer;

    transition: all .2s ease;

}

/* Hover */
.btn-submit:hover:not(:disabled){

    transform: translateY(-1px);

    box-shadow:
    0 8px 20px rgba(79,70,229,.35);

}

/* Active */
.btn-submit:active{
    transform:scale(.97);
}

.btn-submit:disabled{
    opacity:.6;
    cursor:not-allowed;
}

/* ── Server Error Banner ── */
.server-errors{

    background:#fef2f2;

    border:1px solid #fca5a5;

    border-radius:10px;

    padding:1rem;

    margin-bottom:1.4rem;

    color:#b91c1c;

    font-size:.85rem;

}

.server-errors ul{
    padding-left:1.1rem;
}

/* ── Animation ── */
@keyframes fadeIn{
    from{
        opacity:0;
        transform:translateY(15px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* ── Mobile ── */
@media(max-width:540px){

.grid{
grid-template-columns:1fr;
}

.card-body,
.card-header{
padding:1.6rem 1.4rem;
}

}
</style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <h1>🎓 Internship Registration Portal</h1>
        <p>Complete the form below to apply for an internship placement</p>
    </div>

    <div class="card-body">

        <?php if (!empty($errors)): ?>
        <div class="server-errors">
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                <li><?= h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form id="regForm" method="POST" action="register.php" enctype="multipart/form-data" novalidate>

            <div class="grid">

                <!-- Student ID -->
                <div class="field">
                    <label for="student_id">Student ID *</label>
                    <input type="text" id="student_id" name="student_id"
                           placeholder="FA21-BCS-001"
                           value="<?= h($old['student_id'] ?? '') ?>"
                           maxlength="20" required>
                    <span class="hint">Format: FA21-BCS-001</span>
                    <span class="error-msg" id="err-student_id">⚠ Invalid Student ID format.</span>
                </div>

                <!-- Full Name -->
                <div class="field">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name"
                           placeholder="Muhammad Ali"
                           value="<?= h($old['full_name'] ?? '') ?>"
                           maxlength="100" required>
                    <span class="error-msg" id="err-full_name">⚠ Full name is required.</span>
                </div>

                <!-- Email -->
                <div class="field full">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email"
                           placeholder="student@university.edu.pk"
                           value="<?= h($old['email'] ?? '') ?>"
                           maxlength="150" required>
                    <span class="email-status" id="email-status"></span>
                    <span class="error-msg" id="err-email">⚠ Enter a valid e-mail address.</span>
                </div>

                <!-- Password -->
                <div class="field">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password"
                           placeholder="Min 8 chars" required>
                    <div class="strength-bar-wrap">
                        <div class="strength-bar" id="strength-bar"></div>
                    </div>
                    <span class="hint" id="strength-label">Password strength</span>
                    <span class="error-msg" id="err-password">⚠ Password too weak.</span>
                </div>

                <!-- Confirm Password -->
                <div class="field">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Re-enter password" required>
                    <span class="error-msg" id="err-confirm_password">⚠ Passwords do not match.</span>
                </div>

                <!-- CNIC -->
                <div class="field">
                    <label for="cnic">CNIC *</label>
                    <input type="text" id="cnic" name="cnic"
                           placeholder="12345-1234567-1"
                           value="<?= h($old['cnic'] ?? '') ?>"
                           maxlength="15" required>
                    <span class="hint">Format: 12345-1234567-1</span>
                    <span class="error-msg" id="err-cnic">⚠ Invalid CNIC format.</span>
                </div>

                <!-- Phone -->
                <div class="field">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone"
                           placeholder="03001234567"
                           value="<?= h($old['phone'] ?? '') ?>"
                           maxlength="11" required>
                    <span class="hint">Format: 03XXXXXXXXX</span>
                    <span class="error-msg" id="err-phone">⚠ Invalid phone number.</span>
                </div>

                <!-- CGPA -->
                <div class="field">
                    <label for="cgpa">CGPA *</label>
                    <input type="number" id="cgpa" name="cgpa"
                           placeholder="3.50" min="0" max="4" step="0.01"
                           value="<?= h($old['cgpa'] ?? '') ?>"
                           required>
                    <span class="hint">Range: 0.00 – 4.00</span>
                    <span class="error-msg" id="err-cgpa">⚠ CGPA must be between 0.00 and 4.00.</span>
                </div>

                <!-- Department -->
                <div class="field">
                    <label for="department">Department *</label>
                    <select id="department" name="department" required>
                        <option value="">— Select Department —</option>
                        <?php
                        $depts = ['Computer Science','Software Engineering',
                                  'Information Technology','Electrical Engineering',
                                  'Business Administration'];
                        foreach ($depts as $d):
                            $sel = ($old['department'] ?? '') === $d ? 'selected' : '';
                        ?>
                        <option value="<?= h($d) ?>" <?= $sel ?>><?= h($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="error-msg" id="err-department">⚠ Please select a department.</span>
                </div>

                <!-- Resume Upload -->
                <div class="field full">
                    <label>Resume (PDF only, max 2 MB) *</label>
                    <div class="drop-zone" id="dropZone">
                        <input type="file" id="resume" name="resume"
                               accept=".pdf,application/pdf" required>
                        <div class="drop-zone-icon">📄</div>
                        <div class="drop-zone-label">Drag &amp; drop your PDF here, or <strong>click to browse</strong></div>
                        <div id="file-name"></div>
                    </div>
                    <span class="error-msg" id="err-resume">⚠ Please upload a valid PDF (≤ 2 MB).</span>
                </div>

            </div><!-- /grid -->

            <button type="submit" class="btn-submit" id="submitBtn">
                Submit Application
            </button>

        </form>
    </div><!-- /card-body -->
</div><!-- /card -->

<!-- ══════════════════════════════════════════
     JavaScript  –  Client-Side Validation
     ══════════════════════════════════════════ -->
<script>
'use strict';

/* ── Patterns ── */
const PATTERNS = {
    studentId : /^[A-Z]{2}\d{2}-[A-Z]{2,4}-\d{3}$/,
    email     : /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    password  : /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/,
    cnic      : /^\d{5}-\d{7}-\d$/,
    phone     : /^03\d{9}$/,
};

/* ── DOM helpers ── */
const $  = (id) => document.getElementById(id);
const markValid   = (id) => { $(id).classList.add('valid');   $(id).classList.remove('invalid'); };
const markInvalid = (id) => { $(id).classList.add('invalid'); $(id).classList.remove('valid');   };
const showError   = (id)  => $('err-' + id).classList.add('visible');
const hideError   = (id)  => $('err-' + id).classList.remove('visible');
const setField    = (id, ok) => { ok ? (markValid(id), hideError(id)) : (markInvalid(id), showError(id)); return ok; };

/* ── Field validators ── */
const validators = {
    student_id(v)  { return setField('student_id',       PATTERNS.studentId.test(v)); },
    full_name(v)   { return setField('full_name',         v.trim().length > 0 && v.trim().length <= 100); },
    email(v)       { return setField('email',             PATTERNS.email.test(v)); },
    password(v)    {
        const ok = PATTERNS.password.test(v);
        setField('password', ok);
        updateStrengthBar(v);
        return ok;
    },
    confirm_password(v) {
        return setField('confirm_password', v !== '' && v === $('password').value);
    },
    cnic(v)        { return setField('cnic',  PATTERNS.cnic.test(v)); },
    phone(v)       { return setField('phone', PATTERNS.phone.test(v)); },
    cgpa(v)        { const n = parseFloat(v); return setField('cgpa', !isNaN(n) && n >= 0 && n <= 4); },
    department(v)  { return setField('department', v !== ''); },
    resume(file)   {
        if (!file) return setField('resume', false);
        const ok = file.type === 'application/pdf' && file.size <= 2 * 1024 * 1024;
        return setField('resume', ok);
    },
};

/* ── Password strength bar ── */
function updateStrengthBar(pw) {
    let score = 0;
    if (pw.length >= 8)          score++;
    if (/[a-z]/.test(pw))        score++;
    if (/[A-Z]/.test(pw))        score++;
    if (/\d/.test(pw))           score++;
    if (/[\W_]/.test(pw))        score++;

    const bar    = $('strength-bar');
    const label  = $('strength-label');
    const levels = [
        { pct: '0%',   bg: '#e5e7eb', txt: 'Password strength' },
        { pct: '20%',  bg: '#ef4444', txt: 'Very weak' },
        { pct: '40%',  bg: '#f97316', txt: 'Weak' },
        { pct: '60%',  bg: '#eab308', txt: 'Fair' },
        { pct: '80%',  bg: '#84cc16', txt: 'Strong' },
        { pct: '100%', bg: '#22c55e', txt: 'Very strong' },
    ];
    const l = levels[score];
    bar.style.width      = l.pct;
    bar.style.background = l.bg;
    label.textContent    = l.txt;
    label.style.color    = l.bg;
}

/* ── Live validation listeners ── */
['student_id','full_name','cnic','phone','cgpa'].forEach(id => {
    $(id).addEventListener('input', () => validators[id]($(id).value));
});

$('department').addEventListener('change', () => validators.department($('department').value));

$('password').addEventListener('input', () => {
    validators.password($('password').value);
    if ($('confirm_password').value) validators.confirm_password($('confirm_password').value);
});
$('confirm_password').addEventListener('input', () => validators.confirm_password($('confirm_password').value));

/* ── Email: debounced AJAX check ── */
let emailTimer;
$('email').addEventListener('input', () => {
    const v = $('email').value;
    clearTimeout(emailTimer);
    $('email-status').className = 'email-status';
    $('email-status').textContent = '';

    if (!validators.email(v)) return;

    $('email-status').className = 'email-status checking';
    $('email-status').textContent = '⏳ Checking availability…';

    emailTimer = setTimeout(() => checkEmailAvailability(v), 600);
});

async function checkEmailAvailability(email) {
    try {
        const fd = new FormData();
        fd.append('email', email);
        const res  = await fetch('check_email.php', { method: 'POST', body: fd });
        const data = await res.json();
        const status = $('email-status');

        if (data.available) {
            status.className   = 'email-status ok';
            status.textContent = '✔ ' + data.message;
            markValid('email'); hideError('email');
        } else {
            status.className   = 'email-status taken';
            status.textContent = '✖ ' + data.message;
            markInvalid('email'); showError('email');
            $('err-email').textContent = '⚠ ' + data.message;
        }
    } catch (_) {
        $('email-status').className   = 'email-status taken';
        $('email-status').textContent = 'Could not verify e-mail.';
    }
}

/* ── File upload ── */
$('resume').addEventListener('change', () => {
    const file = $('resume').files[0];
    $('file-name').textContent = file ? file.name : '';
    validators.resume(file || null);
});

/* ── Drag-and-drop styling ── */
const dz = $('dropZone');
dz.addEventListener('dragover', (e) => { e.preventDefault(); dz.classList.add('drag'); });
dz.addEventListener('dragleave', ()  => dz.classList.remove('drag'));
dz.addEventListener('drop',      ()  => dz.classList.remove('drag'));

/* ── Form submit ── */
$('regForm').addEventListener('submit', (e) => {
    // Run all validators at once to surface all errors
    const results = [
        validators.student_id($('student_id').value),
        validators.full_name($('full_name').value),
        validators.email($('email').value),
        validators.password($('password').value),
        validators.confirm_password($('confirm_password').value),
        validators.cnic($('cnic').value),
        validators.phone($('phone').value),
        validators.cgpa($('cgpa').value),
        validators.department($('department').value),
        validators.resume($('resume').files[0] || null),
    ];

    if (results.includes(false)) {
        e.preventDefault();
        // Scroll to first error
        document.querySelector('.invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    $('submitBtn').disabled     = true;
    $('submitBtn').textContent  = '⏳ Submitting…';
});
</script>

</body>
</html>
