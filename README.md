# ByteRox Cybersecurity Website

A modern website for a cybersecurity company with user authentication using Supabase.

## Setup Instructions

### 1. Set Up Local Server
- Install XAMPP, WAMP, or MAMP based on your operating system
- Clone or extract this project into your web server's document root folder
- Start your Apache and MySQL services

### 2. Supabase Database Setup

#### Option A: Set Up Supabase Project
1. Create a free account at [Supabase](https://supabase.com)
2. Create a new project
3. Go to the SQL Editor in your Supabase dashboard
4. Run the SQL commands from `database_setup.sql` to create the required tables
5. In the Supabase dashboard, go to Settings > Database to get your connection details
6. Update the database connection details in `config.php` with your Supabase credentials

#### Important Note About UUIDs
The database schema uses UUID primary keys (`id` column) to match Supabase Auth's UUID format. This avoids the following error:
```
ERROR: 42883: operator does not exist: uuid = integer
HINT: No operator matches the given name and argument types. You might need to add explicit type casts.
```

The error occurs when trying to compare a UUID value (`auth.uid()`) with an integer value (SERIAL). Our solution uses UUIDs for the primary key, which makes it compatible with Supabase's auth system.

If you don't want to use UUIDs, the database_setup.sql file includes commented alternative approaches.

#### Option B: Use Local Database Instead (Alternative)
If you prefer to use a local database instead of Supabase:
1. Edit `config.php` to use your local database settings
2. Import the `database_setup.sql` into your local database (modify as needed for MySQL)
3. If using MySQL, you may need to modify the UUID generation code in signup.php

### 3. Run the Website
- Access the website through your browser at: `http://localhost/your-folder-name/`
- You can access specific pages:
  - Home: `index.php`
  - About Us: `aboutus.php`
  - Contact: `contact.php`
  - Login: `login.php`
  - Signup: `signup.php`

## Features
- Modern responsive design with video backgrounds
- User registration and login system using Supabase
- UUID-based user identification for better security
- Contact form
- About us page

## Security Notes
- The passwords are stored using PHP's `password_hash()` function
- User input is validated and sanitized
- Row Level Security (RLS) policies in Supabase protect your data
- For a production environment, additional security measures would be recommended

## Technical Details
- PHP version: 7.4+ recommended
- PostgreSQL database (through Supabase)
- Bootstrap 5 for frontend
- Font Awesome for icons
- AOS library for animations 