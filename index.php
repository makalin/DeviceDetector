<?php 

include 'device-detector.php';

$detector = new DeviceDetector();

if ($detector->isMobile()) {
    // Do something for mobile devices
} elseif ($detector->isTablet()) {
    // Do something for tablets
} else {
    // Do something for desktops
}

// Get screen size
$width = $detector->getScreenWidth();
$height = $detector->getScreenHeight();

// Check orientation
if ($detector->getOrientation() === 'portrait') {
    // Portrait-specific layout
}

// Use custom variables
$fontSize = $detector->getCustomVar('fontSize');

// Add custom variables
$detector->setCustomVar('theme', 'dark');

// Output debug info
echo $detector->debug();
