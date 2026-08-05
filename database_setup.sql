-- OPTION 1: Create users table with UUID primary key (recommended for Supabase Auth integration)
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT auth.uid(),  -- This links directly to Supabase Auth user IDs
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Add index for faster email lookups
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- Enable RLS on the users table
ALTER TABLE users ENABLE ROW LEVEL SECURITY;

-- Create a policy that allows users to see only their own data
CREATE POLICY users_policy ON users
    FOR ALL
    USING (auth.uid() = id);

-- OPTION 2: If you want to keep using SERIAL primary keys (alternative approach)
-- This example is commented out, uncomment if you prefer this approach
/*
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    auth_id UUID NULL UNIQUE,  -- Store Supabase auth.uid() here
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_auth_id ON users(auth_id);

ALTER TABLE users ENABLE ROW LEVEL SECURITY;

-- Policy using auth_id field
CREATE POLICY users_auth_policy ON users
    FOR ALL
    USING (auth.uid() = auth_id);

-- Fallback policy for authenticated users
CREATE POLICY users_authenticated_access ON users
    FOR ALL
    USING (auth.role() = 'authenticated');
*/ 