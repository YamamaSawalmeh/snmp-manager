<?php
putenv("MIBS=");
$ip        = "127.0.0.1";
$community = "public";
$agentName = @snmp2_get($ip, $community, ".1.3.6.1.2.1.1.5.0");
$agentName = $agentName ? str_replace(['"', 'STRING: '], '', $agentName) : 'SNMP Agent';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SNMP Manager – Home</title>
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
*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
body { font-family:var(--font-body); background:var(--bg); color:var(--primary); min-height:100vh; }

.navbar {
    background:var(--primary); display:flex; align-items:center;
    justify-content:space-between; padding:0 28px; height:56px;
    box-shadow:0 2px 8px rgba(0,0,0,.18); position:sticky; top:0; z-index:100;
}
.navbar .brand { color:#fff; font-weight:700; font-size:1rem; text-decoration:none; letter-spacing:.5px; }
.navbar .brand span { color:#60a5fa; }
.navbar nav { display:flex; gap:4px; }
.navbar nav a {
    color:#94a3b8; text-decoration:none; font-size:0.85rem; font-weight:500;
    padding:6px 14px; border-radius:6px; transition:background .15s,color .15s;
}
.navbar nav a:hover, .navbar nav a.active { background:rgba(255,255,255,.1); color:#fff; }

.main-content { max-width: 900px; margin: 0 auto; padding: 60px 24px; }

.hero { text-align:center; margin-bottom:52px; }
.hero h1 { font-size:2.2rem; font-weight:700; color:var(--primary); }
.hero p  { margin-top:10px; color:var(--muted); font-size:1rem; }
.agent-chip {
    display:inline-block; margin-top:16px;
    background:var(--accent-light); color:var(--accent);
    font-size:0.82rem; font-family:var(--font-mono);
    padding:5px 16px; border-radius:20px; border:1px solid #bfdbfe;
}

.pages-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:20px; }
.page-card {
    background:var(--card-bg); border:1px solid var(--border); border-radius:12px;
    box-shadow:var(--shadow); padding:28px 24px; text-align:center;
    text-decoration:none; color:inherit; transition:transform .2s, box-shadow .2s; display:block;
}
.page-card:hover { transform:translateY(-4px); box-shadow:0 10px 28px rgba(0,0,0,.12); }
.page-card .pg-icon { font-size:2rem; margin-bottom:16px; display:block; }
.page-card h3 { font-size:1.05rem; font-weight:700; margin-bottom:8px; }
.page-card p  { font-size:0.85rem; color:var(--muted); line-height:1.6; }
.page-card .pg-num {
    display:inline-block; margin-top:18px;
    background:var(--accent); color:white;
    font-size:0.72rem; font-weight:700;
    padding:4px 12px; border-radius:20px;
}
</style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="brand">🌐 <span>SNMP</span> Manager</a>
    <nav>
        <a href="index.php" class="active">Home</a>
        <a href="page1.php">Page 1</a>
        <a href="page2.php">Page 2</a>
        <a href="page3.php">Page 3</a>
    </nav>
</nav>

<main class="main-content">
    <div class="hero">
        <h1>🌐 SNMP Manager</h1>
        <p>Computer Networks 2 — An-Najah National University</p>
        <div class="agent-chip">Agent: <?php echo htmlspecialchars($agentName); ?> (<?php echo $ip; ?>)</div>
    </div>

    <div class="pages-grid">
        <a href="page1.php" class="page-card">
            
            <h3>System Group</h3>
            <p>View and edit sysName, sysContact, and sysLocation via SNMP GET / SET.</p>
            <span class="pg-num">Page 1</span>
        </a>
        <a href="page2.php" class="page-card">
            
            <h3>UDP Group</h3>
            <p>UDP statistics and active connection table from the MIB-2 UDP group.</p>
            <span class="pg-num">Page 2</span>
        </a>
        <a href="page3.php" class="page-card">
            
            <h3>SNMP Statistics</h3>
            <p>Full SNMP group stats via snmp2_get() and snmp2_walk() side by side.</p>
            <span class="pg-num">Page 3</span>
        </a>
    </div>
</main>
</body>
</html>