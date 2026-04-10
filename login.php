<?php
require_once 'config.php';

if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$error   = '';
$success = '';
$mode    = $_GET['mode'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db    = getDB();
    $mode  = $_POST['mode'] ?? 'login';
    $email = trim($_POST['email']    ?? '');
    $pass  = $_POST['password']      ?? '';

    if ($mode === 'register') {
        $nama  = trim($_POST['nama']      ?? '');
        $pass2 = $_POST['password2']      ?? '';
        if (!$nama || !$email || !$pass)                    $error = 'Semua kolom wajib diisi.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Format email tidak valid.';
        elseif (strlen($pass) < 6)                          $error = 'Password minimal 6 karakter.';
        elseif ($pass !== $pass2)                           $error = 'Konfirmasi password tidak cocok.';
        else {
            try {
                $db->prepare('INSERT INTO users (nama,email,password) VALUES (?,?,?)')
                   ->execute([$nama, $email, password_hash($pass, PASSWORD_BCRYPT)]);
                $success = 'Akun berhasil dibuat! Silakan masuk.';
                $mode    = 'login';
            } catch (PDOException) { $error = 'Email sudah terdaftar.'; }
        }
    } else {
        if (!$email || !$pass) { $error = 'Email dan password wajib diisi.'; }
        else {
            $stmt = $db->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($pass, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_nama']  = $user['nama'];
                $_SESSION['user_email'] = $user['email'];
                header('Location: index.php'); exit;
            } else { $error = 'Email atau password salah.'; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>TaskFlow — <?= $mode === 'register' ? 'Daftar' : 'Masuk' ?></title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0d0f14;--surface:#161920;--surface2:#1e2230;--border:#2a2f40;
  --text:#e8eaf0;--muted:#6b7280;--accent:#6ee7b7;--accent2:#34d399;
  --danger:#f87171;--info:#60a5fa;--radius:14px;--tr:.18s ease;
}
body{
  background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;
  min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.03'/%3E%3C/svg%3E");
}
body::before,body::after{content:'';position:fixed;border-radius:50%;filter:blur(90px);opacity:.1;pointer-events:none;}
body::before{width:500px;height:500px;background:radial-gradient(#6ee7b7,transparent 70%);top:-120px;left:-120px;}
body::after{width:420px;height:420px;background:radial-gradient(#a78bfa,transparent 70%);bottom:-100px;right:-100px;}

.card{
  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
  padding:40px 36px;width:100%;max-width:420px;
  box-shadow:0 24px 64px rgba(0,0,0,.55);position:relative;z-index:1;
  animation:rise .3s ease both;
}
@keyframes rise{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

.logo{font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:800;letter-spacing:-1px;
  background:linear-gradient(135deg,var(--accent),#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:4px;}
.subtitle{font-size:13px;color:var(--muted);margin-bottom:30px;}

.tabs{display:flex;background:var(--surface2);border-radius:10px;padding:4px;margin-bottom:26px;gap:4px;}
.tab{flex:1;text-align:center;padding:9px;border-radius:8px;font-family:'Syne',sans-serif;
  font-size:13px;font-weight:700;cursor:pointer;color:var(--muted);transition:all var(--tr);
  border:none;background:none;}
.tab.active{background:var(--surface);color:var(--text);box-shadow:0 2px 8px rgba(0,0,0,.3);}

.alert{padding:11px 15px;border-radius:9px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px;}
.alert-error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);color:var(--danger);}
.alert-success{background:rgba(110,231,183,.1);border:1px solid rgba(110,231,183,.25);color:var(--accent);}

.fg{display:flex;flex-direction:column;gap:6px;margin-bottom:14px;}
.fg label{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;font-weight:600;}
.iw{position:relative;}
.iw svg.ico{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--muted);pointer-events:none;}
input[type=text],input[type=email],input[type=password]{
  width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:9px;
  color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;padding:11px 40px 11px 40px;outline:none;
  transition:border-color var(--tr),box-shadow var(--tr);}
input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(110,231,183,.12);}
input::placeholder{color:var(--muted);}
.eye{position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;
  cursor:pointer;color:var(--muted);padding:4px;transition:color var(--tr);}
.eye:hover{color:var(--text);}

.btn-sub{width:100%;padding:13px;background:var(--accent);color:#0d0f14;border:none;border-radius:9px;
  font-family:'Syne',sans-serif;font-size:15px;font-weight:700;cursor:pointer;margin-top:6px;
  transition:all var(--tr);}
.btn-sub:hover{background:var(--accent2);transform:translateY(-1px);box-shadow:0 6px 18px rgba(110,231,183,.3);}

.demo{margin-top:20px;padding:13px 16px;background:rgba(96,165,250,.07);
  border:1px dashed rgba(96,165,250,.25);border-radius:9px;font-size:12px;
  color:var(--info);text-align:center;line-height:1.8;}
.demo strong{display:block;margin-bottom:1px;}
</style>
</head>
<body>
<div class="card">
  <div class="logo">TaskFlow</div>
  <div class="subtitle">Kelola tugas Anda dengan lebih efisien</div>

  <div class="tabs">
    <button type="button" class="tab <?= $mode==='login'?'active':'' ?>" onclick="switchMode('login')">Masuk</button>
    <button type="button" class="tab <?= $mode==='register'?'active':'' ?>" onclick="switchMode('register')">Daftar</button>
  </div>

  <?php if($error):?>
  <div class="alert alert-error">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r=".5" fill="currentColor"/></svg>
    <?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>
  <?php if($success):?>
  <div class="alert alert-success">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    <?= htmlspecialchars($success) ?>
  </div>
  <?php endif; ?>

  <form method="POST" id="mainForm">
    <input type="hidden" name="mode" id="modeInput" value="<?= htmlspecialchars($mode) ?>">

    <!-- Nama (register only) -->
    <div class="fg" id="namaField" style="<?= $mode==='register'?'':'display:none' ?>">
      <label>Nama Lengkap</label>
      <div class="iw">
        <svg class="ico" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        <input type="text" name="nama" placeholder="Nama Anda…" autocomplete="name"
               value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
      </div>
    </div>

    <!-- Email -->
    <div class="fg">
      <label>Email</label>
      <div class="iw">
        <svg class="ico" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>
        <input type="email" name="email" placeholder="email@contoh.com" required autocomplete="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
    </div>

    <!-- Password -->
    <div class="fg">
      <label>Password</label>
      <div class="iw">
        <svg class="ico" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <input type="password" name="password" id="pw1" placeholder="••••••••" required autocomplete="current-password">
        <button type="button" class="eye" onclick="togglePw('pw1',this)" title="Tampilkan">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
    </div>

    <!-- Konfirmasi Password (register only) -->
    <div class="fg" id="pw2Field" style="<?= $mode==='register'?'':'display:none' ?>">
      <label>Konfirmasi Password</label>
      <div class="iw">
        <svg class="ico" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <input type="password" name="password2" id="pw2" placeholder="••••••••" autocomplete="new-password">
        <button type="button" class="eye" onclick="togglePw('pw2',this)" title="Tampilkan">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
    </div>

    <button type="submit" class="btn-sub" id="submitBtn">
      <?= $mode === 'register' ? 'Buat Akun' : 'Masuk' ?>
    </button>
  </form>

  <?php if($mode==='login'):?>
  <div class="demo">
    <strong>🔑 Akun Demo</strong>
    admin@taskflow.id &nbsp;/&nbsp; admin123
  </div>
  <?php endif; ?>
</div>

<script>
function switchMode(m) {
  document.getElementById('modeInput').value = m;
  document.getElementById('namaField').style.display = m==='register' ? '' : 'none';
  document.getElementById('pw2Field').style.display  = m==='register' ? '' : 'none';
  document.getElementById('submitBtn').textContent   = m==='register' ? 'Buat Akun' : 'Masuk';
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  event.target.classList.add('active');
  // Hapus alert lama
  document.querySelectorAll('.alert').forEach(a => a.remove());
}
function togglePw(id, btn) {
  const inp = document.getElementById(id);
  const vis = inp.type === 'password';
  inp.type = vis ? 'text' : 'password';
  btn.style.color = vis ? 'var(--accent)' : '';
}
</script>
</body>
</html>
