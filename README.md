# Inventory Managment System [Stock]

> Perform physical inventory using a mobile device with a QR code or barcode scanner, 
>take photos, and enter data. All data is saved in a MySQL database via a PHP API.



## Features
- Multiple users with role-based permissions (user, admin, superadmin).
- No duplicate products: Before inserting a new product, the system checks if it already exists; if it does, the inventory is simply updated (IN/OUT).
- Security: After 5 invalid login attempts, the IP address is added to the blacklist.
- All forms are protected against CSRF (Cross-site Request Forgery).
- User actions are fully logged.
- Directory protection using .htaccess.

## Installation

- Requirements: PHP 8, Apache server, MySQL 8.
- Delete the config.php file from admin/base/config.php.
- In your browser, open https://your-server/folder-of-application/setup/.
- Enter your information, and you're ready to go.
- Download Android app apk from login page set api url example https://alhashim.dev/stock/
- login from the app the items you scan and you saved will be in your server 
- android used retrofit & google ML for Scanning Barcode QR code
