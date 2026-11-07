<?php
putenv("MIBS=");

$ip        = "127.0.0.1";
$community = "public";

// UDP Statistics OIDs (MIB-2 UDP group: 1.3.6.1.2.1.7)
$udpStats = [
    'udpInDatagrams'  => ['oid' => '.1.3.6.1.2.1.7.1.0', 'desc' => 'Total UDP datagrams delivered to applications'],
    'udpNoPorts'      => ['oid' => '.1.3.6.1.2.1.7.2.0', 'desc' => 'Datagrams with no application at destination port'],
    'udpInErrors'     => ['oid' => '.1.3.6.1.2.1.7.3.0', 'desc' => 'Datagrams that could not be delivered (other errors)'],
    'udpOutDatagrams' => ['oid' => '.1.3.6.1.2.1.7.4.0', 'desc' => 'Total UDP datagrams sent from this entity'],
];

$statsData = [];
foreach ($udpStats as $name => $info) {
    $raw = @snmp2_get($ip, $community, $info['oid']);
    $val = $raw !== false ? str_replace(['"', 'Counter32: ', 'Gauge32: ', 'INTEGER: '], '', $raw) : 'N/A';
    $statsData[] = ['name' => $name, 'oid' => $info['oid'], 'desc' => $info['desc'], 'value' => $val];
}

// UDP Table: udpLocalAddress + udpLocalPort
$tableData  = [];
$localAddrs = @snmp2_walk($ip, $community, '.1.3.6.1.2.1.7.5.1.1');
$localPorts = @snmp2_walk($ip, $community, '.1.3.6.1.2.1.7.5.1.2');
if ($localAddrs && $localPorts) {
    $addrs = array_values($localAddrs);
    $ports = array_values($localPorts);
    for ($i = 0; $i < count($addrs); $i++) {
        $tableData[] = [
            'index'   => $i + 1,
            'address' => str_replace(['"', 'IpAddress: ', 'STRING: '], '', $addrs[$i]),
            'port'    => str_replace(['INTEGER: ', 'Gauge32: '], '', $ports[$i]),
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SNMP Manager – Page 2: UDP Group</title>
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

.main-content { max-width: 1000px; margin: 0 auto; padding: 36px 24px 60px; }
.page-header { margin-bottom: 32px; }
.page-badge {
    display:inline-block; background:var(--accent); color:#fff;
    font-size:0.72rem; font-weight:700; letter-spacing:1px; text-transform:uppercase;
    padding:3px 10px; border-radius:20px; margin-bottom:10px;
}
.page-header h1 { font-size:1.8rem; font-weight:700; }
.page-header p  { margin-top:6px; color:var(--muted); font-size:0.92rem; }

.card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow); overflow:hidden; margin-bottom:28px; }
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

table { width:100%; border-collapse:collapse; font-size:0.875rem; }
th, td { padding:11px 16px; text-align:left; border-bottom:1px solid var(--border); }
th { background:#f8fafc; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--muted); }
tr:last-child td { border-bottom:none; }
tr:hover td { background:#f8fafc; }
.id-col   { width:52px; color:var(--muted); font-weight:600; font-family:var(--font-mono); }
.name-col { font-family:var(--font-mono); font-size:0.82rem; }
.val-col  { font-family:var(--font-mono); color:var(--accent); font-weight:600; }
.desc-col { color:var(--muted); font-size:0.83rem; }

.empty-state { padding:32px; text-align:center; color:var(--muted); font-size:0.9rem; }

.nav-buttons { display:flex; gap:12px; justify-content:center; margin-top:36px; }
.btn-nav {
    padding:10px 24px; border-radius:8px; font-size:0.88rem; font-weight:600;
    text-decoration:none; border:2px solid var(--primary); color:var(--primary);
    background:#fff; transition:background .15s,color .15s;
}
.btn-nav:hover { background:var(--primary); color:#fff; }
</style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="brand">🌐 <span>SNMP</span> Manager</a>
    <nav>
        <a href="index.php">Home</a>
        <a href="page1.php">Page 1</a>
        <a href="page2.php" class="active">Page 2</a>
        <a href="page3.php">Page 3</a>
    </nav>
</nav>

<main class="main-content">
    <div class="page-header">
        <div class="page-badge">Page 2</div>
        <h1>UDP Group</h1>
        <p>UDP statistics and connection table from MIB-2 (OID base: 1.3.6.1.2.1.7)</p>
    </div>

    <!-- Statistics -->
    <div class="card">
        <div class="card-header">
            <span></span> UDP Statistics
        </div>
        <table>
            <thead>
                <tr>
                    <th class="id-col">#</th>
                    <th>Object Name</th>
                    <th>OID</th>
                    <th>Description</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($statsData as $i => $row): ?>
                <tr>
                    <td class="id-col"><?php echo $i + 1; ?></td>
                    <td class="name-col"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><code style="font-family:var(--font-mono);font-size:.8rem;"><?php echo htmlspecialchars($row['oid']); ?></code></td>
                    <td class="desc-col"><?php echo htmlspecialchars($row['desc']); ?></td>
                    <td class="val-col"><?php echo htmlspecialchars($row['value']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- UDP Table -->
    <div class="card">
        <div class="card-header">
            <span></span> UDP Connection Table
            <span class="badge">udpTable – OID: 1.3.6.1.2.1.7.5</span>
        </div>
        <?php if (empty($tableData)): ?>
            <div class="empty-state">No active UDP connections found on this agent.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th class="id-col">#</th>
                    <th>Local Address</th>
                    <th>Local Port</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tableData as $row): ?>
                <tr>
                    <td class="id-col"><?php echo $row['index']; ?></td>
                    <td class="name-col"><?php echo htmlspecialchars($row['address']); ?></td>
                    <td class="val-col"><?php echo htmlspecialchars($row['port']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="nav-buttons">
        <a href="page1.php" class="btn-nav">← Previous: System Group</a>
        <a href="page3.php" class="btn-nav">Next: SNMP Statistics →</a>
    </div>
</main>
</body>
</html>