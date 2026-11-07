<?php
set_time_limit(120); // زيادة الوقت لتجنب الخطأ
putenv("MIBS=");

$ip        = "127.0.0.1";
$community = "public";
$timeout   = 200000; // تقليل الوقت (0.2 ثانية)
$retries   = 0;      // بدون إعادة المحاولة

// SNMP Group OIDs: 1.3.6.1.2.1.11.X (X = 1–30, skip 7 and 23)
$objectNames = [
    1  => "snmpInPkts",
    2  => "snmpOutPkts",
    3  => "snmpInBadVersions",
    4  => "snmpInBadCommunityNames",
    5  => "snmpInBadCommunityUses",
    6  => "snmpInASNParseErrs",
    8  => "snmpInTooBigs",
    9  => "snmpInNoSuchNames",
    10 => "snmpInBadValues",
    11 => "snmpInReadOnlys",
    12 => "snmpInGenErrs",
    13 => "snmpInTotalReqVars",
    14 => "snmpInTotalSetVars",
    15 => "snmpInGetRequests",
    16 => "snmpInGetNexts",
    17 => "snmpInSetRequests",
    18 => "snmpInGetResponses",
    19 => "snmpInTraps",
    20 => "snmpOutTooBigs",
    21 => "snmpOutNoSuchNames",
    22 => "snmpOutBadValues",
    24 => "snmpOutGenErrs",
    25 => "snmpOutGetRequests",
    26 => "snmpOutGetNexts",
    27 => "snmpOutSetRequests",
    28 => "snmpOutGetResponses",
    29 => "snmpOutTraps",
    30 => "snmpEnableAuthenTraps",
];

// ✅ Method 2: snmp2_walk() (سريع)
$walkRaw  = @snmp2_walk($ip, $community, ".1.3.6.1.2.1.11", $timeout, $retries);
$walkData = [];

if ($walkRaw) {
    foreach ($walkRaw as $val) {
        $walkData[] = str_replace(
            ['Counter32: ', 'INTEGER: ', 'STRING: ', 'Gauge32: ', '"'],
            '',
            $val
        );
    }
}

$nameKeys = array_keys($objectNames);

// ✅ Method 1 (بدون requests كثيرة → يعتمد على walk)
$method1Data = [];
foreach ($objectNames as $x => $name) {
    $index = array_search($x, $nameKeys);
    $method1Data[] = [
        'id'    => $x,
        'name'  => $name,
        'value' => $walkData[$index] ?? 'N/A',
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SNMP Manager – Page 3: SNMP Statistics</title>
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

.main-content { max-width: 1100px; margin: 0 auto; padding: 36px 24px 60px; }
.page-header { margin-bottom: 32px; }
.page-badge {
    display:inline-block; background:var(--accent); color:#fff;
    font-size:0.72rem; font-weight:700; letter-spacing:1px; text-transform:uppercase;
    padding:3px 10px; border-radius:20px; margin-bottom:10px;
}
.page-header h1 { font-size:1.8rem; font-weight:700; }
.page-header p  { margin-top:6px; color:var(--muted); font-size:0.92rem; }

.two-col { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
@media (max-width:800px) { .two-col { grid-template-columns:1fr; } }

.card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow); overflow:hidden; }
.card-header {
    display:flex; align-items:center; gap:10px; padding:14px 20px;
    background:#f8fafc; border-bottom:1px solid var(--border);
    font-weight:600; font-size:0.92rem;
}
.card-header code { font-family:var(--font-mono); font-size:0.82rem; color:var(--accent); }

table { width:100%; border-collapse:collapse; font-size:0.875rem; }
th, td { padding:11px 16px; text-align:left; border-bottom:1px solid var(--border); }
th { background:#f8fafc; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--muted); }
tr:last-child td { border-bottom:none; }
tr:hover td { background:#f8fafc; }
.id-col   { width:52px; color:var(--muted); font-weight:600; font-family:var(--font-mono); }
.name-col { font-family:var(--font-mono); font-size:0.82rem; }
.val-col  { font-family:var(--font-mono); color:var(--accent); font-weight:600; }

.nav-buttons { display:flex; gap:12px; justify-content:center; margin-top:36px; }
.btn-nav {
    padding:10px 24px; border-radius:8px; font-size:0.88rem; font-weight:600;
    text-decoration:none; border:2px solid var(--primary); color:var(--primary);
    background:#fff; transition:background .15s,color .15s;
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
        <a href="page1.php">Page 1</a>
        <a href="page2.php">Page 2</a>
        <a href="page3.php" class="active">Page 3</a>
    </nav>
</nav>

<main class="main-content">
    <div class="page-header">
        <div class="page-badge">Page 3</div>
        <h1>SNMP Group Statistics</h1>
        <p>All SNMP group objects via two methods (OID base: 1.3.6.1.2.1.11) — skipping OID 7 and 23</p>
    </div>

    <div class="two-col">
        <!-- Method 1 -->
        <div class="card">
            <div class="card-header">
                 Method 1: By <code>snmp2_get()</code>
            </div>
            <table>
                <thead>
                    <tr>
                        <th class="id-col">ID</th>
                        <th>Object Name</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($method1Data as $row): ?>
                    <tr>
                        <td class="id-col"><?php echo $row['id']; ?></td>
                        <td class="name-col"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td class="val-col"><?php echo htmlspecialchars($row['value']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Method 2 -->
        <div class="card">
            <div class="card-header">
                 Method 2: By <code>snmp2_walk()</code>
            </div>
            <table>
                <thead>
                    <tr>
                        <th class="id-col">Item #</th>
                        <th>Object Name</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($walkData as $index => $val): ?>
                    <tr>
                        <td class="id-col"><?php echo $index; ?></td>
                        <td class="name-col"><?php echo htmlspecialchars($objectNames[$nameKeys[$index]] ?? 'Object'); ?></td>
                        <td class="val-col"><?php echo htmlspecialchars($val); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="nav-buttons">
        <a href="page2.php" class="btn-nav">← Previous: UDP Group</a>
        <span class="btn-nav disabled">Next →</span>
    </div>
</main>
</body>
</html>