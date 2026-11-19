<?php
require __DIR__ . '/lib.php';
$db = db();
$db->exec("CREATE TABLE IF NOT EXISTS settings(k VARCHAR(64) PRIMARY KEY, v MEDIUMTEXT) CHARSET=utf8mb4");
$db->exec("CREATE TABLE IF NOT EXISTS items(id INT AUTO_INCREMENT PRIMARY KEY, section VARCHAR(32), title VARCHAR(255), subtitle VARCHAR(255), body TEXT, tags VARCHAR(500), meta VARCHAR(255), link VARCHAR(500), image VARCHAR(255), sort INT DEFAULT 0, visible TINYINT DEFAULT 1, INDEX(section)) CHARSET=utf8mb4");
$db->exec("CREATE TABLE IF NOT EXISTS messages(id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150), email VARCHAR(150), phone VARCHAR(50), msg TEXT, created TIMESTAMP DEFAULT CURRENT_TIMESTAMP) CHARSET=utf8mb4");
if (!empty(settings()['admin_hash'])) die('Already installed. Delete install.php from the server.');

if (($_POST['pw'] ?? '') !== '' && strlen($_POST['pw']) >= 8) {
    $set = [
        'admin_hash' => password_hash($_POST['pw'], PASSWORD_DEFAULT),
        'name' => 'Ahmed Abdelhamed', 'badge' => 'Business Information Systems',
        'tagline' => 'Data, systems, and design for better decisions.',
        'intro' => 'Information Technology and Accounting professional focused on turning data into clear insights, improving workflows, and delivering practical digital solutions.',
        'about_title' => 'Building clarity from complexity',
        'about_text' => "I am an Information Technology and Accounting professional with a strong foundation in data analysis, systems management, and business operations.\n\nMy experience spans financial data, customer-focused systems, visual communication, and web/UI design.",
        'about_points' => "Business Information Systems student at Future Academy\nHands-on experience in accounting, banking, and data operations\nComfortable translating data into understandable reports and visuals",
        'cv_url' => 'https://drive.google.com/file/d/1MP9KjWSY40EEfzIeCvYP-ioGCmX6hxop/view?usp=drive_link',
        'email' => 'info@ahmedabdelhamed.com', 'phone' => '01116512028', 'whatsapp' => '201116512028',
        'location' => 'Heliopolis, Cairo, Egypt',
        'linkedin' => 'https://www.linkedin.com/in/ahmed3bdelhamed/', 'behance' => 'https://www.behance.net/ahmedabdelhamed14',
    ];
    $q = $db->prepare('INSERT INTO settings(k,v) VALUES(?,?)');
    foreach ($set as $k => $v) $q->execute([$k, $v]);
    $seed = [
        ['skills', 'Data & Business Intelligence', '', '', 'Data Analysis,Data Visualization,Power BI,Tableau,Data Cleaning,Data Validation', ''],
        ['skills', 'Systems & Productivity', '', '', 'SQL,Microsoft Access,Advanced Excel,ERP Systems,SAP,Oracle', ''],
        ['skills', 'Accounting & Operations', '', '', 'Financial Reporting,Audit & Compliance,Process Improvement,Technical Documentation', ''],
        ['skills', 'Collaboration', '', '', 'Problem Solving,Communication,Team Collaboration,Leadership', ''],
        ['courses', 'Excel for Smarter Business Decisions', 'Beginner', 'Formulas, clean spreadsheets, and confident reporting.', '', ''],
        ['courses', 'Power BI Dashboard Essentials', 'Intermediate', 'Build clear dashboards that help decision-makers.', '', ''],
        ['experience', 'Graphic Designer', 'ToBim', 'Building a clinic data-entry system and creating client-focused UI/UX designs.', 'Systems,Data Entry,UI/UX', 'Jul 2025 – Present'],
        ['experience', 'Banking Intern', 'Banque Misr & CIB', 'Banking operations, compliance, and accurate transaction handling.', 'Compliance,Customer Service', 'Jul 2025 – Jan 2026'],
        ['experience', 'Financial Auditor', 'HMT Groups', 'Entered financial data and helped identify contract savings that reduced operating costs by 15%.', 'Auditing,Reporting', 'Dec 2024 – Jan 2025'],
    ];
    $q = $db->prepare('INSERT INTO items(section,title,subtitle,body,tags,meta,sort) VALUES(?,?,?,?,?,?,?)');
    foreach ($seed as $i => $r) $q->execute([$r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $i]);
    die('Done ✅ — delete install.php, then open <a href="admin/">/admin</a>');
}
?><!doctype html><meta charset="utf-8"><title>Install</title>
<form method="post" style="font:16px sans-serif;max-width:360px;margin:80px auto">
<h2>Install</h2><p>Choose an admin password (8+ characters):</p>
<input type="password" name="pw" minlength="8" required style="width:100%;padding:10px"><br><br>
<button style="padding:10px 20px">Install</button></form>
