# Google Authentication Setup Guide

This guide will help you set up Google OAuth authentication for your website.

## Step 1: Create a Google Cloud Project

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Navigate to "APIs & Services" > "OAuth consent screen"
4. Choose "External" user type and click "Create"
5. Fill in the required information:
   - App name
   - User support email
   - Developer contact information
6. Click "Save and Continue"
7. For scopes, add the following:
   - `userinfo.email`
   - `userinfo.profile`
8. Click "Save and Continue"
9. Add any test users if you're in testing mode, then click "Save and Continue"

## Step 2: Create OAuth Client ID

1. In the Google Cloud Console, go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" and select "OAuth client ID"
3. Select "Web application" as the application type
4. Give your client a name (e.g., "My Website Authentication")
5. Add authorized JavaScript origins:
   - `https://yourdomain.com` (or your actual domain)
6. Add authorized redirect URIs:
   - `https://yourdomain.com/google_auth.php` (or your actual redirect path)
7. Click "Create"
8. Copy your Client ID and Client Secret

## Step 3: Configure Your Website

1. Open the `config.php` file in your website root directory
2. Update the Google OAuth configuration section:
   ```php
   'google' => [
       'client_id' => 'YOUR_CLIENT_ID',  // Paste your client ID here
       'client_secret' => 'YOUR_CLIENT_SECRET',  // Paste your client secret here
       'redirect_uri' => 'https://yourdomain.com/google_auth.php'  // Update with your domain
   ]
   ```

## Step 4: Install Dependencies

Run the following command in your project root directory to install the required PHP Google API client library:

```bash
composer update
```

## Step 5: Database Migration

Execute the SQL query in `add_google_id_migration.sql` to add the Google ID column to your users table:

```sql
-- Add google_id column to users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) UNIQUE;

-- Create an index for faster lookups
CREATE INDEX IF NOT EXISTS idx_users_google_id ON users(google_id);
```

## Step 6: Test Google Authentication

1. Visit your website's login page
2. Click on the "Continue with Google" button
3. Follow the Google authentication process
4. After successful authentication, you should be redirected to your website's dashboard

## Security Considerations

- Always keep your Client Secret confidential
- Use HTTPS for all OAuth redirects
- Regularly review your Google Cloud Console for any suspicious activities
- Set appropriate scopes to request only the information you need 