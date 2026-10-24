<?php
// auto_clean_worker.php - Tiến trình nền tự động giám sát mức pin và kích hoạt hút bụi cho RoboVac AV

if (php_sapi_name() !== 'cli') {
    die("Tiến trình này chỉ có thể chạy từ Command Line (CLI).\n");
}

require_once __DIR__ . '/eufy_client.php';

$isOnce = in_array('--once', $argv);
$checkInterval = 60; // 60 giây kiểm tra một lần

echo "=========================================================\n";
echo "  ROBOVAC AV - TIẾN TRÌNH NỀN TỰ ĐỘNG HÚT BỤI KHI ĐẦY PIN  \n";
echo "  Thiết bị: " . ROBOVAC_NAME . " (" . ROBOVAC_DEV_ID . ")\n";
echo "  Chu kỳ kiểm tra: " . $checkInterval . " giây/lần\n";
echo "=========================================================\n\n";

function logConsole($msg) {
    $time = date('Y-m-d H:i:s');
    echo "[{$time}] {$msg}\n";
}

function getWorkerSettings() {
    if (!file_exists(SETTINGS_FILE)) {
        return [
            'auto_clean_enabled'     => true,
            'trigger_battery_level'  => 100,
            'cooldown_minutes'       => 60,
            'last_auto_cleaned_at'   => null,
            'last_auto_clean_battery'=> null,
            'history'                => []
        ];
    }
    $data = json_decode(@file_get_contents(SETTINGS_FILE), true);
    return is_array($data) ? $data : [];
}

function saveWorkerSettings($data) {
    @file_put_contents(SETTINGS_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function writeWorkerLog($msg) {
    $entry = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;
    @file_put_contents(AUTOCLEAN_LOG_FILE, $entry, FILE_APPEND);
}

try {
    $client = new RoboVacAVClient();
} catch (Exception $e) {
    logConsole("Lỗi kết nối RoboVac AV: " . $e->getMessage());
    exit(1);
}

do {
    try {
        $settings = getWorkerSettings();
        $enabled = !empty($settings['auto_clean_enabled']);
        $triggerLevel = (int)($settings['trigger_battery_level'] ?? 100);

        if (!$enabled) {
            logConsole("Tính năng tự động hút khi đầy pin đang TẮT trong cài đặt.");
        } else {
            $status = $client->getDeviceStatus();
            $battery = $status['battery'];
            $activity = $status['activity_text'];
            $isAlreadyCleaning = $status['is_cleaning'] || $status['work_state'] || strtolower($status['activity_code']) === 'running';

            logConsole("Kiểm tra pin: {$battery}% | Trạng thái: {$activity}");

            if ($isAlreadyCleaning) {
                logConsole("-> Robot hiện đang dọn dẹp, không cần gửi thêm lệnh.");
            } elseif ($battery < $triggerLevel) {
                logConsole("-> Pin ({$battery}%) chưa đạt mức yêu cầu ({$triggerLevel}%). Tiếp tục theo dõi.");
            } else {
                // Kiểm tra cooldown
                $lastCleaned = !empty($settings['last_auto_cleaned_at']) ? strtotime($settings['last_auto_cleaned_at']) : 0;
                $cooldownSecs = ($settings['cooldown_minutes'] ?? 60) * 60;
                $elapsed = time() - $lastCleaned;

                if ($lastCleaned > 0 && $elapsed < $cooldownSecs) {
                    $remainMin = ceil(($cooldownSecs - $elapsed) / 60);
                    logConsole("-> Đang trong thời gian giãn cách (cooldown). Còn {$remainMin} phút.");
                } else {
                    // KÍCH HOẠT HÚT BỤI!
                    logConsole("⚡ PIN ĐÃ ĐẠT {$battery}% (>= {$triggerLevel}%)! Gửi lệnh tự động hút bụi...");
                    $client->startClean();

                    $nowStr = date('Y-m-d H:i:s');
                    $msg = "TIẾN TRÌNH NỀN: Tự động kích hoạt RoboVac AV hút bụi khi pin đạt {$battery}%.";
                    writeWorkerLog($msg);

                    $settings['last_auto_cleaned_at'] = $nowStr;
                    $settings['last_auto_clean_battery'] = $battery;
                    if (!isset($settings['history']) || !is_array($settings['history'])) {
                        $settings['history'] = [];
                    }
                    array_unshift($settings['history'], [
                        'time'    => $nowStr,
                        'battery' => $battery,
                        'message' => "Worker tự động kích hoạt (Pin {$battery}%)"
                    ]);
                    $settings['history'] = array_slice($settings['history'], 0, 20);
                    saveWorkerSettings($settings);

                    logConsole("✓ Đã gửi lệnh thành công và cập nhật lịch sử!");
                }
            }
        }
    } catch (Exception $e) {
        logConsole("Lỗi trong chu kỳ kiểm tra: " . $e->getMessage());
    }

    if ($isOnce) {
        break;
    }

    sleep($checkInterval);
} while (true);
