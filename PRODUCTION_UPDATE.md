# Production Update Guide - Pull Changes from GitHub

## Quick Commands for Production Server

### If you have SSH access:

```bash
# 1. Navigate to your application directory
cd ~/midnightpilgrim  # or wherever your app is located

# 2. Pull latest changes from GitHub
git pull origin main

# 3. Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 4. Cache configurations for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Run any new migrations
php artisan migrate --force

# 6. Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/framework

# 7. Rebuild conversation cache (if using conversation features)
php artisan conversation:rebuild-cache
```

### If using cPanel Terminal:

1. Log into cPanel
2. Open "Terminal" tool
3. Run the commands above

### If you DON'T have SSH/Terminal access:

You'll need to use FTP to update files manually:
1. Download changed files from GitHub
2. Upload them via FTP to your server
3. Use cPanel File Manager to clear cache files in:
   - `storage/framework/cache/*`
   - `storage/framework/views/*`
   - `bootstrap/cache/*` (except .gitkeep files)

## What Changed in This Update

This update includes:
- ✅ Full conversation system implementation
- ✅ Auto-quote generation (at least 1 quote per note)
- ✅ Improved error handling in write form
- ✅ Simplified landing page
- ✅ Fixed write page behavior
- ✅ New migrations (4 database tables)
- ✅ Better .gitignore configuration

## Post-Update Verification

After pulling changes, test these URLs:
- Homepage: `https://yourdomain.com`
- Write: `https://yourdomain.com/write`
- Read: `https://yourdomain.com/read`
- Conversation: `https://yourdomain.com/talk`

## Troubleshooting

### "Site showing old content"
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### "Database errors"
```bash
# Check if new migrations need to run
php artisan migrate:status

# Run pending migrations
php artisan migrate --force
```

### "500 errors after update"
```bash
# Check permissions
chmod -R 755 storage bootstrap/cache

# Check logs
tail -50 storage/logs/laravel.log
```

## One-Line Update Command

If everything is working and you just want to update quickly:

```bash
cd ~/midnightpilgrim && git pull origin main && php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && php artisan conversation:rebuild-cache
```

## Rollback (if needed)

If something breaks after the update:

```bash
# See recent commits
git log --oneline -5

# Rollback to previous commit (replace COMMIT_HASH with actual hash)
git reset --hard COMMIT_HASH

# Clear caches
php artisan config:clear && php artisan route:clear && php artisan view:clear
```

---

**Generated:** $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
