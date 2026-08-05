-- Add new fields to users table for better auth tracking

-- Add auth_provider field
ALTER TABLE users ADD COLUMN IF NOT EXISTS auth_provider VARCHAR(20) DEFAULT 'email';

-- Add profile_picture field
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture TEXT NULL;

-- Add last_login field
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP WITH TIME ZONE NULL;

-- Add login_count field
ALTER TABLE users ADD COLUMN IF NOT EXISTS login_count INTEGER DEFAULT 0;

-- Create login_history table if not exists
CREATE TABLE IF NOT EXISTS login_history (
    id SERIAL PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id),
    login_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    auth_provider VARCHAR(20) NOT NULL,
    success BOOLEAN DEFAULT TRUE,
    user_agent TEXT NULL
);

-- Create signup_events table if not exists
CREATE TABLE IF NOT EXISTS signup_events (
    id SERIAL PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id),
    signup_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    auth_provider VARCHAR(20) NOT NULL,
    referral_source VARCHAR(255) NULL
);

-- Create index for better query performance
CREATE INDEX IF NOT EXISTS idx_login_history_user_id ON login_history(user_id);
CREATE INDEX IF NOT EXISTS idx_signup_events_user_id ON signup_events(user_id);

-- Update existing users with email auth_provider if google_id is NULL
UPDATE users SET auth_provider = 'email' WHERE google_id IS NULL AND auth_provider IS NULL;

-- Update existing users with google auth_provider if google_id is NOT NULL
UPDATE users SET auth_provider = 'google' WHERE google_id IS NOT NULL AND auth_provider IS NULL; 