خطوات الرفع على cPanel:
1) MySQL Databases: اعمل Database + User وادّيله All Privileges.
2) عدّل config.php بالبيانات دي.
3) File Manager: ارفع محتوى المجلد ده كله في public_html (أو extract الـ zip).
4) افتح yourdomain.com/install.php واختار باسورد للأدمن.
5) امسح install.php.
6) ادخل yourdomain.com/admin (يفضل PHP 8.1+ من Select PHP Version، وتأكد إن curl و pdo_mysql مفعّلين).
