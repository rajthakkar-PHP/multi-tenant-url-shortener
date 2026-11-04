# Company URL Shortener

A multi-company URL shortener built with Laravel 12, PHP 8.2+, and MySQL. Each short URL belongs to both a company and its creator; authorization and list queries enforce those boundaries on the server.

## Features

- Session-based login, logout, CSRF protection, session ID rotation, and throttling after repeated failed logins.
- Company management for SuperAdmin, with company-scoped visibility for Admins.
- Dedicated User Management page: SuperAdmin sees all companies, Admin sees only its company, and unconfigured roles are denied.
- Email invitations with one-time random tokens, SHA-256 token hashes in the database, seven-day expiry, and transactional acceptance.
- Secure random eight-character alphanumeric short codes with collision retries.
- Public HTTP 302 redirects; only HTTP and HTTPS destinations are accepted.
- Paginated short URL lists with role-based access: SuperAdmin sees all, Admin sees the company, Member sees their own URLs.
- Role-aware dashboards and safe 403, 404, 410, and 500 pages.
- Sales and Manager have no permissions by default. Add permissions deliberately before enabling features for those roles.

## Roles

| Role | Company scope | Short URLs | Invitations |
| --- | --- | --- | --- |
| SuperAdmin | All companies | View all; cannot create | Invite Admin to a company |
| Admin | Own company | Create and view company URLs | Invite Admin or Member to own company |
| Member | Own company | Create and view own URLs | Not permitted |
| Sales | Not configured | Not permitted by default | Not permitted by default |
| Manager | Not configured | Not permitted by default | Not permitted by default |

## Requirements

- Windows with XAMPP, or another PHP/MySQL environment.
- PHP 8.2 or newer with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`, and `json` enabled.
- Composer 2.
- MySQL or MariaDB. The tested local setup uses XAMPP MariaDB on port 3306.
- Node.js is not required to run the current pages; Bootstrap is loaded from its CDN. Node/npm is only needed if you add Vite-managed assets.

## Technologies

- Laravel 12 and PHP 8.2+
- MySQL/MariaDB with Eloquent ORM
- Laravel Blade, Bootstrap 5, HTML, and CSS
- PHPUnit through Laravel's `php artisan test` command
- Laravel mail logging for local invitation testing

## XAMPP Database Setup

1. Start **MySQL** in the XAMPP Control Panel. Apache is optional when using `php artisan serve`.
2. Open `http://localhost/phpmyadmin`.
3. Choose **New**, create a database named `company_url_shortener`, and select the `utf8mb4_unicode_ci` collation.
4. The example configuration assumes the local XAMPP defaults: host `127.0.0.1`, port `3306`, user `root`, and an empty database password. Adjust these values if your MySQL installation differs.

## Install and Configure

Run these commands from the project directory in PowerShell:

```powershell
cd C:\xampp\htdocs\sembark-url-shortener
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

Open the local `.env` file and configure the application. Do not put credentials in `.env.example`.

```dotenv
APP_NAME="Company URL Shortener"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=company_url_shortener
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
SUPERADMIN_EMAIL=you@example.com
SUPERADMIN_PASSWORD=choose-a-unique-password-of-at-least-12-characters
```

Set a private, unique SuperAdmin password of at least 12 characters. Do not send it in chat, add it to source control, or reuse a password that has been shared elsewhere. `.env` is excluded by `.gitignore`.

Generate the application key, clear any stale cached configuration, apply migrations, and seed roles plus the configured SuperAdmin:

```powershell
php artisan key:generate
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
```

`DatabaseSeeder` runs `RoleSeeder` followed by `SuperAdminSeeder`. The SuperAdmin seeder uses parameterized raw SQL. If the SuperAdmin environment values are missing or invalid, it warns and skips account creation; it does not install a default password.

To seed roles only, run `php artisan db:seed --class=RoleSeeder --force`. Never use `migrate:fresh` on a database with data; it drops existing tables.

### Default Development Account

There is deliberately no shared or hard-coded default account. Set `SUPERADMIN_EMAIL` and a private password of at least 12 characters in your ignored local `.env`, then run `php artisan db:seed --class=SuperAdminSeeder --force`. The values in `.env.example` are blank placeholders. If a password has been exposed, replace it before using the account.

## Run Locally

The recommended no-virtual-host setup uses Artisan:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Keep that terminal open and visit `http://127.0.0.1:8000`. Sign in at `/login` with the email and password configured in your local `.env`. Stop the server with Ctrl+C in that terminal.

### Optional: XAMPP Apache

Laravel's Apache document root must be the project's `public` directory, not the repository root. The Artisan server above works without Apache or a virtual host. To configure one in XAMPP:

1. In `C:\xampp\apache\conf\httpd.conf`, ensure `mod_rewrite` is loaded and `Include conf/extra/httpd-vhosts.conf` is enabled.
2. Add this to `C:\xampp\apache\conf\extra\httpd-vhosts.conf` (adjust the project path if needed):

```apache
<VirtualHost *:80>
    ServerName company-url-shortener.test
    DocumentRoot "C:/xampp/htdocs/sembark-url-shortener/public"
    <Directory "C:/xampp/htdocs/sembark-url-shortener/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Add `127.0.0.1 company-url-shortener.test` to `C:\Windows\System32\drivers\etc\hosts` using an editor with administrator permission.
4. Set `APP_URL=http://company-url-shortener.test` in `.env` and restart Apache.

Laravel's `public/.htaccess` handles application routes through `mod_rewrite`. A virtual host is not needed for the Artisan setup above.

## Invitations

Set `MAIL_MAILER=log` for local testing. After an authorized user sends an invitation, its acceptance URL is written to `storage/logs/laravel.log`. Open the URL as the invitee, enter the invited email address, and set a password. Tokens expire after seven days and cannot be reused. Only the token hash is stored in MySQL.

## Tests and Formatting

```powershell
php artisan test
vendor\bin\pint --test
```

The PHPUnit suite uses an isolated in-memory SQLite database so tests do not alter your XAMPP database. The application itself is configured for MySQL. MySQL migration status can be inspected with `php artisan migrate:status`.

Feature tests cover role logins, company and URL access isolation, invitation authorization and acceptance, token hashing and replay prevention, HTTP 302 redirects, invalid codes, unsafe schemes, collision retries, pagination, and role-aware dashboards.

## GitHub and Production Safety

- Commit `.env.example`, never `.env` or any file containing real credentials.
- Keep SuperAdmin credentials unique and private; there are no committed default credentials.
- Before deployment, set `APP_ENV=production`, `APP_DEBUG=false`, use HTTPS, use a least-privilege database account, configure a real mail transport, and rotate any credentials exposed during development.
- Review `git status` and staged files before publishing. Do not commit logs, database dumps, generated secrets, or local build output.

## AI Tool Disclosure

GitHub Copilot in VS Code Agent Mode was used to assist with environment inspection, implementation, debugging, feature-test creation and verification, and documentation. This disclosure is included to comply with the assignment's AI-use policy.

## License

This project is provided for the technical assignment. Laravel and its dependencies remain under their respective licenses.
