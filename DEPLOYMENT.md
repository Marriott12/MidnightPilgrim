# Deployment Guide - Shared Hosting (cPanel/FTP)

## Prerequisites
- PHP 8.2+ enabled on hosting
- SQLite support enabled
- SSH access (recommended) or FTP access
- Domain pointed to hosting

## Step 1: Prepare Production Environment File

Create `.env.production` locally with these settings:

```env
APP_NAME=MidnightPilgrim
APP_ENV=production
APP_KEY=base64:YOUR_PRODUCTION_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=/home/username/midnightpilgrim_db/database.sqlite

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=525600

CACHE_STORE=file

# Optional: OpenAI API for conversation features
OPENAI_API_KEY=your_openai_key_here
```

**Important:** Generate a new production APP_KEY:
```bash
php artisan key:generate --show
```

## Step 2: Upload Files via FTP

### Files to Upload:
```
/app/
/bootstrap/
/config/
/database/migrations/
/public/
/resources/
/routes/
/storage/framework/cache/.gitkeep
/storage/framework/sessions/.gitkeep
/storage/framework/views/.gitkeep
/storage/logs/.gitkeep
/vendor/
.htaccess (in public/)
artisan
composer.json
composer.lock
```

### Files to EXCLUDE:
```
/.git/
/.env (upload .env.production as .env instead)
/node_modules/
/storage/logs/*
/storage/framework/cache/*
/storage/framework/sessions/*
/storage/framework/views/*
/database/database.sqlite
/tests/
```

## Step 3: File Structure on Server

Your hosting file structure should look like:
```
/home/username/
├── public_html/ (or www/)
│   └── (point this to 'public' folder)
├── midnightpilgrim/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── artisan
└── midnightpilgrim_db/
    └── database.sqlite
```

## Step 4: Configure cPanel

### A. Set Document Root
In cPanel → Domains → Your Domain:
- Set document root to: `/home/username/midnightpilgrim/public`

### B. Set PHP Version
In cPanel → Select PHP Version:
- Select PHP 8.2 or higher
- Enable required extensions:
  - pdo_sqlite
  - mbstring
  - openssl
  - tokenizer
  - xml
  - ctype
  - json
  - bcmath

## Step 5: SSH Commands (if available)

If you have SSH access:

```bash
# Navigate to your directory
cd ~/midnightpilgrim

# Set permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/framework

# Create database directory
mkdir -p ~/midnightpilgrim_db
touch ~/midnightpilgrim_db/database.sqlite
chmod 664 ~/midnightpilgrim_db/database.sqlite

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate short lines cache for conversation system
php artisan conversation:rebuild-cache
```

## Step 6: Alternative - Manual Setup (FTP Only)

If you don't have SSH:

1. Upload all files via FTP
2. Rename `.env.production` to `.env`
3. Create `database.sqlite` file in separate directory outside public_html
4. Use cPanel File Manager to:
   - Set permissions on `storage/` and `bootstrap/cache/` to 755
   - Create empty `database.sqlite` with 664 permissions
5. Access `https://yourdomain.com` in browser

## Step 7: Post-Deployment Verification

Visit these URLs to verify:
- Homepage: `https://yourdomain.com`
- Write: `https://yourdomain.com/write`
- Read: `https://yourdomain.com/read`
- Conversation: `https://yourdomain.com/talk`

## Troubleshooting

### "500 Internal Server Error"
- Check storage/ permissions (must be writable)
- Verify .env file exists and is readable
- Check error_log in cPanel

### "Database error"
- Verify database.sqlite path in .env
- Check file permissions on database.sqlite
- Ensure directory containing database is writable

### "Class not found" errors
- Run `composer install --no-dev --optimize-autoloader` locally
- Re-upload vendor/ directory
- Clear caches via cPanel Terminal

### Static assets not loading
- Verify document root points to `public/` folder
- Check .htaccess exists in public/
- Enable mod_rewrite in cPanel

## Security Checklist

✅ APP_DEBUG=false in production  
✅ APP_ENV=production  
✅ Strong APP_KEY generated  
✅ Database file outside public_html  
✅ .env file not accessible via web  
✅ storage/ and bootstrap/cache/ writable but not executable  
✅ HTTPS enabled (SSL certificate)  
✅ Regular backups of storage/app/private/vault/

## Maintenance

### Updating the Application
1. Pull latest changes locally
2. Test locally
3. Upload changed files via FTP
4. Clear caches (SSH or cPanel Terminal):
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Backup Strategy
Regular backups should include:
- `/storage/app/private/vault/` - All markdown notes
- `/database/database.sqlite` - Database
- `.env` - Configuration

## Support

For issues, check:
- Laravel logs: `storage/logs/laravel.log`
- cPanel error logs
- PHP error logs in cPanel

---

**Remember:** Midnight Pilgrim is local-first by design. Consider whether public hosting aligns with the privacy philosophy. For personal use, local installation is recommended.
