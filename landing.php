<?php
// landing.php

// Capture the user agent string
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// Capture the IP address
$ip_address = $_SERVER['REMOTE_ADDR'];

// Capture the referrer URL
$referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct Access';

// Capture the request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Capture the request URI
$request_uri = $_SERVER['REQUEST_URI'];

// Capture the server name
$server_name = $_SERVER['SERVER_NAME'];

// Capture the preferred language
$accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

// Capture cookies
$cookies = json_encode($_COOKIE);

// Capture the query string
$query_string = $_SERVER['QUERY_STRING'];

// Function to detect OS
function getOS($user_agent) {
    $os_platform = "Unknown OS";
    $os_array = [
        '/windows nt 11.0/i'=> 'Windows 11',
        '/windows nt 10.0/i'=> 'Windows 10',
        '/windows nt 6.3/i' => 'Windows 8.1',
        '/windows nt 6.2/i' => 'Windows 8',
        '/windows nt 6.1/i' => 'Windows 7',
        '/windows nt 6.0/i' => 'Windows Vista',
        '/windows nt 5.1/i' => 'Windows XP',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/linux/i'  => 'Linux',
        '/iphone/i' => 'iOS',
        '/android/i'=> 'Android'
    ];

    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            if (strpos($user_agent, 'Windows NT 10.0') !== false && strpos($user_agent, 'Edg') !== false) {
                // Assume Windows 11 if using Edge on Windows NT 10.0
                $os_platform = 'Windows 11';
            } else {
                $os_platform = $value;
            }
        }
    }

    return $os_platform;
}

// Function to detect device type
function getDeviceType($user_agent) {
    if (preg_match('/mobile/i', $user_agent)) { return "Mobile";
    } elseif (preg_match('/tablet/i', $user_agent)) { return "Tablet";
    } else { return "PC/Laptop"; }
}

// Function to detect browser
function getBrowser($user_agent) {
    $browser = "Unknown Browser";
    $browser_array = [
        '/msie/i'       => 'Internet Explorer',
        '/firefox/i'    => 'Firefox',
        '/safari/i'     => 'Safari',
        '/chrome/i'     => 'Chrome',
        '/edge/i'       => 'Edge',
        '/opera/i'      => 'Opera',
        '/netscape/i'   => 'Netscape',
        '/maxthon/i'    => 'Maxthon',
        '/konqueror/i'  => 'Konqueror',
        '/mobile/i'     => 'Mobile Browser'
    ];

    foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $browser = $value;
        }
    }

    return $browser;
}

// Get OS from user agent
$os = getOS($user_agent);

// Get device type from user agent
$device_type = getDeviceType($user_agent);

// Get browser from user agent
$browser = getBrowser($user_agent);

// Get User IP
function getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$LastIP     = getUserIpAddr();
// Fetching details from IPAPI
$details    = json_decode(file_get_contents("https://api.ipapi.is?q={$LastIP}"), true);

// Error handling if API request fails
if ($details === null) {
    $Location = "Unknown";
    $ISP = "Unknown";
    $isProxy = 0;
    $isTor = 0;
    $isVPN = 0;
} else {
    if (isset($details['is_bogon']) && $details['is_bogon'] === true) {
        // IP is a bogon IP
        $Location = "Local";
        $ISP      = "Local";
        $isProxy  = isset($details['is_proxy']) && $details['is_proxy'] ? 1 : 0;
        $isTor    = isset($details['is_tor']) && $details['is_tor'] ? 1 : 0;
        $isVPN    = isset($details['is_vpn']) && $details['is_vpn'] ? 1 : 0;
    } else {
        // Regular IP with location details
        $Location = $details['location']['country']." - ".$details['location']['city'];
        $ISP      = $details['asn']['org'];
        $isProxy  = isset($details['is_proxy']) && $details['is_proxy'] ? 1 : 0;
        $isTor    = isset($details['is_tor']) && $details['is_tor'] ? 1 : 0;
        $isVPN    = isset($details['is_vpn']) && $details['is_vpn'] ? 1 : 0;
    }
}

// Set the default timezone to Cairo, Egypt
date_default_timezone_set('Africa/Cairo');

// Get the current date and time in Cairo
$date_time = date('Y-m-d H:i:s');

// Log the information (you can also store it in a database)
// Date and Time | IP Address | Location | ISP | isProxy |  isTor | isVPN | Referrer | Request Method | Request URI | Server Name | Preferred Language | Cookies | Query String | Device Type | OS | Browser | User Agent
file_put_contents('log.txt', "$date_time | $ip_address | $Location | $ISP | $isProxy | $isTor | $isVPN | $referrer | $request_method | $request_uri | $server_name | $accept_language | $cookies | $query_string | $device_type | $os | $browser | $user_agent\n", FILE_APPEND);

// Redirect to the final URL
$redirect_url = "http://www.example.com/final-destination";
header("Location: $redirect_url");
exit;
?>
