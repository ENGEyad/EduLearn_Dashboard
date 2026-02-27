<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>{{ $title ?? 'EduLearn – School Admin Panel' }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>

  <style>
    /* ============================
       CSS DESIGN TOKENS
    ============================ */
    :root {
      --sidebar-width: 252px;

      /* Palette */
      --primary:       #4f46e5;
      --primary-dark:  #3730a3;
      --primary-light: #818cf8;
      --accent:        #06b6d4;
      --success:       #10b981;
      --warning:       #f59e0b;
      --danger:        #ef4444;

      /* Sidebar gradient */
      --sidebar-from: #1e1b4b;
      --sidebar-to:   #2e1065;

      /* Surfaces */
      --bg:       #f1f5f9;
      --card:     #ffffff;
      --border:   #e2e8f0;

      /* Text */
      --title:    #0f172a;
      --text:     #1e293b;
      --muted:    #64748b;
      --muted-lt: #94a3b8;

      /* Shadows */
      --shadow-xs: 0 1px 2px rgba(15,23,42,.06);
      --shadow-sm: 0 4px 16px rgba(15,23,42,.06);
      --shadow-md: 0 8px 28px rgba(15,23,42,.10);
      --shadow-lg: 0 16px 48px rgba(15,23,42,.14);
      --glow-primary: 0 4px 20px rgba(79,70,229,.28);

      /* Radii */
      --radius-sm: 10px;
      --radius:    14px;
      --radius-lg: 18px;
      --radius-xl: 24px;
    }

    /* ============================
       BASE
    ============================ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: var(--bg);
      font-family: "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
      color: var(--text);
      min-height: 100vh;
    }

    h1,h2,h3,h4,h5,h6 { font-family: "Outfit", "Inter", sans-serif; }

    /* ============================
       SIDEBAR
    ============================ */
    .sidebar {
      position: fixed; top: 0; left: 0;
      width: var(--sidebar-width); height: 100vh;
      background: linear-gradient(160deg, var(--sidebar-from) 0%, var(--sidebar-to) 100%);
      padding: 1.4rem 1rem 1.5rem;
      display: flex; flex-direction: column; gap: .5rem;
      z-index: 200;
      box-shadow: 4px 0 24px rgba(30,27,75,.25);
    }

    /* Brand */
    .brand-box {
      display: flex; align-items: center; gap: .8rem;
      padding: .1rem .5rem .9rem;
      border-bottom: 1px solid rgba(255,255,255,.1);
      margin-bottom: .4rem;
    }
    .brand-avatar {
      width: 40px; height: 40px; border-radius: 12px;
      background: linear-gradient(135deg,#818cf8,#4f46e5);
      display: grid; place-items: center;
      color: #fff; font-weight: 800; font-size: .78rem;
      font-family: "Outfit", sans-serif;
      letter-spacing: .5px;
      box-shadow: 0 4px 10px rgba(79,70,229,.45);
    }
    .brand-name {
      color: #fff; font-weight: 700; font-size: 1rem;
      font-family: "Outfit", sans-serif; letter-spacing: .3px;
    }
    .brand-sub { color: rgba(255,255,255,.45); font-size: .72rem; }

    /* Nav links */
    .sidebar .nav-section-label {
      font-size: .62rem; font-weight: 600; letter-spacing: .08em;
      text-transform: uppercase; color: rgba(255,255,255,.3);
      padding: .6rem .75rem .2rem;
    }
    .sidebar .nav a {
      display: flex; gap: .7rem; align-items: center;
      padding: .6rem .85rem; border-radius: var(--radius);
      color: rgba(255,255,255,.65); font-weight: 500; font-size: .885rem;
      text-decoration: none;
      transition: background .17s, color .17s, box-shadow .17s;
      position: relative; overflow: hidden;
    }
    .sidebar .nav a::before {
      content: ''; position: absolute; inset: 0;
      background: rgba(255,255,255,0); border-radius: var(--radius);
      transition: background .17s;
    }
    .sidebar .nav a:hover {
      color: #fff;
      background: rgba(255,255,255,.08);
    }
    .sidebar .nav a.active {
      background: linear-gradient(90deg, rgba(255,255,255,.15), rgba(255,255,255,.07));
      color: #fff;
      box-shadow: 0 2px 12px rgba(0,0,0,.15);
    }
    .sidebar .nav a.active::after {
      content: ''; position: absolute; right: 0; top: 20%; bottom: 20%;
      width: 3px; border-radius: 4px 0 0 4px;
      background: var(--primary-light);
    }
    .nav-icon {
      width: 32px; height: 32px; border-radius: 9px;
      display: grid; place-items: center; font-size: .9rem;
      background: rgba(255,255,255,.1);
      transition: background .17s, box-shadow .17s;
      flex-shrink: 0;
    }
    .sidebar .nav a.active .nav-icon,
    .sidebar .nav a:hover .nav-icon {
      background: rgba(255,255,255,.18);
      box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }

    /* Bottom links */
    .sidebar .bottom-links {
      margin-top: auto;
      border-top: 1px solid rgba(255,255,255,.1);
      padding-top: 1rem;
    }
    .sidebar .bottom-links button {
      display: flex; gap: .6rem; align-items: center;
      width: 100%; background: transparent; border: 0;
      color: rgba(255,255,255,.45); font-size: .85rem;
      padding: .45rem .75rem; border-radius: var(--radius);
      cursor: pointer; transition: color .15s, background .15s;
    }
    .sidebar .bottom-links button:hover {
      color: rgba(255,255,255,.85);
      background: rgba(255,255,255,.07);
    }

    /* Badge count on nav items */
    .nav-badge {
      margin-left: auto; font-size: .62rem; font-weight: 700;
      background: var(--primary-light); color: #fff;
      padding: .1rem .45rem; border-radius: 99px;
    }

    /* ============================
       MAIN WRAPPER
    ============================ */
    .main-wrapper {
      margin-left: var(--sidebar-width);
      min-height: 100vh;
      display: flex; flex-direction: column;
    }

    /* TOPBAR */
    .topbar {
      height: 68px;
      background: rgba(255,255,255,.82);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center;
      justify-content: space-between; padding: 0 1.75rem;
      position: sticky; top: 0; z-index: 100;
      box-shadow: var(--shadow-xs);
    }
    .topbar-left .page-title {
      font-family: "Outfit", sans-serif;
      font-weight: 700; font-size: 1.1rem; color: var(--title);
      line-height: 1.2;
    }
    .topbar-left small { color: var(--muted); font-size: .78rem; }

    .topbar-search {
      display: flex; align-items: center; gap: .5rem;
      background: var(--bg); border: 1px solid var(--border);
      border-radius: 10px; padding: .42rem .8rem; width: 210px;
      transition: border-color .15s, box-shadow .15s;
    }
    .topbar-search:focus-within {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(79,70,229,.08);
    }
    .topbar-search input {
      border: 0; background: transparent; outline: none;
      font-size: .84rem; color: var(--title); width: 100%;
    }
    .topbar-search i { color: var(--muted-lt); font-size: .9rem; }

    .topbar-right { display: flex; align-items: center; gap: .75rem; }

    .icon-btn {
      width: 38px; height: 38px; border-radius: 10px;
      background: var(--bg); border: 1px solid var(--border);
      display: grid; place-items: center; cursor: pointer;
      color: var(--muted); font-size: 1rem;
      transition: background .15s, box-shadow .15s, color .15s;
      position: relative;
    }
    .icon-btn:hover { background: var(--card); box-shadow: var(--shadow-sm); color: var(--title); }

    .notif-dot {
      position: absolute; top: 6px; right: 6px;
      width: 8px; height: 8px; border-radius: 50%;
      background: var(--danger); border: 2px solid #fff;
    }

    .admin-pill {
      display: flex; align-items: center; gap: .55rem;
      background: var(--bg); border: 1px solid var(--border);
      padding: .32rem .75rem .32rem .4rem; border-radius: 99px;
      cursor: pointer; transition: box-shadow .15s;
    }
    .admin-pill:hover { box-shadow: var(--shadow-sm); }
    .admin-avatar {
      width: 28px; height: 28px; border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      display: grid; place-items: center;
      color: #fff; font-size: .68rem; font-weight: 700;
    }
    .admin-pill span { font-size: .82rem; font-weight: 600; color: var(--title); }

    /* Content area */
    .content-area { padding: 1.75rem 1.75rem 3rem; flex: 1; }

    /* ============================
       SHARED CARD COMPONENTS
    ============================ */

    /* Stat card */
    .stat-card {
      background: var(--card);
      border-radius: var(--radius-lg);
      padding: 1.25rem 1.25rem 1.1rem;
      border: 1px solid var(--border);
      box-shadow: var(--shadow-sm);
      position: relative; overflow: hidden;
      transition: box-shadow .2s, transform .2s;
    }
    .stat-card:hover {
      box-shadow: var(--shadow-md);
      transform: translateY(-2px);
    }
    .stat-card::before {
      content: ''; position: absolute; top: 0; left: 0;
      width: 3px; height: 100%;
      background: var(--card-accent, var(--primary));
      border-radius: 4px 0 0 4px;
    }
    .stat-card .stat-icon {
      width: 42px; height: 42px; border-radius: 12px;
      display: grid; place-items: center; font-size: 1.1rem;
      margin-bottom: .7rem;
    }
    .stat-card .stat-label { font-size: .73rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
    .stat-card .stat-value {
      font-family: "Outfit", sans-serif;
      font-size: 1.85rem; font-weight: 800; color: var(--title);
      line-height: 1.1; margin: .25rem 0 .3rem;
    }
    .stat-card .stat-delta { font-size: .75rem; font-weight: 600; display: flex; align-items: center; gap: .25rem; }
    .stat-card .delta-up   { color: var(--success); }
    .stat-card .delta-down { color: var(--danger); }
    .stat-card .delta-neu  { color: var(--muted); }

    /* Generic card panel */
    .card-panel {
      background: var(--card);
      border-radius: var(--radius-lg);
      border: 1px solid var(--border);
      padding: 1.25rem;
      box-shadow: var(--shadow-sm);
    }
    .card-panel .panel-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: .9rem;
    }
    .panel-title {
      font-family: "Outfit", sans-serif;
      font-weight: 700; font-size: .97rem; color: var(--title);
    }

    /* Table shell */
    .table-shell {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-sm);
      padding: 1rem 1rem .5rem;
      overflow: hidden;
    }
    .table-shell thead th {
      font-size: .68rem; text-transform: uppercase;
      letter-spacing: .06em; color: var(--muted-lt);
      border-bottom: 1px solid var(--border) !important;
      background: transparent; padding-bottom: .65rem;
      font-weight: 600;
    }
    .table-shell tbody tr {
      border-bottom: 1px solid #f1f5f9;
      transition: background .12s;
    }
    .table-shell tbody tr:last-child { border-bottom: 0; }
    .table-shell tbody tr:hover { background: #f8faff; }
    .table-shell td { vertical-align: middle; padding: .7rem .5rem; font-size: .875rem; }

    /* Status pills */
    .status-pill {
      border-radius: 99px; padding: .28rem .72rem;
      font-size: .67rem; font-weight: 600; display: inline-flex;
      align-items: center; gap: .3rem; border: 0;
    }
    .status-pill::before {
      content: ''; width: 6px; height: 6px; border-radius: 50%;
    }
    .status-active    { background: #d1fae5; color: #065f46; }
    .status-active::before { background: #10b981; }
    .status-suspended { background: #fee2e2; color: #b91c1c; }
    .status-suspended::before { background: #ef4444; }
    .status-graduated { background: #dbeafe; color: #1e40af; }
    .status-graduated::before { background: #3b82f6; }
    .status-inactive  { background: #f1f5f9; color: #475569; }
    .status-inactive::before  { background: #94a3b8; }

    /* Profile shell */
    .profile-shell {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 1.25rem;
      box-shadow: var(--shadow-sm);
      min-height: 400px;
    }
    .profile-header { display: flex; align-items: center; gap: .9rem; margin-bottom: 1rem; }
    .avatar-circle {
      width: 56px; height: 56px; border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-light), var(--primary));
      display: grid; place-items: center;
      font-weight: 800; font-size: 1.1rem; color: #fff;
      font-family: "Outfit", sans-serif;
      box-shadow: 0 4px 12px rgba(79,70,229,.3);
      flex-shrink: 0;
    }
    .profile-name { font-family: "Outfit",sans-serif; font-weight: 700; font-size: .97rem; color: var(--title); }
    .profile-shell hr { border-color: var(--border); margin: .8rem 0; }

    /* kv rows inside profile */
    .kv-row { display: flex; justify-content: space-between; font-size: .82rem; margin-bottom: .4rem; }
    .kv-label { color: var(--muted); }
    .kv-value { font-weight: 500; color: var(--title); }

    /* Section divider label */
    .section-divider {
      font-size: .67rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .07em; color: var(--primary);
      border-bottom: 1px solid var(--border); padding-bottom: .4rem;
      margin: .9rem 0 .65rem;
    }

    /* Profile tabs */
    .profile-tabs.nav-tabs { border-bottom: 1px solid var(--border); gap: .4rem; }
    .profile-tabs .nav-link {
      border: 0 !important; background: transparent !important;
      color: var(--muted); padding: .4rem .2rem; margin-right: 1rem;
      border-bottom: 2px solid transparent !important; font-weight: 500;
    }
    .profile-tabs .nav-link.active {
      color: var(--primary) !important;
      border-bottom-color: var(--primary) !important;
    }

    /* ============================
       BUTTONS
    ============================ */
    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark)) !important;
      border: 0 !important; color: #fff !important;
      font-weight: 600; border-radius: var(--radius-sm) !important;
      box-shadow: var(--glow-primary) !important;
      transition: opacity .15s, transform .15s, box-shadow .15s !important;
    }
    .btn-primary:hover { opacity: .88; transform: translateY(-1px); }

    .btn-outline-secondary {
      border-color: var(--border) !important; color: var(--text) !important;
      font-weight: 500; border-radius: var(--radius-sm) !important;
      background: var(--card) !important;
      transition: box-shadow .15s, border-color .15s !important;
    }
    .btn-outline-secondary:hover {
      border-color: var(--primary-light) !important;
      box-shadow: 0 0 0 3px rgba(79,70,229,.08) !important;
    }

    .btn-light {
      background: var(--bg) !important;
      border-color: var(--border) !important;
      color: var(--text) !important;
      font-weight: 500;
      border-radius: var(--radius-sm) !important;
    }

    /* ============================
       FORM CONTROLS
    ============================ */
    .form-control, .form-select {
      border-color: var(--border) !important;
      border-radius: var(--radius-sm) !important;
      font-size: .875rem !important;
      color: var(--title) !important;
      background-color: var(--card) !important;
      transition: border-color .15s, box-shadow .15s !important;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-light) !important;
      box-shadow: 0 0 0 3px rgba(79,70,229,.1) !important;
      outline: none !important;
    }
    .form-label { font-size: .8rem; font-weight: 600; color: var(--muted); margin-bottom: .35rem; }

    .input-group-text {
      background: var(--bg) !important;
      border-color: var(--border) !important;
      color: var(--muted) !important;
      border-radius: var(--radius-sm) 0 0 var(--radius-sm) !important;
    }

    /* ============================
       MODAL IMPROVEMENTS
    ============================ */
    .modal-content {
      border-radius: var(--radius-lg) !important;
      border: 0 !important;
      box-shadow: var(--shadow-lg) !important;
    }
    .modal-header {
      border-bottom: 1px solid var(--border) !important;
      padding: 1.1rem 1.25rem !important;
    }
    .modal-footer {
      border-top: 1px solid var(--border) !important;
      padding: .85rem 1.25rem !important;
    }
    .modal-title { font-family: "Outfit",sans-serif; font-weight: 700; }

    /* ============================
       ACTIVITY TIMELINE
    ============================ */
    .timeline { list-style: none; padding: 0; margin: 0; }
    .timeline li {
      display: flex; gap: .85rem; align-items: flex-start;
      padding: .6rem 0; border-bottom: 1px solid #f1f5f9;
      font-size: .84rem;
    }
    .timeline li:last-child { border-bottom: 0; }
    .timeline-icon {
      width: 32px; height: 32px; border-radius: 10px;
      display: grid; place-items: center; flex-shrink: 0;
      font-size: .85rem;
    }
    .timeline-text { color: var(--text); }
    .timeline-time { font-size: .7rem; color: var(--muted-lt); margin-top: .1rem; }

    /* ============================
       ALERT ROWS
    ============================ */
    .alert-row {
      display: flex; align-items: center; gap: .8rem;
      padding: .7rem .9rem; border-radius: var(--radius-sm);
      background: var(--bg); font-size: .845rem;
      border-left: 3px solid transparent;
      margin-bottom: .5rem;
    }
    .alert-row:last-child { margin-bottom: 0; }
    .alert-row.warn  { border-color: var(--warning); background: #fffbeb; }
    .alert-row.info  { border-color: var(--primary);  background: #eef2ff; }
    .alert-row.danger{ border-color: var(--danger);   background: #fef2f2; }
    .alert-row i { font-size: 1rem; }
    .alert-row.warn  i { color: var(--warning); }
    .alert-row.info  i { color: var(--primary);  }
    .alert-row.danger i { color: var(--danger);  }

    /* ============================
       SECTION TITLE (global)
    ============================ */
    .section-title {
      font-family: "Outfit", sans-serif;
      font-weight: 700; font-size: .97rem; color: var(--title);
      margin-bottom: .5rem;
    }

    /* ============================
       REPORTS SKIN (untouched logic, visual refresh)
    ============================ */
    .reports-skin { --bg:#f1f5f9; --card:#fff; --muted:#64748b; --border:#e2e8f0; --title:#0f172a; --link:#4f46e5; --shadow: var(--shadow-sm); --radius:14px; }
    .reports-skin .content-wrap,.reports-skin .page,.reports-skin .page-wrap { max-width:1160px; margin-inline:auto; padding:20px 20px 56px }
    .reports-skin .page-header { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px }
    .reports-skin .page-title  { font-weight:800; font-size:28px; margin:0 }
    .reports-skin .subtitle    { color:var(--muted); font-size:14px }
    .reports-skin .crumbs      { font-size:14px; color:var(--muted) }
    .reports-skin .crumbs .sep { margin:0 .35rem; color:#cbd5e1 }
    .reports-skin .btn         { border-radius:12px; box-shadow:var(--shadow-xs) }
    .reports-skin .btn-primary { background:var(--link)!important; border-color:var(--link)!important; font-weight:700 }
    .reports-skin .btn-soft    { background:#eef2ff; color:#3949ab; border:1px solid #e0e7ff; font-weight:600; border-radius:12px; padding:.6rem .9rem }
    .reports-skin .btn-ghost   { background:#fff; border:1px solid var(--border); border-radius:12px; padding:.6rem .9rem }
    .reports-skin .btn-cta     { background:#e9f0ff; border:1px solid #dbe6ff; color:#0b4de0; border-radius:12px; padding:.6rem .9rem; font-weight:700 }
    .reports-skin .filters     { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:12px; display:flex; gap:10px; flex-wrap:wrap; box-shadow:var(--shadow-xs) }
    .reports-skin .input-group>.input-group-text { background:#fff; border-right:0; border-color:var(--border); border-top-left-radius:10px; border-bottom-left-radius:10px }
    .reports-skin .input-group>.form-control     { border-left:0; border-color:var(--border); border-top-right-radius:10px; border-bottom-right-radius:10px; box-shadow:none }
    .reports-skin .form-select  { border-radius:10px; border:1px solid var(--border); box-shadow:none; background-color:#fff }
    .reports-skin .form-select:focus,.reports-skin .form-control:focus { border-color:#c7d2fe; box-shadow:0 0 0 .2rem rgba(79,70,229,.08) }
    .reports-skin .table-shell  { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:8px; box-shadow:var(--shadow-xs) }
    .reports-skin .table        { margin:0 }
    .reports-skin .table>thead th { color:var(--muted); font-weight:600; font-size:12.5px; border-bottom:1px solid var(--border)!important; background:transparent }
    .reports-skin .table>tbody td { vertical-align:middle }
    .reports-skin .table>tbody tr:hover { background:#f7faff }
    .reports-skin .pagination-wrap { display:flex; justify-content:flex-end }
    .reports-skin .pagination .page-link { color:var(--title); border:1px solid var(--border); background:#fff; border-radius:10px; margin:0 3px; box-shadow:var(--shadow-xs) }
    .reports-skin .pagination .page-item.active .page-link { background:#eef2ff; border-color:#dbe6ff; color:#0b4de0 }
    .reports-skin .cardy { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-xs) }
    .reports-skin .panel { padding:16px }
    .reports-skin .panel h6 { font-size:15px; font-weight:800; margin-bottom:10px }
    .reports-skin .stats { display:grid; grid-template-columns:repeat(5,1fr); gap:16px; margin-top:14px }
    .reports-skin .stat  { padding:16px }
    .reports-skin .stat .k  { font-size:13px; color:var(--muted); margin-bottom:6px }
    .reports-skin .stat .v  { font-size:26px; font-weight:800 }
    .reports-skin .delta    { font-size:12px; font-weight:700; margin-top:4px }
    .reports-skin .delta.g  { color:#16a34a } .reports-skin .delta.r{ color:#dc2626 }
    .reports-skin .page-head    { display:flex; align-items:center; justify-content:space-between; margin:10px 0 14px }
    .reports-skin .student-head { padding:16px 18px }
    .reports-skin .avatar { width:56px; height:56px; border-radius:50%; background:#fde68a; display:inline-grid; place-items:center; font-weight:700; color:#78350f }
    .reports-skin .kvs    { display:flex; align-items:center; gap:14px }
    .reports-skin .meta .name { font-weight:700 }
    .reports-skin .meta .sub  { font-size:13px; color:var(--muted) }
    .reports-skin .status-pill { margin-left:auto; padding:6px 10px; background:#eaf7ef; color:#15803d; border-radius:999px; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:6px }
    .reports-skin .status-dot  { width:8px; height:8px; border-radius:50%; background:#22c55e }
    .reports-skin .stats--student { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-top:10px }
    .reports-skin .stat h6  { font-size:13px; color:var(--muted); margin:0 0 8px }
    .reports-skin .stat .val{ font-size:28px; font-weight:800 }
    .reports-skin .row-charts     { display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-top:12px }
    .reports-skin .chart-wrap     { position:relative; height:220px }
    .reports-skin .mini-two       { display:grid; grid-template-columns:1fr 1fr; gap:16px }
    .reports-skin .stat-mini      { display:flex; align-items:center; gap:12px; padding:16px }
    .reports-skin .icn-badge      { width:42px; height:42px; border-radius:12px; background:#eef2ff; display:grid; place-items:center; color:#3856e8; font-size:20px }
    .reports-skin .stat-mini .label { font-size:13px; color:var(--muted); margin-bottom:2px }
    .reports-skin .stat-mini .val   { font-size:26px; font-weight:800 }
    .reports-skin .trend-note { font-size:13px; color:#16a34a; font-weight:700; display:flex; align-items:center; gap:6px }
    .reports-skin .muted        { color:var(--muted) }
    .reports-skin .section-title{ font-weight:800; font-size:16px; margin:2px 0 8px }
    .reports-skin .ach-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px }
    .reports-skin .ach-card { padding:18px; display:flex; align-items:center; gap:14px; border:1px dashed #e2e8f0; border-radius:12px }
    .reports-skin .ach-icn  { width:46px; height:46px; border-radius:50%; display:grid; place-items:center }
    .reports-skin .ach-icn.star { background:#fff7e6; color:#d97706 }
    .reports-skin .ach-icn.book { background:#e6fffa; color:#059669 }
    .reports-skin .badge-pass   { background:#e8f7ee; color:#118d57; font-weight:700 }
    .reports-skin .badge-fail   { background:#fee2e2; color:#be123c; font-weight:700 }
    .reports-skin .action-link  { color:var(--link); font-weight:600; text-decoration:none }
    @media (max-width:1100px){ .reports-skin .stats{grid-template-columns:repeat(3,1fr)} }
    @media (max-width:992px){ .reports-skin .row-charts{grid-template-columns:1fr} .reports-skin .mini-two{grid-template-columns:1fr} }
    @media (max-width:720px){ .reports-skin .stats{grid-template-columns:repeat(2,1fr)} }
    @media print {
      .reports-skin .no-print,.reports-skin .filters{display:none!important}
      body{background:#fff}
      .reports-skin .content-wrap,.reports-skin .page,.reports-skin .page-wrap{max-width:100%;padding:0}
      .reports-skin .cardy,.reports-skin .table-shell{box-shadow:none}
    }

    /* ============================
       RESPONSIVE
    ============================ */
    @media (max-width: 1024px) {
      :root { --sidebar-width: 220px; }
    }
    @media (max-width: 768px) {
      .sidebar { position: static; width: 100%; height: auto; flex-direction: row; overflow-x: auto; padding: .75rem; }
      .main-wrapper { margin-left: 0; }
      .topbar { flex-wrap: wrap; gap: .75rem; height: auto; padding: .75rem 1rem; }
      .topbar-search { width: 100%; }
    }

    /* ============================
       SCROLLBAR (Webkit)
    ============================ */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

    /* ============================
       ANIMATIONS
    ============================ */
    @keyframes fadeInUp {
      from { opacity:0; transform:translateY(12px); }
      to   { opacity:1; transform:translateY(0); }
    }
    .content-area > * { animation: fadeInUp .28s ease both; }
  </style>
</head>
<body>

  <!-- ========== SIDEBAR ========== -->
  <aside class="sidebar">
    <div class="brand-box">
      <div class="brand-avatar">EL</div>
      <div>
        <div class="brand-name">EduLearn</div>
        <div class="brand-sub">School Admin Panel</div>
      </div>
    </div>

    <div class="nav-section-label">Main Menu</div>

    <nav class="nav flex-column gap-1" id="sidebarNav">
      <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-grid-1x2-fill"></i></span>
        <span>Dashboard</span>
      </a>
      <a href="{{ url('/students') }}" class="{{ request()->is('students*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-person-lines-fill"></i></span>
        <span>Students</span>
      </a>
      <a href="{{ url('/teachers') }}" class="{{ request()->is('teachers*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-person-badge-fill"></i></span>
        <span>Teachers</span>
      </a>
      <a href="{{ url('/classes') }}" class="{{ request()->is('classes*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-journal-text"></i></span>
        <span>Classes</span>
      </a>
      <a href="{{ url('/subjects') }}" class="{{ request()->is('subjects*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-journal-bookmark-fill"></i></span>
        <span>Subjects</span>
      </a>
      <a href="{{ url('/class-subjects') }}" class="{{ request()->is('class-subjects*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-journal-check"></i></span>
        <span>Class Subjects</span>
      </a>
      <a href="{{ url('/assignments') }}" class="{{ request()->is('assignments*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-diagram-3-fill"></i></span>
        <span>Assignments</span>
      </a>

      <div class="nav-section-label mt-2">Analytics</div>

      <a href="{{ url('/reports') }}" class="{{ request()->is('reports*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-bar-chart-fill"></i></span>
        <span>Reports</span>
      </a>
    </nav>

    <div class="bottom-links">
      <button>
        <i class="bi bi-question-circle"></i> Help & Support
      </button>
      <button class="mt-1">
        <i class="bi bi-box-arrow-left"></i> Logout
      </button>
    </div>
  </aside>

  <!-- ========== MAIN WRAPPER ========== -->
  <div class="main-wrapper">

    <!-- TOPBAR -->
    <header class="topbar">
      <div class="topbar-left">
        <div class="page-title" id="pageTitle">{{ $pageTitle ?? 'Dashboard' }}</div>
        <small id="pageSubtitle">{{ $pageSubtitle ?? 'Welcome back, Admin!' }}</small>
      </div>

      <div class="topbar-right">
        <div class="topbar-search">
          <i class="bi bi-search"></i>
          <input type="text" placeholder="Search anything…" />
        </div>

        <div class="icon-btn" title="Notifications">
          <i class="bi bi-bell"></i>
          <span class="notif-dot"></span>
        </div>

        <div class="icon-btn" title="Settings">
          <i class="bi bi-gear"></i>
        </div>

        <div class="admin-pill">
          <div class="admin-avatar">AD</div>
          <span>Admin</span>
          <i class="bi bi-chevron-down" style="font-size:.65rem; color:var(--muted);"></i>
        </div>
      </div>
    </header>

    <main class="content-area">
      @yield('content')
    </main>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  @stack('scripts')
</body>
</html>
