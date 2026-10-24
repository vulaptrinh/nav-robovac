<?php
// api.php - REST API phục vụ giao diện điều khiển và giám sát RoboVac AV

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
}

require_once __DIR__ . '/eufy_client.php';

function jsonResponse($success, $data = null, $message = '') {
    echo json_encode([
        'success'   => $success,
        'message'   => $message,
        'data'      => $data,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function getSettings() {
    if (!file_exists(SETTINGS_FILE)) {
        $default = [
            'auto_clean_enabled'     => true,
            'trigger_battery_level'  => 100,
            'cooldown_minutes'       => 60,
            'last_auto_cleaned_at'   => null,
            'last_auto_clean_battery'=> null,
            'history'                => []
        ];
        @file_put_contents(SETTINGS_FILE, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $default;
    }
    $content = @file_get_contents(SETTINGS_FILE);
    $data = json_decode($content, true);
    if (!is_array($data)) {
        $data = [
            'auto_clean_enabled'     => true,
            'trigger_battery_level'  => 100,
            'cooldown_minutes'       => 60,
            'last_auto_cleaned_at'   => null,
            'last_auto_clean_battery'=> null,
            'history'                => []
        ];
    }
    return $data;
}

function saveSettings($data) {
    return @file_put_contents(SETTINGS_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function writeAutoCleanLog($message) {
    $entry = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;
    @file_put_contents(AUTOCLEAN_LOG_FILE, $entry, FILE_APPEND);
}

$action = $_REQUEST['action'] ?? 'status';

try {
    $client = new RoboVacAVClient();

    switch ($action) {
        case 'status':
            $status = $client->getDeviceStatus();
            $settings = getSettings();
            jsonResponse(true, [
                'device'   => $status,
                'settings' => $settings
            ]);
            break;

        case 'schedules':
            $schedules = $client->getSchedules();
            jsonResponse(true, $schedules);
            break;

        case 'command':
            $cmd = $_POST['cmd'] ?? ($_GET['cmd'] ?? '');
            if ($cmd === 'start') {
                $client->startClean();
                jsonResponse(true, null, 'Đã gửi lệnh bắt đầu hút bụi thành công!');
            } elseif ($cmd === 'pause') {
                $client->pauseClean();
                jsonResponse(true, null, 'Đã gửi lệnh tạm dừng hút bụi!');
            } elseif ($cmd === 'dock') {
                $client->returnToDock();
                jsonResponse(true, null, 'Đã gửi lệnh quay về dock sạc!');
            } elseif ($cmd === 'find') {
                $client->findRobot();
                jsonResponse(true, null, 'Đã gửi lệnh phát chuông tìm Robot (tè té te)!');
            } elseif ($cmd === 'suction') {
                $mode = $_POST['mode'] ?? 'Boost_IQ';
                $client->setSuctionMode($mode);
                jsonResponse(true, null, "Đã chỉnh lực hút sang: {$mode}");
            } else {
                jsonResponse(false, null, 'Lệnh điều khiển không hợp lệ: ' . htmlspecialchars($cmd));
            }
            break;

        case 'get_settings':
            $settings = getSettings();
            jsonResponse(true, $settings);
            break;

        case 'save_settings':
            $settings = getSettings();
            if (isset($_POST['auto_clean_enabled'])) {
                $settings['auto_clean_enabled'] = filter_var($_POST['auto_clean_enabled'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($_POST['trigger_battery_level'])) {
                $level = (int)$_POST['trigger_battery_level'];
                if ($level >= 50 && $level <= 100) {
                    $settings['trigger_battery_level'] = $level;
                }
            }
            saveSettings($settings);
            jsonResponse(true, $settings, 'Cài đặt tự động hóa đã được cập nhật thành công!');
            break;

        case 'check_autoclean':
            // Logic kiểm tra tự động hút bụi khi đầy pin
            $settings = getSettings();
            if (empty($settings['auto_clean_enabled'])) {
                jsonResponse(true, [
                    'triggered' => false,
                    'reason'    => 'Tính năng tự động hút khi đầy pin đang TẮT.'
                ]);
            }

            $status = $client->getDeviceStatus();
            $currentBattery = $status['battery'];
            $triggerLevel = (int)($settings['trigger_battery_level'] ?? 100);
            $isChargingOrDocked = $status['is_charging'] || strtolower($status['activity_code']) === 'charging' || strtolower($status['activity_code']) === 'standby';
            $isAlreadyCleaning = $status['is_cleaning'] || $status['work_state'] || strtolower($status['activity_code']) === 'running';

            // Nếu robot đã đang hút bụi -> không cần kích hoạt lại
            if ($isAlreadyCleaning) {
                jsonResponse(true, [
                    'triggered' => false,
                    'battery'   => $currentBattery,
                    'reason'    => 'Robot hiện đang hoạt động dọn dẹp.'
                ]);
            }

            // Kiểm tra mức pin
            if ($currentBattery < $triggerLevel) {
                jsonResponse(true, [
                    'triggered' => false,
                    'battery'   => $currentBattery,
                    'target'    => $triggerLevel,
                    'reason'    => "Pin hiện tại ({$currentBattery}%) chưa đạt mức yêu cầu ({$triggerLevel}%)."
                ]);
            }

            // Kiểm tra cooldown
            $lastCleanedTime = !empty($settings['last_auto_cleaned_at']) ? strtotime($settings['last_auto_cleaned_at']) : 0;
            $cooldownSecs = ($settings['cooldown_minutes'] ?? 60) * 60;
            $elapsed = time() - $lastCleanedTime;

            if ($lastCleanedTime > 0 && $elapsed < $cooldownSecs) {
                $remainingMin = ceil(($cooldownSecs - $elapsed) / 60);
                jsonResponse(true, [
                    'triggered' => false,
                    'battery'   => $currentBattery,
                    'reason'    => "Đang trong thời gian giãn cách (cooldown). Còn {$remainingMin} phút nữa."
                ]);
            }

            // ĐỦ ĐIỀU KIỆN: KÍCH HOẠT HÚT BỤI TỰ ĐỘNG!
            $client->startClean();

            $nowStr = date('Y-m-d H:i:s');
            $logMsg = "TỰ ĐỘNG HÚT BỤI THÀNH CÔNG: Pin đạt {$currentBattery}% (Yêu cầu {$triggerLevel}%). Đã kích hoạt RoboVac AV bắt đầu quét dọn.";
            writeAutoCleanLog($logMsg);

            $settings['last_auto_cleaned_at'] = $nowStr;
            $settings['last_auto_clean_battery'] = $currentBattery;
            if (!isset($settings['history']) || !is_array($settings['history'])) {
                $settings['history'] = [];
            }
            array_unshift($settings['history'], [
                'time'    => $nowStr,
                'battery' => $currentBattery,
                'message' => "Tự động kích hoạt khi pin đạt {$currentBattery}%"
            ]);
            // Giữ tối đa 20 bản ghi
            $settings['history'] = array_slice($settings['history'], 0, 20);
            saveSettings($settings);

            jsonResponse(true, [
                'triggered' => true,
                'battery'   => $currentBattery,
                'time'      => $nowStr,
                'message'   => $logMsg
            ], $logMsg);
            break;

        default:
            jsonResponse(false, null, "Action không hợp lệ: " . htmlspecialchars($action));
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Lỗi hệ thống: ' . $e->getMessage());
}
