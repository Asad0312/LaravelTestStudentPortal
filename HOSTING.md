# StudentHub — Hosting Guide (Laravel 12)

> **Recommendation:** Agar aapko hosting khud karni hai to **cPanel Shared Hosting** sabse asaan hai
> (Hostinger / Bluehost / Namecheap / koi bhi cPanel host). Koi server manage nahi karna,
> File Manager + Terminal se ho jata hai. VPS tab lein jab traffic zyada ho ya full control chahiye.

---

## Part A — cPanel Shared Hosting (Sabse Asaan — Recommended)

### A1. Hosting se pehle cheezein ready karein
1. Domain ya subdomain jahan site lagni hai (e.g. `studenthub.com`).
2. cPanel me **MySQL Databases** se database + user bana lein (naam yaad rakhein).
3. Hosting ka **PHP 8.2** select karein (cPanel → Select PHP Version).

### A2. Project upload karein
Project ka structure: Laravel ka `public/` folder hi website ka root hona chahiye.

1. Local project ko zip karein (`studentportal.zip` — sab folders andar, `node_modules` ki zaroorat nahi).
2. cPanel **File Manager** → `home/` ke andar ek folder banayein: `laravelapp`
3. Zip upload karke us folder me extract karein → `/home/USER/laravelapp/` me `app/, bootstrap/, ...` aa jayenge.
4. `laravelapp/public/*` ki sab files **`public_html/`** me move kar dein.
5. `public_html/index.php` me do lines edit karein (File Manager → Edit):

```php
require __DIR__.'/../laravelapp/vendor/autoload.php';        // purana: __DIR__.'/../vendor/autoload.php'
$app = require_once __DIR__.'/../laravelapp/bootstrap/app.php'; // purana: __DIR__.'/../bootstrap/app.php'
```

> Agar aap `home/USER/` ke neeche hi rakh rahe hain to path hoga:
> `'/home/USER/laravelapp/vendor/autoload.php'` (full path likhna zyada safe hai).

### A3. Composer (vendor folder)
- **Agar host me terminal + composer hai:** cPanel Terminal chalayein:
  ```bash
  cd ~/laravelapp
  composer install --no-dev --optimize-autoloader
  ```
- **Agar nahi hai:** local me `composer install` karein aur pura `vendor/` folder zip karke upload kar dein (vendor uploads me kaafi bara ho sakta hai — split kar lein ya `--no-dev` se chhota karein).

### A4. `.env` setup (production)
`laravelapp/.env` File Manager se edit karein:

```env
APP_NAME=StudentHub
APP_ENV=production
APP_KEY=base64:...            # key kaise banayein: A6 me
APP_DEBUG=false               # IMPORTANT — error public me na dikhein
APP_URL=https://studenthub.com
APP_TIMEZONE=Asia/Karachi        # message expiry / dates sahi chalne ke liye

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=cpanelusername_saportal
DB_USERNAME=cpanelusername_saportal
DB_PASSWORD=********

SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=...                  # A7 me
MAIL_PORT=465
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@studenthub.com
MAIL_FROM_NAME="StudentHub"

ADMIN_EMAIL=admin@studenthub.com
ADMIN_PASSWORD=apnaStrongPassword
ADMIN_NAME=Admin

WHATSAPP_API_URL=              # A8 me (optional abhi)
WHATSAPP_PHONE=92XXXXXXXXXX
```

**APP_KEY banane ke liye** (Terminal me, project folder me):
```bash
php artisan key:generate --show
# output ko .env me APP_KEY= paste karein
```
Ya agar terminal nahi: local project ke `.env` me jo `APP_KEY` hai wahi copy kar dein (agar `php artisan key:generate` local me chala chuka hai).

### A5. Database import + migrate
**Tarika 1 (aasaan):** phpMyAdmin → database select → Import → local `studentportal_laravel` ki SQL file (mysqldump se bana kar) → Go.

**Tarika 2 (artisan):** cPanel Terminal me:
```bash
cd ~/laravelapp
php artisan migrate --force
php artisan db:seed --force     # sirf admin credentials banata hai
```

Admin login: `ADMIN_EMAIL` + `ADMIN_PASSWORD` (`.env` se — DB me hashtabled).

### A6. Permissions
File Manager → `laravelapp/storage` aur `laravelapp/bootstrap/cache` → Properties → Permission **755** (sub-folders ke saath, Recursive).
Agar permission error aaye to **775** try karein.

### A7. SMTP — email alerts chalane ke liye
3 options (koi ek chunein):

1. **Gmail (sabse aasaan):**
   - Google Account → Security → 2-Step Verification ON → "App passwords" se 16-digit password banayein.
   - `.env` me:
     ```env
     MAIL_HOST=smtp.gmail.com
     MAIL_PORT=465
     MAIL_USERNAME=aapka@gmail.com
     MAIL_PASSWORD=16digitAppPassword
     MAIL_ENCRYPTION=ssl
     MAIL_FROM_ADDRESS=aapka@gmail.com
     ```
2. **Hosting ka apna email** (info@domain pe cPanel Email account bana kar): `.env` me hosting ke SMTP details (hosting ke emails me aate hain).
3. **Zoho / Outlook / Yahoo** — same pattern, unke SMTP settings.

Deploy ke baad cache flush:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### A8. WhatsApp alerts (CallMeBot — key `.env` me daalein)
Abhi sirf email enabled hai; WhatsApp key milte hi on ho jayega.

1. Apne number se ye message bhejein (browser se bhi ho jata hai):
   `I allow callmebot to send me messages` → **+34 644 64 10 91** (CallMeBot official).
2. Bot reply me **API key** dega.
3. `.env` me:
   ```env
   WHATSAPP_PHONE=923001234567
   WHATSAPP_API_URL=https://api.callmebot.com/whatsapp.php?phone={WHATSAPP_PHONE}&text={text}&apikey=AAPKA_KEY
   ```
   - `{text}` placeholder **zaroori** hai — app isi se message bhejta hai. Key galat/empty rahe to email theek chalta hai, WhatsApp silently skip ho jata hai (upload kabhi fail nahi hota).

### A9. Cron (agar queue/mail queue use karein)
cPanel → **Cron Jobs** → minute hour day month weekday:
```
* * * * * /usr/local/bin/php /home/USER/laravelapp/artisan schedule:run >> /dev/null 2>&1
```
(Is project me `QUEUE_CONNECTION=sync` hai — cron optional hai.)

### A10. HTTPS
cPanel → **SSL/TLS Status** → domain par **Run AutoSSL** (Let's Encrypt free hosts par).
`.env` me `APP_URL=https://...` (https) rakhein.

---

## Part B — VPS Hosting (Full Control)

> Hostinger VPS / DigitalOcean / Contabo / AWS — koi bhi. Steps Ubuntu 22.04/24.04 ke.

### B1. Server setup (ek baar)
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath unzip git

# Composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# MySQL database
sudo mysql -e "CREATE DATABASE studentportal_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'sp_user'@'localhost' IDENTIFIED BY 'StrongPass!';"
sudo mysql -e "GRANT ALL ON studentportal_laravel.* TO 'sp_user'@'localhost'; FLUSH PRIVILEGES;"
```

### B2. Project upload
```bash
sudo mkdir -p /var/www/studentportal
# local se upload (project folder ke andar):
scp -r ./* user@SERVER:/tmp/project/
sudo cp -r /tmp/project/* /var/www/studentportal/
cd /var/www/studentportal
composer install --no-dev --optimize-autoloader
cp .env.example .env   # ya local ka .env (key ke saath)
php artisan key:generate --force     # agar APP_KEY nahi hai
php artisan migrate --force && php artisan db:seed --force
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### B3. Nginx config
`/etc/nginx/sites-available/studentportal`:
```nginx
server {
    listen 80;
    server_name studenthub.com www.studenthub.com;
    root /var/www/studentportal/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```
```bash
sudo ln -s /etc/nginx/sites-available/studentportal /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### B4. HTTPS + cron
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d studenthub.com -d www.studenthub.com

(crontab -l; echo "* * * * * php /var/www/studentportal/artisan schedule:run > /dev/null 2>&1") | crontab -
```

### B5. Firewall
```bash
sudo ufw allow OpenSSH && sudo ufw allow 'Nginx Full' && sudo ufw enable
```

---

## Deploy ke baad checklist (dono hosting par)

- [ ] `/login` khul raha hai, admin se login ho raha hai
- [ ] Admin panel → Admissions → **New Admission** form bhar kar student ban raha hai (ID + password flash me)
- [ ] Admin panel → naya test add karo → students ke portal par dikhe
- [ ] Email a rahi hai (`.env` ka `MAIL_*` sahi? → `storage/logs/laravel.log` check karein)
- [ ] Student ID + password se `/portal` login — email se login **nahi** hota
- [ ] Student session me `/admin` open karke dekhein → `/portal` par redirect hona chahiye
- [ ] `APP_DEBUG=false` aur `.env` file web se reachable NA ho (`public_html` me `.env` nahi hai — sirf `laravelapp/` me hai)

## Local development (XAMPP ke alawa)
```bash
php artisan serve          # http://localhost:8000
```
MySQL: XAMPP se `mysql` on rakhein, DB `studentportal_laravel`, `php artisan migrate:fresh --seed`.
