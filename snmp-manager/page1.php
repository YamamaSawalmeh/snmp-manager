<?php
putenv("MIBS=");

$ip              = "127.0.0.1";
$read_community  = "public";
$write_community = "private";

$oids = [
    'sysName'     => '.1.3.6.1.2.1.1.5.0',
    'sysContact'  => '.1.3.6.1.2.1.1.4.0',
    'sysLocation' => '.1.3.6.1.2.1.1.6.0',
];

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $res1 = @snmp2_set($ip, $write_community, $oids['sysName'],     "s", $_POST['sysName']);
    $res2 = @snmp2_set($ip, $write_community, $oids['sysContact'],  "s", $_POST['sysContact']);
    $res3 = @snmp2_set($ip, $write_community, $oids['sysLocation'], "s", $_POST['sysLocation']);
    $message = ($res1 && $res2 && $res3)
        ? "<div class='alert alert-success'>✔ Data updated successfully!</div>"
        : "<div class='alert alert-error'>✘ Update failed. Check Write Community settings.</div>";
}

$currentName     = @snmp2_get($ip, $read_community, $oids['sysName']);
$currentContact  = @snmp2_get($ip, $read_community, $oids['sysContact']);
$currentLocation = @snmp2_get($ip, $read_community, $oids['sysLocation']);

function clean($str) {
    return htmlspecialchars(str_replace(['STRING: ', '"'], '', $str));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SNMP Manager – Page 1: System Group</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap');
:root {
    --primary:      #1a2744;
    --accent:       #2563eb;
    --accent-light: #eff6ff;
    --bg:           #f1f5f9;
    --card-bg:      #ffffff;
    --border:       #e2e8f0;
    --muted:        #64748b;
    --shadow:       0 2px 12px rgba(0,0,0,.07);
    --font-body:    'IBM Plex Sans', sans-serif;
    --font-mono:    'IBM Plex Mono', monospace;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: var(--font-body); background: var(--bg); color: var(--primary); min-height: 100vh; }

/* Navbar */
.navbar {
    background: var(--primary); display: flex; align-items: center;
    justify-content: space-between; padding: 0 28px; height: 56px;
    box-shadow: 0 2px 8px rgba(0,0,0,.18); position: sticky; top: 0; z-index: 100;
}
.navbar .brand { color:#fff; font-weight:700; font-size:1rem; text-decoration:none; letter-spacing:.5px; }
.navbar .brand span { color:#60a5fa; }
.navbar nav { display:flex; gap:4px; }
.navbar nav a {
    color:#94a3b8; text-decoration:none; font-size:0.85rem; font-weight:500;
    padding:6px 14px; border-radius:6px; transition:background .15s,color .15s;
}
.navbar nav a:hover, .navbar nav a.active { background:rgba(255,255,255,.1); color:#fff; }

/* Layout */
.main-content { max-width: 700px; margin: 0 auto; padding: 36px 24px 60px; }
.page-header { margin-bottom: 32px; }
.page-badge {
    display:inline-block; background:var(--accent); color:#fff;
    font-size:0.72rem; font-weight:700; letter-spacing:1px; text-transform:uppercase;
    padding:3px 10px; border-radius:20px; margin-bottom:10px;
}
.page-header h1 { font-size:1.8rem; font-weight:700; }
.page-header p  { margin-top:6px; color:var(--muted); font-size:0.92rem; }

/* Card */
.card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow); overflow:hidden; }
.card-header {
    display:flex; align-items:center; gap:10px; padding:14px 20px;
    background:#f8fafc; border-bottom:1px solid var(--border);
    font-weight:600; font-size:0.92rem;
}
.badge {
    margin-left:auto; background:var(--accent-light); color:var(--accent);
    font-size:0.75rem; font-family:var(--font-mono); padding:3px 10px;
    border-radius:20px; border:1px solid #bfdbfe;
}

/* Form */
.form { padding: 24px; }
.form-group { margin-bottom: 22px; }
.form-group label {
    display:flex; align-items:center; gap:8px; flex-wrap:wrap;
    margin-bottom:8px; font-weight:600; font-size:0.88rem;
}
.form-group label code {
    font-family:var(--font-mono); font-size:0.8rem;
    background:#f1f5f9; padding:2px 6px; border-radius:4px; color:var(--accent);
}
.oid-tag { margin-left:auto; font-size:0.72rem; color:var(--muted); font-family:var(--font-mono); font-weight:400; }
.form-group input[type="text"] {
    width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:8px;
    font-family:var(--font-mono); font-size:0.9rem; color:var(--primary);
    background:#fafcff; transition:border-color .15s, box-shadow .15s;
}
.form-group input[type="text"]:focus {
    outline:none; border-color:var(--accent);
    box-shadow:0 0 0 3px rgba(37,99,235,.12); background:#fff;
}
.btn-primary {
    width:100%; padding:12px; background:var(--accent); color:white;
    font-family:var(--font-body); font-weight:600; font-size:0.95rem;
    border:none; border-radius:8px; cursor:pointer; transition:background .15s;
}
.btn-primary:hover { background:#1d4ed8; }

/* Alerts */
.alert { padding:12px 18px; border-radius:8px; font-size:0.88rem; font-weight:500; margin-bottom:20px; }
.alert-success { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
.alert-error   { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }

/* Nav buttons */
.nav-buttons { display:flex; gap:12px; justify-content:center; margin-top:36px; }
.btn-nav {
    padding:10px 24px; border-radius:8px; font-size:0.88rem; font-weight:600;
    text-decoration:none; border:2px solid var(--primary); color:var(--primary);
    background:#fff; transition:background .15s, color .15s;
}
.btn-nav:hover { background:var(--primary); color:#fff; }
.btn-nav.disabled { opacity:.35; pointer-events:none; }
</style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="brand">🌐 <span>SNMP</span> Manager</a>
    <nav>
        <a href="index.php">Home</a>
        <a href="page1.php" class="active">Page 1</a>
        <a href="page2.php">Page 2</a>
        <a href="page3.php">Page 3</a>
    </nav>
</nav>

<main class="main-content">
    <div class="page-header">
        <div class="page-badge">Page 1</div>
        <h1>System Group</h1>
        <p>View and edit system identification fields via SNMP GET / SET</p>
    </div>

    <div class="card">
        <div class="card-header">
            <span></span> System Configuration
            <span class="badge">Agent: <?php echo $ip; ?></span>
        </div>
        <div class="form">
            <?php if ($message) echo $message; ?>
            <form method="POST">
                <div class="form-group">
                    <label>
                         System Name <code>sysName</code>
                        <span class="oid-tag">OID: 1.3.6.1.2.1.1.5.0</span>
                    </label>
                    <input type="text" name="sysName" value="<?php echo clean($currentName); ?>" required>
                </div>
                <div class="form-group">
                    <label>
                         System Contact <code>sysContact</code>
                        <span class="oid-tag">OID: 1.3.6.1.2.1.1.4.0</span>
                    </label>
                    <input type="text" name="sysContact" value="<?php echo clean($currentContact); ?>" required>
                </div>
                <div class="form-group">
                    <label>
                         System Location <code>sysLocation</code>
                        <span class="oid-tag">OID: 1.3.6.1.2.1.1.6.0</span>
                    </label>
                    <input type="text" name="sysLocation" value="<?php echo clean($currentLocation); ?>" required>
                </div>
                <button type="submit" name="update" class="btn-primary">Update Configuration</button>
            </form>
        </div>
    </div>

    <div class="nav-buttons">
        <span class="btn-nav disabled">← Previous</span>
        <a href="page2.php" class="btn-nav">Next: UDP Group →</a>
    </div>
</main>
</body>
</html>