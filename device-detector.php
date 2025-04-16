<?php
/**
 * DeviceDetector - A comprehensive PHP class for detecting devices, browsers, and device properties
 * 
 * Features:
 * - Detects device type (mobile, tablet, desktop)
 * - Identifies browser and operating system
 * - Determines screen resolution and orientation
 * - Sets custom variables for conditional logic
 * - Auto-reloads page to get accurate screen information
 * - Provides utility methods for responsive design
 */
class DeviceDetector {
    // Store user agent
    private $userAgent;
    
    // Device information
    private $deviceType;
    private $browser;
    private $browserVersion;
    private $os;
    private $osVersion;
    private $isMobile;
    private $isTablet;
    private $isDesktop;
    private $isBot;
    
    // Screen properties
    private $screenWidth;
    private $screenHeight;
    private $orientation;
    
    // Custom variables
    private $customVars = [];
    
    // Cookie expiration time (in seconds)
    private $cookieExpiration = 86400; // 24 hours
    
    /**
     * Constructor
     * 
     * @param bool $autoReload Whether to automatically reload the page to get screen info
     */
    public function __construct($autoReload = true) {
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->detectDevice();
        
        // Check if we need to set cookies and reload
        if ($autoReload && !isset($_COOKIE['device_detected']) && !isset($_GET['no_reload'])) {
            $this->setJavaScriptDetection();
            exit; // Stop execution after script is sent
        }
        
        $this->detectScreenProperties();
        $this->setDefaultCustomVars();
    }
    
    /**
     * Detect the device type, browser, and OS
     */
    private function detectDevice() {
        // Detect if device is a bot
        $botPatterns = [
            'googlebot', 'bingbot', 'yandexbot', 'baiduspider', 'facebookexternalhit',
            'twitterbot', 'rogerbot', 'linkedinbot', 'embedly', 'quora link preview',
            'showyoubot', 'outbrain', 'pinterest', 'slackbot', 'vkShare', 'W3C_Validator'
        ];
        
        $this->isBot = false;
        foreach ($botPatterns as $botPattern) {
            if (stripos($this->userAgent, $botPattern) !== false) {
                $this->isBot = true;
                break;
            }
        }
        
        // Detect if device is mobile
        $mobilePatterns = [
            'Mobile', 'Android', 'iPhone', 'iPod', 'BlackBerry', 'Windows Phone',
            'webOS', 'Opera Mini', 'IEMobile', 'Silk/'
        ];
        
        $this->isMobile = false;
        foreach ($mobilePatterns as $mobilePattern) {
            if (stripos($this->userAgent, $mobilePattern) !== false) {
                $this->isMobile = true;
                break;
            }
        }
        
        // Detect if device is tablet
        $tabletPatterns = [
            'iPad', 'Android(?!.*Mobile)', 'Tablet', 'Kindle', 'Silk',
            'PlayBook', 'Nexus 7', 'Nexus 10'
        ];
        
        $this->isTablet = false;
        foreach ($tabletPatterns as $tabletPattern) {
            if (preg_match('/' . $tabletPattern . '/i', $this->userAgent)) {
                $this->isTablet = true;
                break;
            }
        }
        
        // If device is tablet, it's not mobile
        if ($this->isTablet) {
            $this->isMobile = false;
        }
        
        // If device is neither mobile nor tablet, it's desktop
        $this->isDesktop = !$this->isMobile && !$this->isTablet;
        
        // Set device type
        if ($this->isBot) {
            $this->deviceType = 'bot';
        } elseif ($this->isMobile) {
            $this->deviceType = 'mobile';
        } elseif ($this->isTablet) {
            $this->deviceType = 'tablet';
        } else {
            $this->deviceType = 'desktop';
        }
        
        // Detect browser
        $this->detectBrowser();
        
        // Detect operating system
        $this->detectOS();
    }
    
    /**
     * Detect the browser and its version
     */
    private function detectBrowser() {
        $browsers = [
            'Chrome' => 'Chrome',
            'Firefox' => 'Firefox',
            'MSIE' => 'Internet Explorer',
            'Trident/7.0' => 'Internet Explorer 11',
            'Edge' => 'Edge',
            'Edg' => 'Edge',
            'Safari' => 'Safari',
            'Opera' => 'Opera',
            'OPR' => 'Opera',
            'SamsungBrowser' => 'Samsung Browser',
            'UCBrowser' => 'UC Browser',
            'YaBrowser' => 'Yandex Browser'
        ];
        
        $this->browser = 'Unknown';
        $this->browserVersion = '';
        
        foreach ($browsers as $key => $value) {
            if (strpos($this->userAgent, $key) !== false) {
                $this->browser = $value;
                
                // Extract version
                if (preg_match('/' . preg_quote($key, '/') . '\s*[\/|:]\s*([0-9\.]+)/', $this->userAgent, $matches)) {
                    $this->browserVersion = $matches[1];
                } elseif ($key === 'Trident/7.0') {
                    $this->browserVersion = '11.0';
                } elseif ($key === 'Safari' && preg_match('/Version\/([0-9\.]+)/', $this->userAgent, $matches)) {
                    $this->browserVersion = $matches[1];
                }
                
                break;
            }
        }
    }
    
    /**
     * Detect the operating system
     */
    private function detectOS() {
        $os = [
            'Windows NT 10.0' => 'Windows 10',
            'Windows NT 6.3' => 'Windows 8.1',
            'Windows NT 6.2' => 'Windows 8',
            'Windows NT 6.1' => 'Windows 7',
            'Windows NT 6.0' => 'Windows Vista',
            'Windows NT 5.1' => 'Windows XP',
            'Windows NT 5.0' => 'Windows 2000',
            'Mac OS X' => 'macOS',
            'Macintosh' => 'Mac',
            'Android' => 'Android',
            'Linux' => 'Linux',
            'iPhone OS' => 'iOS',
            'iPad; CPU OS' => 'iPadOS',
            'Ubuntu' => 'Ubuntu',
            'CrOS' => 'Chrome OS'
        ];
        
        $this->os = 'Unknown';
        $this->osVersion = '';
        
        foreach ($os as $key => $value) {
            if (strpos($this->userAgent, $key) !== false) {
                $this->os = $value;
                
                // Extract version
                if ($value === 'Android' && preg_match('/Android\s+([0-9\.]+)/', $this->userAgent, $matches)) {
                    $this->osVersion = $matches[1];
                } elseif ($value === 'iOS' && preg_match('/iPhone OS\s+([0-9_]+)/', $this->userAgent, $matches)) {
                    $this->osVersion = str_replace('_', '.', $matches[1]);
                } elseif ($value === 'iPadOS' && preg_match('/CPU OS\s+([0-9_]+)/', $this->userAgent, $matches)) {
                    $this->osVersion = str_replace('_', '.', $matches[1]);
                } elseif ($value === 'macOS' && preg_match('/Mac OS X\s+([0-9_\.]+)/', $this->userAgent, $matches)) {
                    $this->osVersion = str_replace('_', '.', $matches[1]);
                } elseif (strpos($value, 'Windows') !== false && preg_match('/Windows NT\s+([0-9\.]+)/', $this->userAgent, $matches)) {
                    $this->osVersion = $matches[1];
                }
                
                break;
            }
        }
    }
    
    /**
     * Set JavaScript to detect screen properties and reload the page
     */
    private function setJavaScriptDetection() {
        // Get the current URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $query = $_GET;
        
        // Add no_reload parameter to prevent infinite loops
        $query['no_reload'] = '1';
        $queryString = http_build_query($query);
        $redirectUrl = $protocol . '://' . $host . $uri . '?' . $queryString;
        
        // Set cookie expiration time
        $expiry = time() + $this->cookieExpiration;
        
        // Output JavaScript to set cookies and reload
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Detecting Device Properties</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; text-align: center; }
                .loader { border: 5px solid #f3f3f3; border-radius: 50%; border-top: 5px solid #3498db; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto; }
                @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            </style>
        </head>
        <body>
            <h2>Detecting your device properties...</h2>
            <div class='loader'></div>
            <p>Please wait a moment while we optimize the site for your device.</p>
            
            <script>
                // Set cookies with screen properties
                document.cookie = 'screen_width=' + screen.width + '; path=/; max-age=" . $this->cookieExpiration . "';
                document.cookie = 'screen_height=' + screen.height + '; path=/; max-age=" . $this->cookieExpiration . "';
                document.cookie = 'orientation=' + (screen.width > screen.height ? 'landscape' : 'portrait') + '; path=/; max-age=" . $this->cookieExpiration . "';
                document.cookie = 'device_detected=1; path=/; max-age=" . $this->cookieExpiration . "';
                
                // Detect if device has touch capability
                document.cookie = 'has_touch=' + ('ontouchstart' in window || navigator.maxTouchPoints > 0) + '; path=/; max-age=" . $this->cookieExpiration . "';
                
                // Reload with the redirect URL
                window.location.href = '" . $redirectUrl . "';
            </script>
        </body>
        </html>";
    }
    
    /**
     * Detect screen properties (width, height, orientation)
     */
    private function detectScreenProperties() {
        // Get values from cookies if available
        if (isset($_COOKIE['screen_width'])) {
            $this->screenWidth = intval($_COOKIE['screen_width']);
        } else {
            $this->screenWidth = 0;
        }
        
        if (isset($_COOKIE['screen_height'])) {
            $this->screenHeight = intval($_COOKIE['screen_height']);
        } else {
            $this->screenHeight = 0;
        }
        
        if (isset($_COOKIE['orientation'])) {
            $this->orientation = $_COOKIE['orientation'];
        } else {
            // Guess orientation based on user agent
            if ($this->isMobile) {
                $this->orientation = 'portrait'; // Most mobile devices default to portrait
            } elseif ($this->isTablet) {
                $this->orientation = 'landscape'; // Tablets can go either way
            } else {
                $this->orientation = 'landscape'; // Desktops are typically landscape
            }
        }
    }
    
    /**
     * Set default custom variables based on device detection
     */
    private function setDefaultCustomVars() {
        // Set default font size based on device type
        if ($this->isMobile) {
            $this->setCustomVar('fontSize', '14px');
            $this->setCustomVar('buttonSize', 'large');
            $this->setCustomVar('navigationStyle', 'hamburger');
        } elseif ($this->isTablet) {
            $this->setCustomVar('fontSize', '16px');
            $this->setCustomVar('buttonSize', 'medium');
            $this->setCustomVar('navigationStyle', 'compact');
        } else {
            $this->setCustomVar('fontSize', '16px');
            $this->setCustomVar('buttonSize', 'normal');
            $this->setCustomVar('navigationStyle', 'full');
        }
        
        // Set image quality based on device and screen size
        if ($this->screenWidth > 1920) {
            $this->setCustomVar('imageQuality', 'high');
        } elseif ($this->screenWidth > 1280) {
            $this->setCustomVar('imageQuality', 'medium');
        } else {
            $this->setCustomVar('imageQuality', 'low');
        }
        
        // Set layout type based on orientation
        $this->setCustomVar('layout', $this->orientation);
        
        // Set touch support flag
        $hasTouch = isset($_COOKIE['has_touch']) ? ($_COOKIE['has_touch'] === 'true') : ($this->isMobile || $this->isTablet);
        $this->setCustomVar('hasTouch', $hasTouch);
    }
    
    /**
     * Get the device type
     * 
     * @return string ('mobile', 'tablet', 'desktop', or 'bot')
     */
    public function getDeviceType() {
        return $this->deviceType;
    }
    
    /**
     * Get the browser name
     * 
     * @return string
     */
    public function getBrowser() {
        return $this->browser;
    }
    
    /**
     * Get the browser version
     * 
     * @return string
     */
    public function getBrowserVersion() {
        return $this->browserVersion;
    }
    
    /**
     * Get the operating system
     * 
     * @return string
     */
    public function getOS() {
        return $this->os;
    }
    
    /**
     * Get the operating system version
     * 
     * @return string
     */
    public function getOSVersion() {
        return $this->osVersion;
    }
    
    /**
     * Check if the device is mobile
     * 
     * @return bool
     */
    public function isMobile() {
        return $this->isMobile;
    }
    
    /**
     * Check if the device is a tablet
     * 
     * @return bool
     */
    public function isTablet() {
        return $this->isTablet;
    }
    
    /**
     * Check if the device is a desktop computer
     * 
     * @return bool
     */
    public function isDesktop() {
        return $this->isDesktop;
    }
    
    /**
     * Check if the user agent is a bot
     * 
     * @return bool
     */
    public function isBot() {
        return $this->isBot;
    }
    
    /**
     * Get the screen width in pixels
     * 
     * @return int
     */
    public function getScreenWidth() {
        return $this->screenWidth;
    }
    
    /**
     * Get the screen height in pixels
     * 
     * @return int
     */
    public function getScreenHeight() {
        return $this->screenHeight;
    }
    
    /**
     * Get the screen orientation ('portrait' or 'landscape')
     * 
     * @return string
     */
    public function getOrientation() {
        return $this->orientation;
    }
    
    /**
     * Set a custom variable
     * 
     * @param string $name Variable name
     * @param mixed $value Variable value
     * @return DeviceDetector
     */
    public function setCustomVar($name, $value) {
        $this->customVars[$name] = $value;
        return $this;
    }
    
    /**
     * Get a custom variable
     * 
     * @param string $name Variable name
     * @param mixed $default Default value if variable doesn't exist
     * @return mixed
     */
    public function getCustomVar($name, $default = null) {
        return isset($this->customVars[$name]) ? $this->customVars[$name] : $default;
    }
    
    /**
     * Get all custom variables
     * 
     * @return array
     */
    public function getAllCustomVars() {
        return $this->customVars;
    }
    
    /**
     * Check if the device resolution matches a specific breakpoint range
     * 
     * @param int $minWidth Minimum width
     * @param int $maxWidth Maximum width
     * @return bool
     */
    public function isBreakpoint($minWidth, $maxWidth = null) {
        if ($maxWidth === null) {
            return $this->screenWidth >= $minWidth;
        }
        return $this->screenWidth >= $minWidth && $this->screenWidth <= $maxWidth;
    }
    
    /**
     * Get responsive class names based on screen size
     * 
     * @return string
     */
    public function getResponsiveClasses() {
        $classes = [];
        
        // Add device type class
        $classes[] = 'device-' . $this->deviceType;
        
        // Add orientation class
        $classes[] = 'orientation-' . $this->orientation;
        
        // Add breakpoint classes
        if ($this->screenWidth < 576) {
            $classes[] = 'breakpoint-xs';
        } elseif ($this->screenWidth < 768) {
            $classes[] = 'breakpoint-sm';
        } elseif ($this->screenWidth < 992) {
            $classes[] = 'breakpoint-md';
        } elseif ($this->screenWidth < 1200) {
            $classes[] = 'breakpoint-lg';
        } else {
            $classes[] = 'breakpoint-xl';
        }
        
        return implode(' ', $classes);
    }
    
    /**
     * Get the full user agent string
     * 
     * @return string
     */
    public function getUserAgent() {
        return $this->userAgent;
    }
    
    /**
     * Check if the device is in a specific resolution range
     * 
     * @param string $range One of 'xs', 'sm', 'md', 'lg', 'xl'
     * @return bool
     */
    public function isResolutionRange($range) {
        switch ($range) {
            case 'xs':
                return $this->screenWidth < 576;
            case 'sm':
                return $this->screenWidth >= 576 && $this->screenWidth < 768;
            case 'md':
                return $this->screenWidth >= 768 && $this->screenWidth < 992;
            case 'lg':
                return $this->screenWidth >= 992 && $this->screenWidth < 1200;
            case 'xl':
                return $this->screenWidth >= 1200;
            default:
                return false;
        }
    }
    
    /**
     * Generate a JSON representation of device information
     * 
     * @return string JSON string
     */
    public function toJson() {
        $data = [
            'deviceType' => $this->deviceType,
            'browser' => $this->browser,
            'browserVersion' => $this->browserVersion,
            'os' => $this->os,
            'osVersion' => $this->osVersion,
            'isMobile' => $this->isMobile,
            'isTablet' => $this->isTablet,
            'isDesktop' => $this->isDesktop,
            'isBot' => $this->isBot,
            'screenWidth' => $this->screenWidth,
            'screenHeight' => $this->screenHeight,
            'orientation' => $this->orientation,
            'customVars' => $this->customVars
        ];
        
        return json_encode($data);
    }
    
    /**
     * Print debug information about the detected device
     * 
     * @param bool $asHtml Whether to output as HTML (true) or plain text (false)
     * @return string
     */
    public function debug($asHtml = true) {
        $info = [
            'Device Type' => $this->deviceType,
            'Browser' => $this->browser . ' ' . $this->browserVersion,
            'Operating System' => $this->os . ' ' . $this->osVersion,
            'Is Mobile' => $this->isMobile ? 'Yes' : 'No',
            'Is Tablet' => $this->isTablet ? 'Yes' : 'No',
            'Is Desktop' => $this->isDesktop ? 'Yes' : 'No',
            'Is Bot' => $this->isBot ? 'Yes' : 'No',
            'Screen Resolution' => $this->screenWidth . ' x ' . $this->screenHeight,
            'Orientation' => $this->orientation,
            'Custom Variables' => print_r($this->customVars, true)
        ];
        
        if ($asHtml) {
            $output = '<div class="device-debug">';
            $output .= '<h3>Device Detection Results</h3>';
            $output .= '<table border="1" cellpadding="5" cellspacing="0">';
            foreach ($info as $key => $value) {
                $output .= '<tr><th>' . htmlspecialchars($key) . '</th><td>' . htmlspecialchars($value) . '</td></tr>';
            }
            $output .= '</table>';
            $output .= '</div>';
        } else {
            $output = "===== Device Detection Results =====\n";
            foreach ($info as $key => $value) {
                $output .= $key . ": " . $value . "\n";
            }
        }
        
        return $output;
    }
    
    /**
     * Set the cookie expiration time
     * 
     * @param int $seconds Number of seconds before cookies expire
     * @return DeviceDetector
     */
    public function setCookieExpiration($seconds) {
        $this->cookieExpiration = $seconds;
        return $this;
    }
    
    /**
     * Check if device information has been detected
     * 
     * @return bool
     */
    public function isDetected() {
        return isset($_COOKIE['device_detected']) && $_COOKIE['device_detected'] == '1';
    }
    
    /**
     * Force a refresh of the detection cookies
     * 
     * @return void
     */
    public function refreshDetection() {
        // Clear detection cookies
        setcookie('device_detected', '', time() - 3600, '/');
        setcookie('screen_width', '', time() - 3600, '/');
        setcookie('screen_height', '', time() - 3600, '/');
        setcookie('orientation', '', time() - 3600, '/');
        setcookie('has_touch', '', time() - 3600, '/');
        
        // Get current URL without no_reload parameter
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        
        // Redirect to current page
        header('Location: ' . $protocol . '://' . $host . $uri);
        exit;
    }
}

/**
 * Usage Example:
 * 
 * // Initialize detector with auto-reload enabled (default)
 * $detector = new DeviceDetector();
 * 
 * // Or disable auto-reload if you prefer
 * // $detector = new DeviceDetector(false);
 * 
 * if ($detector->isMobile()) {
 *     // Do something for mobile devices
 * } elseif ($detector->isTablet()) {
 *     // Do something for tablets
 * } else {
 *     // Do something for desktops
 * }
 * 
 * // Get screen size
 * $width = $detector->getScreenWidth();
 * $height = $detector->getScreenHeight();
 * 
 * // Check orientation
 * if ($detector->getOrientation() === 'portrait') {
 *     // Portrait-specific layout
 * }
 * 
 * // Force refresh detection (useful if user rotates device, etc.)
 * // This will reload the page automatically
 * if (isset($_GET['refresh'])) {
 *     $detector->refreshDetection();
 * }
 * 
 * // Output debug info
 * echo $detector->debug();
 */
?>
