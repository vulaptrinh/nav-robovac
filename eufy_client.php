<?php
// eufy_client.php - Lớp kết nối và điều khiển RoboVac AV qua Tuya/Eufy Cloud

require_once __DIR__ . '/config.php';

function shuffled_md5($value) {
    $hash = md5($value);
    return substr($hash, 8, 8) . substr($hash, 0, 8) . substr($hash, 24, 8) . substr($hash, 16, 8);
}

function unpadded_rsa($key_exponent, $key_n, $plaintext) {
    $key_n_gmp = gmp_init($key_n);
    $key_exp_gmp = gmp_init($key_exponent);
    $input_gmp = gmp_import($plaintext, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
    $crypted_gmp = gmp_powm($input_gmp, $key_exp_gmp, $key_n_gmp);
    $bin = gmp_export($crypted_gmp, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
    
    $bit_len = strlen(gmp_strval($key_n_gmp, 2));
    $keylength = (int)ceil($bit_len / 8);
    
    return str_pad($bin, $keylength, "\0", STR_PAD_LEFT);
}

function determine_password($username, $tuyaPasswordKey, $tuyaPasswordIv) {
    $len = strlen($username);
    $padded_size = 16 * (int)ceil($len / 16);
    $password_uid = str_pad($username, $padded_size, "0", STR_PAD_LEFT);
    
    $encrypted_uid = openssl_encrypt(
        $password_uid,
        'aes-128-cbc',
        $tuyaPasswordKey,
        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
        $tuyaPasswordIv
    );
    
    return md5(strtoupper(bin2hex($encrypted_uid)));
}

function generate_tuya_device_id() {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $prefix = "8534c8ec0ed0";
    $rem = 44 - strlen($prefix);
    $res = $prefix;
    for ($i = 0; $i < $rem; $i++) {
        $res .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $res;
}

function get_tuya_signature($query_params, $encoded_post_data, $hmac_key) {
    $sig_params = [
        "a", "v", "lat", "lon", "lang", "deviceId", "appVersion", "ttid",
        "isH5", "h5Token", "os", "clientId", "postData", "time", "requestId",
        "et", "n4h5", "sid", "sp"
    ];
    $sig_set = array_flip($sig_params);
    
    $params = $query_params;
    if (!empty($encoded_post_data)) {
        $params['postData'] = $encoded_post_data;
    }
    
    ksort($params);
    $pairs = [];
    foreach ($params as $k => $v) {
        if ($k !== '' && isset($sig_set[$k])) {
            $val = ($k === 'postData') ? shuffled_md5($v) : (string)$v;
            $pairs[] = "{$k}={$val}";
        }
    }
    
    $msg = implode("||", $pairs);
    return hash_hmac('sha256', $msg, $hmac_key);
}

function callEufyApi($url, $method = 'GET', $data = null, $token = null, $uid = null) {
    $ch = curl_init();
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: EufyHome-Android-2.4.0',
        'timezone: Asia/Ho_Chi_Minh',
        'category: Home',
        'openudid: sdk_gphone64_arm64',
        'clientType: 2',
        'language: vi',
        'country: VN'
    ];

    if ($token) {
        $headers[] = 'token: ' . $token;
    }
    if ($uid) {
        $headers[] = 'uid: ' . $uid;
    }

    $options = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => $headers
    ];

    if ($method === 'POST') {
        $jsonPayload = json_encode($data, JSON_UNESCAPED_SLASHES);
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = $jsonPayload;
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    return [
        'code'     => $httpCode,
        'response' => $response,
        'error'    => $error
    ];
}

class TuyaClient {
    public $username;
    public $country_code;
    public $session_id = null;
    public $base_url;
    public $device_id;
    public $default_query_params;
    public $hmac_key;
    public $tuyaPasswordKey;
    public $tuyaPasswordIv;
    public $cached_home_id = null;
    
    public function __construct($username, $country_code, $hmac_key, $tuyaPasswordKey, $tuyaPasswordIv, $initial_url, $client_id) {
        $this->username = $username;
        $this->country_code = $country_code;
        $this->hmac_key = $hmac_key;
        $this->tuyaPasswordKey = $tuyaPasswordKey;
        $this->tuyaPasswordIv = $tuyaPasswordIv;
        $this->base_url = $initial_url;
        $this->device_id = generate_tuya_device_id();
        
        $this->default_query_params = [
            'appVersion' => '2.4.0',
            'deviceId'   => $this->device_id,
            'platform'   => 'sdk_gphone64_arm64',
            'clientId'   => $client_id,
            'lang'       => 'en',
            'osSystem'   => '12',
            'os'         => 'Android',
            'timeZoneId' => 'Asia/Ho_Chi_Minh',
            'ttid'       => 'android',
            'et'         => '0.0.1',
            'sdkVersion' => '3.0.8cAnker',
        ];

        // Khôi phục session cache nếu có
        $this->load_cached_session();
    }

    private function load_cached_session() {
        if (file_exists(CACHE_SESSION_FILE)) {
            $data = json_decode(@file_get_contents(CACHE_SESSION_FILE), true);
            if (!empty($data['session_id']) && !empty($data['base_url']) && ($data['username'] ?? '') === $this->username) {
                // Hạn dùng cache 6 giờ
                if (time() - ($data['saved_at'] ?? 0) < 21600) {
                    $this->session_id = $this->default_query_params['sid'] = $data['session_id'];
                    $this->base_url = $data['base_url'];
                    $this->cached_home_id = $data['home_id'] ?? null;
                    if (!empty($data['device_id'])) {
                        $this->device_id = $this->default_query_params['deviceId'] = $data['device_id'];
                    }
                }
            }
        }
    }

    private function save_cached_session() {
        $data = [
            'username'   => $this->username,
            'session_id' => $this->session_id,
            'base_url'   => $this->base_url,
            'device_id'  => $this->device_id,
            'home_id'    => $this->cached_home_id,
            'saved_at'   => time()
        ];
        @file_put_contents(CACHE_SESSION_FILE, json_encode($data));
    }
    
    public function request($action, $version = '1.0', $data = null, $extra_query_params = [], $requires_session = true, $retryOnAuthFail = true) {
        if (!$this->session_id && $requires_session) {
            $this->acquire_session();
        }
        
        $current_time = time();
        $request_id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        
        $qp = array_merge($this->default_query_params, [
            'time'      => (string)$current_time,
            'requestId' => $request_id,
            'a'         => $action,
            'v'         => $version,
        ], $extra_query_params);
        
        $encoded_post_data = !empty($data) ? json_encode($data, JSON_UNESCAPED_SLASHES) : '';
        $qp['sign'] = get_tuya_signature($qp, $encoded_post_data, $this->hmac_key);
        
        $url = rtrim($this->base_url, '/') . '/api.json?' . http_build_query($qp);
        
        $ch = curl_init($url);
        $options = [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'User-Agent: TY-UA=APP/Android/2.4.0/SDK/null'
            ]
        ];
        
        if (!empty($encoded_post_data)) {
            $options[CURLOPT_POSTFIELDS] = http_build_query(['postData' => $encoded_post_data]);
        }
        
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        
        $json = json_decode($res, true);
        if (!isset($json['result'])) {
            $errorCode = $json['errorCode'] ?? '';
            // Nếu session hết hạn hoặc không hợp lệ -> acquire session lại một lần
            if ($retryOnAuthFail && in_array($errorCode, ['SESSION_EXPIRED', 'SIGN_EXPIRED', 'USER_SESSION_INVALID'])) {
                $this->session_id = null;
                $this->acquire_session();
                return $this->request($action, $version, $data, $extra_query_params, $requires_session, false);
            }
            $msg = $json['errorMsg'] ?? ($json['message'] ?? $res);
            throw new Exception("Lỗi Tuya API ({$action}, HTTP {$code}): {$msg} ({$err})");
        }
        return $json['result'];
    }
    
    public function acquire_session() {
        $token_resp = $this->request('tuya.m.user.uid.token.create', '1.0', [
            'uid'         => $this->username,
            'countryCode' => $this->country_code,
        ], [], false, false);
        
        $password = determine_password($this->username, $this->tuyaPasswordKey, $this->tuyaPasswordIv);
        $enc_pass = unpadded_rsa($token_resp['exponent'], $token_resp['publicKey'], $password);
        
        $login_data = [
            'uid'         => $this->username,
            'createGroup' => true,
            'ifencrypt'   => 1,
            'passwd'      => bin2hex($enc_pass),
            'countryCode' => $this->country_code,
            'options'     => '{"group": 1}',
            'token'       => $token_resp['token']
        ];
        
        $sess_resp = $this->request('tuya.m.user.uid.password.login.reg', '1.0', $login_data, [], false, false);
        $this->session_id = $this->default_query_params['sid'] = $sess_resp['sid'];
        $this->base_url = $sess_resp['domain']['mobileApiUrl'];
        $this->save_cached_session();
    }
    
    public function list_homes() {
        return $this->request('tuya.m.location.list', '2.1');
    }

    public function get_home_id() {
        if ($this->cached_home_id) {
            return $this->cached_home_id;
        }
        $homes = $this->list_homes();
        if (!empty($homes[0]['groupId'])) {
            $this->cached_home_id = $homes[0]['groupId'];
            $this->save_cached_session();
            return $this->cached_home_id;
        }
        return null;
    }
    
    public function list_devices($home_id = null) {
        if (!$home_id) {
            $home_id = $this->get_home_id();
        }
        $own = $this->request('tuya.m.my.group.device.list', '1.0', null, ['gid' => $home_id]);
        $shared = $this->request('tuya.m.my.shared.device.list', '1.0');
        return array_merge($own ?: [], $shared ?: []);
    }

    public function publish_dps($devId, $dpsArray) {
        return $this->request('tuya.m.device.dp.publish', '1.0', [
            'devId' => $devId,
            'dps'   => json_encode($dpsArray)
        ]);
    }

    public function get_timers($devId) {
        return $this->request('tuya.m.timer.list', '1.0', [
            'bizId'   => $devId,
            'bizType' => '0'
        ]);
    }
}

class RoboVacAVClient {
    private $tuyaClient = null;
    private $devId = ROBOVAC_DEV_ID;

    public function __construct() {
        $this->initTuyaClient();
    }

    private function initTuyaClient() {
        // Đăng nhập Eufy để lấy UID
        $loginCacheFile = DATA_DIR . '/eufy_token.json';
        $userId = null;
        $phoneCode = '84';

        if (file_exists($loginCacheFile)) {
            $cache = json_decode(@file_get_contents($loginCacheFile), true);
            if (!empty($cache['userId']) && time() - ($cache['saved_at'] ?? 0) < 86400) {
                $userId = $cache['userId'];
                $phoneCode = $cache['phoneCode'] ?? '84';
            }
        }

        if (!$userId) {
            $loginRes = callEufyApi(EUFY_BASE_URL . 'user/email/login', 'POST', [
                'client_Secret' => EUFY_CLIENT_SECRET,
                'client_id'     => EUFY_CLIENT_ID,
                'email'         => DEFAULT_EUFY_EMAIL,
                'password'      => DEFAULT_EUFY_PASSWORD
            ]);
            $body = json_decode($loginRes['response'], true);
            if (empty($body['user_info']['id'])) {
                throw new Exception("Đăng nhập Eufy thất bại: " . ($body['message'] ?? $loginRes['error']));
            }
            $userId = $body['user_info']['id'];
            $phoneCode = $body['user_info']['phone_code'] ?: '84';
            @file_put_contents($loginCacheFile, json_encode([
                'userId'    => $userId,
                'phoneCode' => $phoneCode,
                'token'     => $body['access_token'] ?? '',
                'saved_at'  => time()
            ]));
        }

        $tuyaUsername = "eh-{$userId}";
        $this->tuyaClient = new TuyaClient(
            $tuyaUsername,
            $phoneCode,
            EUFY_HMAC_KEY,
            $GLOBALS['TUYA_PASS_KEY'],
            $GLOBALS['TUYA_PASS_IV'],
            TUYA_INITIAL_BASE_URL,
            TUYA_CLIENT_ID
        );
    }

    public function getDeviceStatus() {
        $devices = $this->tuyaClient->list_devices();
        $targetDev = null;
        foreach ($devices as $d) {
            if ($d['devId'] === $this->devId) {
                $targetDev = $d;
                break;
            }
        }

        if (!$targetDev) {
            throw new Exception("Không tìm thấy thiết bị RoboVac AV (" . $this->devId . ")");
        }

        $dps = $targetDev['dps'] ?? [];
        $battery = isset($dps['104']) ? (int)$dps['104'] : 0;
        $activityCode = $dps['15'] ?? 'Standby';
        $workState = !empty($dps['2']);
        $powerState = !empty($dps['1']);
        $suctionMode = $dps['102'] ?? 'Standard';
        $cleanMode = $dps['5'] ?? 'auto';
        $errorCode = $dps['106'] ?? 'no_error';

        // Phân tích trạng thái hoạt động tiếng Việt
        $activityText = 'Chờ lệnh';
        $badgeClass = 'status-standby';
        $isCharging = false;
        $isCleaning = false;

        $actLower = strtolower($activityCode);
        if ($actLower === 'charging' || $actLower === 'recharge' || strpos($actLower, 'charge') !== false) {
            if ($battery >= 100) {
                $activityText = 'Đã sạc đầy 100% (Ở Dock)';
                $badgeClass = 'status-full';
            } else {
                $activityText = 'Đang sạc pin';
                $badgeClass = 'status-charging';
            }
            $isCharging = true;
        } elseif ($actLower === 'running' || $actLower === 'cleaning' || $workState === true) {
            $activityText = 'Đang hút bụi (' . ucfirst($cleanMode) . ')';
            $badgeClass = 'status-cleaning';
            $isCleaning = true;
        } elseif ($actLower === 'completed') {
            $activityText = 'Đã hoàn thành dọn dẹp';
            $badgeClass = 'status-completed';
        } elseif ($actLower === 'sleeping') {
            $activityText = 'Đang ngủ / Tiết kiệm pin';
            $badgeClass = 'status-sleeping';
        } elseif ($errorCode !== 'no_error') {
            $activityText = 'Báo lỗi: ' . $errorCode;
            $badgeClass = 'status-error';
        } else {
            $activityText = 'Đang ở Dock chờ / Sẵn sàng';
            $badgeClass = 'status-ready';
        }

        return [
            'dev_id'         => $this->devId,
            'name'           => $targetDev['name'] ?? ROBOVAC_NAME,
            'model'          => ROBOVAC_MODEL,
            'online'         => !empty($targetDev['moduleMap']['wifi']['isOnline']),
            'battery'        => $battery,
            'activity_code'  => $activityCode,
            'activity_text'  => $activityText,
            'badge_class'    => $badgeClass,
            'is_charging'    => $isCharging,
            'is_cleaning'    => $isCleaning,
            'work_state'     => $workState,
            'power_state'    => $powerState,
            'suction_mode'   => $suctionMode,
            'clean_mode'     => $cleanMode,
            'error_code'     => $errorCode,
            'ip'             => $targetDev['ip'] ?? '',
            'mac'            => $targetDev['mac'] ?? '',
            'updated_at'     => date('H:i:s d/m/Y'),
            'raw_dps'        => $dps
        ];
    }

    public function getSchedules() {
        $timers = $this->tuyaClient->get_timers($this->devId);
        $schedules = [];

        // Bảng ánh xạ bitmask tuần Tuya (bit 0-6: Sun, Mon, Tue, Wed, Thu, Fri, Sat)
        $dayNames = [
            0 => 'Chủ Nhật',
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy'
        ];

        foreach ($timers as $t) {
            $loops = $t['loops'] ?? '0000000';
            $activeDays = [];
            
            // Loop string có độ dài 7 ký tự (0-6)
            for ($i = 0; $i < 7; $i++) {
                if (isset($loops[$i]) && $loops[$i] === '1') {
                    $activeDays[] = $dayNames[$i] ?? "Ngày $i";
                }
            }

            $daysText = !empty($activeDays) ? implode(', ', $activeDays) : 'Một lần';
            if ($loops === '1111111') {
                $daysText = 'Hàng ngày (T2 - CN)';
            } elseif ($loops === '0111110') {
                $daysText = 'Các ngày trong tuần (T2 - T6)';
            } elseif ($loops === '1000001') {
                $daysText = 'Cuối tuần (T7, CN)';
            }

            $schedules[] = [
                'id'         => $t['id'],
                'time'       => $t['time'] ?? '00:00',
                'status'     => (int)($t['status'] ?? 0),
                'loops'      => $loops,
                'days_text'  => $daysText,
                'timezone'   => $t['timezoneId'] ?? 'Asia/Ho_Chi_Minh',
                'action_desc'=> 'Hút bụi tự động (Auto Clean)'
            ];
        }

        // Sắp xếp lịch theo giờ
        usort($schedules, function($a, $b) {
            return strcmp($a['time'], $b['time']);
        });

        return $schedules;
    }

    public function startClean() {
        return $this->tuyaClient->publish_dps($this->devId, [
            '2' => true,
            '5' => 'auto'
        ]);
    }

    public function pauseClean() {
        return $this->tuyaClient->publish_dps($this->devId, [
            '2' => false
        ]);
    }

    public function returnToDock() {
        return $this->tuyaClient->publish_dps($this->devId, [
            '101' => true,
            '103' => true,
            '2'   => false
        ]);
    }

    public function setSuctionMode($mode) {
        $validModes = ['Standard', 'Boost_IQ', 'Max'];
        if (!in_array($mode, $validModes)) {
            $mode = 'Boost_IQ';
        }
        return $this->tuyaClient->publish_dps($this->devId, [
            '102' => $mode
        ]);
    }
}
