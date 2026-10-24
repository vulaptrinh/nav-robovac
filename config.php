<?php
// config.php - Cấu hình hệ thống cho riêng robot RoboVac AV

define('EUFY_CLIENT_ID', 'eufyhome-app');
define('EUFY_CLIENT_SECRET', 'GQCpr9dSp3uQpsOMgJ4xQ');
define('EUFY_BASE_URL', 'https://home-api.eufylife.com/v1/');

define('TUYA_CLIENT_ID', 'yx5v9uc3ef9wg3v9atje');
define('TUYA_INITIAL_BASE_URL', 'https://a1.tuyaeu.com');
define('BMP_SECRET', 'cepev5pfnhua4dkqkdpmnrdxx378mpjr');
define('APPSECRET', 's8x78u7xwymasd9kqa7a73pjhxqsedaj');
define('EUFY_HMAC_KEY', 'A_' . BMP_SECRET . '_' . APPSECRET);

define('DEFAULT_EUFY_EMAIL', 'vu.laptrinh@gmail.com');
define('DEFAULT_EUFY_PASSWORD', '04092021@Huyentran');

// Cấu hình riêng cho robot RoboVac AV
define('ROBOVAC_DEV_ID', '32783030a4e57c87b404');
define('ROBOVAC_NAME', 'RoboVac AV');
define('ROBOVAC_MODEL', 'T2118');
define('ROBOVAC_LOCAL_KEY', ':cXNoJQqi*5szfaK');

// Khóa giải mã Tuya AES-128
$GLOBALS['TUYA_PASS_KEY'] = pack('C*', 36, 78, 109, 138, 86, 172, 135, 145, 36, 67, 45, 139, 108, 188, 162, 196);
$GLOBALS['TUYA_PASS_IV']  = pack('C*', 119, 36, 86, 242, 167, 102, 76, 243, 57, 44, 53, 151, 233, 62, 87, 71);

// Đường dẫn lưu trữ dữ liệu
define('DATA_DIR', __DIR__ . '/data');
define('SETTINGS_FILE', DATA_DIR . '/settings.json');
define('AUTOCLEAN_LOG_FILE', DATA_DIR . '/autoclean.log');
define('CACHE_SESSION_FILE', DATA_DIR . '/session_cache.json');

if (!is_dir(DATA_DIR)) {
    @mkdir(DATA_DIR, 0777, true);
}
