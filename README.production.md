# Kagomori Production Deployment Guide

## Build Process

1. **Build Frontend:**

   ```bash
   cd frontend
   npm install
   npm run build:prod
   ```

2. **Deployment Structure:**

   ```
   Production Server:
   /var/www/html/kagomori/
   ├── index.html (from frontend/dist/)
   ├── assets/ (from frontend/dist/assets/)
   ├── sinto-logo.svg (copy from frontend/public/)
   ├── .htaccess (from frontend/public/)
   └── backend/
       ├── get_timesheet.php
       ├── config.production.php (rename to config.php)
       └── test_connection.php
   ```

3. **Server Requirements:**

   - Apache with mod_rewrite enabled
   - PHP 8.1+ with SQL Server drivers (sqlsrv, pdo_sqlsrv)
   - Access to SQL Server database

4. **Environment Setup:**

   - Update database credentials in config.php
   - Ensure proper file permissions (755 for directories, 644 for files)
   - Test API endpoint: http://yourserver.com/kagomori/backend/test_connection.php

5. **Access URLs:**
   - Application: http://yourserver.com/kagomori/
   - API: http://yourserver.com/kagomori/backend/

## Security Checklist

- [ ] Database credentials secured
- [ ] Error reporting disabled in production
- [ ] HTTPS enabled
- [ ] File permissions set correctly
- [ ] Backup strategy in place
