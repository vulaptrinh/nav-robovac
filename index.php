<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAV • Hệ Thống Điều Khiển Robot Hút Bụi</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Cdefs%3E%3ClinearGradient id='bg' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%230f172a'/%3E%3Cstop offset='100%25' stop-color='%230284c7'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='64' height='64' rx='16' fill='url(%23bg)'/%3E%3Cpath d='M18 46V18h6l16 26V18h6v28h-6L24 20v26z' fill='%2338bdf8'/%3E%3Ccircle cx='43' cy='17' r='3' fill='%2310b981'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --purple: #8b5cf6;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --radius: 14px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            padding: 24px 16px;
            min-height: 100vh;
        }

        .container {
            max-width: 1080px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 20px 24px;
            box-shadow: var(--shadow);
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .header-title-box {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .nav-logo-box {
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.25);
            transition: transform 0.3s ease;
            overflow: hidden;
            flex-shrink: 0;
            background: #0f172a;
        }

        .nav-logo-box:hover {
            transform: scale(1.05) rotate(2deg);
        }

        .nav-logo-box svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .nav-brand-title {
            display: flex;
            align-items: baseline;
            gap: 10px;
        }

        .nav-brand-name {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 1px;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 50%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
        }

        .nav-brand-model {
            font-size: 15px;
            font-weight: 700;
            color: #475569;
            background: #f1f5f9;
            padding: 3px 10px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .header p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .online-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }

        .online-dot {
            width: 8px;
            height: 8px;
            background-color: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .btn-refresh {
            background: #f8fafc;
            border: 1px solid var(--border);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-refresh:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        /* Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 22px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Battery Card */
        .battery-display {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 12px 0 20px;
        }

        .battery-icon-container {
            width: 80px;
            height: 42px;
            border: 3px solid #334155;
            border-radius: 8px;
            padding: 3px;
            position: relative;
            background: #f8fafc;
        }

        .battery-icon-container::after {
            content: '';
            position: absolute;
            right: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 16px;
            background: #334155;
            border-radius: 0 3px 3px 0;
        }

        .battery-bar {
            height: 100%;
            width: 0%;
            background: var(--success);
            border-radius: 4px;
            transition: width 0.5s ease-in-out, background 0.3s;
        }

        .battery-value {
            font-size: 40px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -1px;
            line-height: 1;
        }

        .battery-charging-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            font-weight: 600;
            color: #d97706;
            background: #fef3c7;
            padding: 3px 8px;
            border-radius: 6px;
            margin-top: 6px;
        }

        .device-info-list {
            margin-top: auto;
            border-top: 1px dashed var(--border);
            padding-top: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 13px;
        }

        .device-info-item {
            display: flex;
            justify-content: space-between;
            color: var(--text-muted);
        }

        .device-info-item span:last-child {
            font-weight: 600;
            color: var(--text-main);
            font-family: monospace;
        }

        /* Activity Card */
        .status-badge-lg {
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .status-cleaning { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .status-charging { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-full     { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .status-ready    { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
        .status-standby  { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
        .status-sleeping { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
        .status-error    { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .control-btns {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 12px;
        }

        .btn-cmd {
            padding: 12px 6px;
            border-radius: 10px;
            border: none;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .btn-cmd:active { transform: scale(0.97); }

        .btn-start {
            background: #10b981;
            color: white;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
        }
        .btn-start:hover { background: #059669; }

        .btn-pause {
            background: #f59e0b;
            color: white;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.25);
        }
        .btn-pause:hover { background: #d97706; }

        .btn-dock {
            background: #8b5cf6;
            color: white;
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.25);
        }
        .btn-dock:hover { background: #7c3aed; }

        .btn-find {
            background: #0284c7;
            color: white;
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);
        }
        .btn-find:hover { background: #0369a1; }

        .suction-selector {
            margin-top: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .suction-selector label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .suction-selector select {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-size: 13px;
            font-weight: 600;
            background: white;
            cursor: pointer;
        }

        /* Auto Clean Card */
        .switch-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            padding: 14px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            margin-bottom: 14px;
        }

        .switch-info h4 {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
        }

        .switch-info p {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 26px;
            flex-shrink: 0;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .3s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary);
        }

        input:checked + .slider:before {
            transform: translateX(22px);
        }

        .autoclean-settings {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 14px;
            padding: 0 4px;
        }

        .autoclean-settings select {
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-weight: 600;
        }

        .autoclean-status-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #1e40af;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .log-list {
            margin-top: 10px;
            max-height: 120px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 8px;
            font-size: 12px;
            background: #fafafa;
        }

        .log-item {
            padding: 4px 0;
            border-bottom: 1px dashed #e2e8f0;
            color: var(--text-muted);
        }

        .log-item:last-child { border-bottom: none; }
        .log-item b { color: var(--text-main); }

        /* Schedule Card */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .schedule-table th {
            text-align: left;
            color: var(--text-muted);
            font-weight: 600;
            padding: 8px 10px;
            border-bottom: 2px solid var(--border);
        }

        .schedule-table td {
            padding: 10px;
            border-bottom: 1px solid var(--border);
        }

        .schedule-table tr:last-child td { border-bottom: none; }

        .time-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-family: monospace;
        }

        .badge-enabled {
            background: #dcfce7;
            color: #166534;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-disabled {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0f172a;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 9999;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="header-title-box">
            <div class="nav-logo-box">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
                  <defs>
                    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#0f172a"/>
                      <stop offset="50%" stop-color="#1e1b4b"/>
                      <stop offset="100%" stop-color="#0284c7"/>
                    </linearGradient>
                    <linearGradient id="primaryGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#38bdf8"/>
                      <stop offset="50%" stop-color="#6366f1"/>
                      <stop offset="100%" stop-color="#a855f7"/>
                    </linearGradient>
                    <linearGradient id="accentGrad" x1="0%" y1="100%" x2="100%" y2="0%">
                      <stop offset="0%" stop-color="#06b6d4"/>
                      <stop offset="100%" stop-color="#10b981"/>
                    </linearGradient>
                    <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.8"/>
                      <stop offset="50%" stop-color="#818cf8" stop-opacity="0.2"/>
                      <stop offset="100%" stop-color="#c084fc" stop-opacity="0.8"/>
                    </linearGradient>
                    <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                      <feGaussianBlur stdDeviation="8" result="blur"/>
                      <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                    </filter>
                    <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                      <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#0284c7" flood-opacity="0.35"/>
                    </filter>
                  </defs>
                  <rect x="25" y="25" width="450" height="450" rx="110" fill="url(#bgGrad)" stroke="url(#ringGrad)" stroke-width="4" filter="url(#shadow)"/>
                  <circle cx="250" cy="250" r="180" fill="none" stroke="#38bdf8" stroke-width="1.5" stroke-opacity="0.15" stroke-dasharray="6 8"/>
                  <circle cx="250" cy="250" r="130" fill="none" stroke="#818cf8" stroke-width="1.5" stroke-opacity="0.2" stroke-dasharray="4 6"/>
                  <line x1="250" y1="45" x2="250" y2="60" stroke="#38bdf8" stroke-width="3" stroke-linecap="round"/>
                  <line x1="250" y1="440" x2="250" y2="455" stroke="#38bdf8" stroke-width="3" stroke-linecap="round"/>
                  <line x1="45" y1="250" x2="60" y2="250" stroke="#38bdf8" stroke-width="3" stroke-linecap="round"/>
                  <line x1="440" y1="250" x2="455" y2="250" stroke="#38bdf8" stroke-width="3" stroke-linecap="round"/>
                  <g filter="url(#glow)">
                    <path d="M 140 340 L 140 160 Q 140 145 155 145 L 180 145 Q 195 145 195 160 L 195 340 Q 195 355 180 355 L 155 355 Q 140 355 140 340 Z" fill="url(#primaryGrad)"/>
                    <path d="M 185 155 L 305 325 Q 315 340 330 335 L 340 330 Q 355 320 345 305 L 225 135 Q 215 120 200 125 L 190 130 Q 175 140 185 155 Z" fill="url(#accentGrad)"/>
                    <path d="M 305 340 L 305 160 Q 305 145 320 145 L 345 145 Q 360 145 360 160 L 360 340 Q 360 355 345 355 L 320 355 Q 305 355 305 340 Z" fill="url(#primaryGrad)"/>
                    <circle cx="332" cy="148" r="14" fill="#38bdf8" filter="url(#glow)"/>
                    <circle cx="332" cy="148" r="6" fill="#ffffff"/>
                    <path d="M 125 380 Q 250 425 375 380" fill="none" stroke="url(#accentGrad)" stroke-width="5" stroke-linecap="round"/>
                    <circle cx="250" cy="402" r="4" fill="#10b981" filter="url(#glow)"/>
                  </g>
                </svg>
            </div>
            <div>
                <div class="nav-brand-title">
                    <span class="nav-brand-name">NAV</span>
                    <span class="nav-brand-model">RoboVac AV</span>
                </div>
                <p>Hệ Thống Điều Khiển & Tự Động Hóa Thông Minh • Model: T2118</p>
            </div>
        </div>
        <div class="header-actions">
            <div class="online-badge">
                <div class="online-dot"></div>
                <span id="txt-online">Cloud Online</span>
            </div>
            <button class="btn-refresh" onclick="fetchStatus(true)">
                <span>🔄</span> Cập nhật
            </button>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid">
        <!-- Card 1: Dung lượng Pin -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">🔋 Dung Lượng Pin</div>
                <small id="txt-updated" style="color:var(--text-muted); font-size:12px;">Đang tải...</small>
            </div>
            <div class="battery-display">
                <div class="battery-icon-container">
                    <div class="battery-bar" id="battery-bar"></div>
                </div>
                <div>
                    <div class="battery-value" id="battery-percent">--%</div>
                    <div id="battery-charging-box" style="display:none;">
                        <span class="battery-charging-indicator">⚡ Đang sạc điện</span>
                    </div>
                </div>
            </div>

            <div class="device-info-list">
                <div class="device-info-item">
                    <span>Trạng thái nguồn:</span>
                    <span id="txt-power">--</span>
                </div>
                <div class="device-info-item">
                    <span>Địa chỉ IP LAN:</span>
                    <span id="txt-ip">116.111.186.59</span>
                </div>
                <div class="device-info-item">
                    <span>Địa chỉ MAC:</span>
                    <span id="txt-mac">A4:E5:7C:87:B4:04</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Hoạt động hiện tại -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">⚡ Hoạt Động Hiện Tại</div>
            </div>
            <div class="status-badge-lg status-ready" id="status-badge">
                <span id="status-icon">⏳</span>
                <span id="status-text">Đang kết nối...</span>
            </div>

            <div class="control-btns">
                <button class="btn-cmd btn-start" onclick="sendCommand('start')">
                    <span style="font-size:18px;">▶</span>
                    <span>Hút bụi</span>
                </button>
                <button class="btn-cmd btn-pause" onclick="sendCommand('pause')">
                    <span style="font-size:18px;">⏸</span>
                    <span>Tạm dừng</span>
                </button>
                <button class="btn-cmd btn-dock" onclick="sendCommand('dock')">
                    <span style="font-size:18px;">⚡</span>
                    <span>Về sạc</span>
                </button>
                <button class="btn-cmd btn-find" onclick="sendCommand('find')">
                    <span style="font-size:18px;">🔔</span>
                    <span>Tìm Robot</span>
                </button>
            </div>

            <div class="suction-selector">
                <label for="select-suction">Lực hút (Suction Mode):</label>
                <select id="select-suction" onchange="changeSuctionMode(this.value)">
                    <option value="Standard">Tiêu chuẩn (Standard)</option>
                    <option value="Boost_IQ">Tự động điều chỉnh (Boost_IQ)</option>
                    <option value="Max">Tối đa (Max)</option>
                </select>
            </div>
        </div>

        <!-- Card 3: Tự động hút khi đầy pin -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">🤖 Tự Động Hút Khi Đầy Pin</div>
            </div>

            <div class="switch-container">
                <div class="switch-info">
                    <h4>Tự động dọn khi sạc đầy</h4>
                    <p>Tự động xuất phát dọn dẹp khi pin nạp đủ</p>
                </div>
                <label class="switch">
                    <input type="checkbox" id="toggle-autoclean" onchange="toggleAutoClean(this.checked)">
                    <span class="slider"></span>
                </label>
            </div>

            <div class="autoclean-settings">
                <span>Ngưỡng pin kích hoạt:</span>
                <select id="select-trigger-level" onchange="changeTriggerLevel(this.value)">
                    <option value="100">100% (Pin đầy hoàn toàn)</option>
                    <option value="95">95%</option>
                    <option value="90">90%</option>
                </select>
            </div>

            <div class="autoclean-status-box" id="autoclean-live-box">
                <span>⏱</span>
                <span id="autoclean-live-desc">Đang kiểm tra điều kiện tự động...</span>
            </div>

            <div style="font-size:12px; font-weight:600; color:var(--text-muted); margin-top:8px;">
                Lịch sử kích hoạt tự động gần nhất:
            </div>
            <div class="log-list" id="autoclean-history">
                <div class="log-item">Chưa có bản ghi kích hoạt nào.</div>
            </div>
        </div>
    </div>

    <!-- Card 4: Lịch trình hoạt động -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📅 Lịch Trình Hoạt Động (RoboVac AV)</div>
            <small style="color:var(--text-muted); font-size:12px;">Đồng bộ từ Tuya Cloud</small>
        </div>

        <div style="overflow-x:auto;">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Giờ hẹn</th>
                        <th>Ngày áp dụng</th>
                        <th>Hành động</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody id="schedule-tbody">
                    <tr><td colspan="4" style="text-align:center; color:var(--text-muted);">Đang tải lịch trình...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        NAV Smart System • Nền Tảng Điều Khiển Robot Hút Bụi Thông Minh • Eufy & Tuya Cloud Protocol
    </div>
</div>

<div class="toast" id="toast-msg">Thông báo hệ thống</div>

<script>
    let currentBattery = 0;
    let isCurrentAtDock = false;
    let pollInterval = null;

    function showToast(msg) {
        const toast = document.getElementById('toast-msg');
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3500);
    }

    async function fetchStatus(isManual = false) {
        try {
            const res = await fetch('api.php?action=status');
            const data = await res.json();
            if (!data.success) {
                console.error("Lỗi lấy dữ liệu:", data.message);
                return;
            }

            const dev = data.data.device;
            const settings = data.data.settings;

            isCurrentAtDock = !!(dev.is_charging || dev.activity_code === 'Charging' || dev.activity_code === 'standby');

            // 1. Cập nhật Pin
            currentBattery = dev.battery;
            document.getElementById('battery-percent').textContent = dev.battery + '%';
            const bar = document.getElementById('battery-bar');
            bar.style.width = dev.battery + '%';

            if (dev.battery > 50) {
                bar.style.background = '#10b981'; // Green
            } else if (dev.battery > 20) {
                bar.style.background = '#f59e0b'; // Amber
            } else {
                bar.style.background = '#ef4444'; // Red
            }

            document.getElementById('battery-charging-box').style.display = dev.is_charging ? 'block' : 'none';
            document.getElementById('txt-power').textContent = dev.power_state ? 'Bật (ON)' : 'Tắt (OFF)';
            document.getElementById('txt-ip').textContent = dev.ip || '116.111.186.59';
            document.getElementById('txt-mac').textContent = dev.mac || 'A4:E5:7C:87:B4:04';
            document.getElementById('txt-updated').textContent = 'Cập nhật lúc: ' + dev.updated_at;

            // 2. Cập nhật Hoạt động
            const badge = document.getElementById('status-badge');
            badge.className = 'status-badge-lg ' + (dev.badge_class || 'status-ready');
            document.getElementById('status-text').textContent = dev.activity_text;

            let icon = '🤖';
            if (dev.is_cleaning) icon = '🧹';
            else if (dev.is_charging) icon = '⚡';
            else if (dev.error_code !== 'no_error') icon = '⚠️';
            document.getElementById('status-icon').textContent = icon;

            document.getElementById('select-suction').value = dev.suction_mode || 'Boost_IQ';

            // 3. Cập nhật Cài đặt tự động hóa
            document.getElementById('toggle-autoclean').checked = !!settings.auto_clean_enabled;
            document.getElementById('select-trigger-level').value = settings.trigger_battery_level || 100;

            renderHistory(settings.history || []);

            if (isManual) {
                showToast("Đã làm mới thông tin RoboVac AV!");
            }

            // Kiểm tra tự động hút bụi
            checkAutoCleanCondition();

        } catch (err) {
            console.error("Fetch status error:", err);
        }
    }

    async function checkAutoCleanCondition() {
        try {
            const res = await fetch('api.php?action=check_autoclean');
            const data = await res.json();
            const desc = document.getElementById('autoclean-live-desc');

            if (data.success && data.data) {
                if (data.data.triggered) {
                    desc.textContent = "⚡ " + data.data.message;
                    showToast("🎉 " + data.data.message);
                    fetchStatus(); // Lấy lại trạng thái ngay sau khi kích hoạt
                } else {
                    desc.textContent = data.data.reason || "Hệ thống đang theo dõi mức sạc...";
                }
            }
        } catch (e) {
            console.error("Auto clean check error:", e);
        }
    }

    async function fetchSchedules() {
        try {
            const res = await fetch('api.php?action=schedules');
            const data = await res.json();
            if (!data.success || !Array.isArray(data.data)) return;

            const tbody = document.getElementById('schedule-tbody');
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Chưa có lịch trình nào được cài đặt.</td></tr>';
                return;
            }

            tbody.innerHTML = data.data.map(item => `
                <tr>
                    <td><span class="time-badge">${item.time}</span></td>
                    <td><strong>${item.days_text}</strong></td>
                    <td>${item.action_desc}</td>
                    <td>
                        <span class="${item.status === 1 ? 'badge-enabled' : 'badge-disabled'}">
                            ${item.status === 1 ? 'ĐANG BẬT' : 'ĐÃ TẮT'}
                        </span>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            console.error("Schedule fetch error:", err);
        }
    }

    async function sendCommand(cmd) {
        try {
            const form = new FormData();
            form.append('cmd', cmd);
            const res = await fetch('api.php?action=command', { method: 'POST', body: form });
            const data = await res.json();
            if (cmd === 'dock' && isCurrentAtDock) {
                showToast("Đã gửi lệnh về dock! (Robot hiện đang ở sẵn trong dock)");
            } else {
                showToast(data.message || (data.success ? 'Thành công!' : 'Thất bại!'));
            }
            setTimeout(fetchStatus, 1000);
        } catch (err) {
            showToast("Lỗi gửi lệnh: " + err.message);
        }
    }

    async function changeSuctionMode(mode) {
        try {
            const form = new FormData();
            form.append('cmd', 'suction');
            form.append('mode', mode);
            const res = await fetch('api.php?action=command', { method: 'POST', body: form });
            const data = await res.json();
            showToast(data.message || 'Đã đổi lực hút');
        } catch (err) {
            showToast("Lỗi: " + err.message);
        }
    }

    async function toggleAutoClean(enabled) {
        try {
            const form = new FormData();
            form.append('auto_clean_enabled', enabled ? 'true' : 'false');
            const res = await fetch('api.php?action=save_settings', { method: 'POST', body: form });
            const data = await res.json();
            showToast(enabled ? "Đã BẬT tự động hút khi đầy pin!" : "Đã TẮT tự động hút khi đầy pin!");
            checkAutoCleanCondition();
        } catch (err) {
            showToast("Lỗi lưu cấu hình: " + err.message);
        }
    }

    async function changeTriggerLevel(level) {
        try {
            const form = new FormData();
            form.append('trigger_battery_level', level);
            const res = await fetch('api.php?action=save_settings', { method: 'POST', body: form });
            const data = await res.json();
            showToast(`Đã đổi ngưỡng kích hoạt sang ${level}%`);
            checkAutoCleanCondition();
        } catch (err) {
            showToast("Lỗi: " + err.message);
        }
    }

    function renderHistory(hist) {
        const box = document.getElementById('autoclean-history');
        if (!hist || hist.length === 0) {
            box.innerHTML = '<div class="log-item">Chưa có bản ghi kích hoạt nào.</div>';
            return;
        }
        box.innerHTML = hist.map(h => `
            <div class="log-item">
                <b>[${h.time}]</b> Pin: ${h.battery}% - ${h.message}
            </div>
        `).join('');
    }

    // Khởi tạo
    document.addEventListener('DOMContentLoaded', () => {
        fetchStatus();
        fetchSchedules();
        // Tự động kiểm tra mỗi 15 giây
        pollInterval = setInterval(() => {
            fetchStatus();
        }, 15000);
    });
</script>

</body>
</html>
