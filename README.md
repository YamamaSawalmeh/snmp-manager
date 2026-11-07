# 📡 SNMP Manager & Secure File Transfer

> **Computer Networks 2 – course project, An-Najah National University (Spring 2026)**

Two parts:

1. **SNMP Manager (web)** – a PHP dashboard that talks to an SNMP agent (SNMPv2c) to read and change MIB-II values.
2. **File encryption** – scripts that protect a file with AES-256 and check its integrity with SHA-256 (OpenSSL).

---

## Part 1 – SNMP Manager

| Page | MIB group | What it does |
|------|-----------|--------------|
| `Index.php` | – | Home page, shows the agent's name (`sysName`) |
| `page1.php` | **System** (1.3.6.1.2.1.1) | Shows system info and lets you **edit** writable objects (`sysContact`, `sysName`, `sysLocation`) with `snmp2_set` |
| `page2.php` | **UDP** (1.3.6.1.2.1.7) | UDP counters and the UDP listener table using `snmp2_walk` |
| `page3.php` | **SNMP** (1.3.6.1.2.1.11) | SNMP protocol statistics (packets in/out, errors …) |

### Run

1. Enable the SNMP service on Windows (or install `snmpd` on Linux) with a `public` read community and a write community.
2. Install XAMPP and enable the PHP SNMP extension (`extension=snmp` in `php.ini`).
3. Copy the `snmp-manager/` folder into `htdocs/` and open <http://localhost/snmp-manager/Index.php>.

The agent IP and community are set at the top of each page (`$ip = "127.0.0.1"`, `$community = "public"`).

---

## Part 2 – File encryption with OpenSSL

```bat
enc.bat NetPart2.txt <name>   :: generate a random 256-bit key, encrypt with AES-256-CBC (PBKDF2), save SHA-256 hash
dec.bat NetPart2.txt <name>   :: decrypt, compare with the original, and compare the hashes
```

`enc.bat` creates `<name>key.hex`, `NetPart2.txt.enc` and `NetPart2.txt.sha256`; `dec.bat` produces `NetPart2.txt.dec` and checks it matches the original byte for byte. (Generated keys and outputs are not committed.)

## Tech stack

PHP · SNMPv2c · MIB-II · XAMPP · OpenSSL (AES-256-CBC, SHA-256) · Batch scripts
