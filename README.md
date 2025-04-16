# 📱 PHP Device Detector

A lightweight PHP utility to detect the type of device visiting your website: **mobile**, **tablet**, or **desktop**. Useful for responsive design, analytics, or conditional content delivery without relying on JavaScript or external libraries.

---

## 🚀 Features

- ✅ Detects mobile, tablet, or desktop devices using the `User-Agent` string
- ✅ Written in plain PHP — no dependencies
- ✅ Fast and easy to integrate into any PHP-based project
- ✅ Supports a wide range of devices and browsers
- ✅ Can be extended with your own patterns

---

## 📦 Installation

Download or clone this repository:

```bash
git clone https://github.com/makalin/DeviceDetector.git
```

Then include the script in your project:

```php
require_once 'device-detector.php';
```

---

## 🛠️ How It Works

The script checks the `$_SERVER['HTTP_USER_AGENT']` value against regular expressions that match known mobile or tablet user agents. If a match is found, it sets the following variables:

- `$mobil = 1;` if the device is a mobile phone
- `$tablet = 1;` if the device is a tablet
- Both remain `0` for desktops

---

## 💡 Usage Example

```php
<?php
include 'device-detector.php';

if ($mobil) {
    echo "📱 You are using a mobile device.";
} elseif ($tablet) {
    echo "📱 You are using a tablet.";
} else {
    echo "💻 You are using a desktop.";
}
?>
```

You can also log or inspect the visitor's user agent:

```php
echo "Your User-Agent: " . $_SERVER['HTTP_USER_AGENT'];
```

---

## 🔍 Notes

- iPads with iOS 13+ may report desktop-like user agents. Additional handling using screen size or JavaScript may be required for 100% accuracy.
- For more advanced detection (e.g. OS, browser type), consider integrating with external libraries such as [MobileDetect](https://github.com/serbanghita/Mobile-Detect).

---

## 📁 File Structure

```
DeviceDetector/
├── device-detector.php     # Core detection logic
├── index.php             # Optional usage example
└── README.md               # Project documentation
```

---

## 🧩 Integration Ideas

- Redirect mobile users to a separate mobile site
- Load mobile-optimized images or UI
- Track analytics by device type
- Disable heavy scripts for mobile users

---

## 🤝 Contributing

Want to improve detection or add new features?
- Fork this repository
- Make your changes
- Open a pull request

Please test your changes on multiple devices before submitting.

---

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.
