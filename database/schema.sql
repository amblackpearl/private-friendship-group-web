-- ============================================================
-- Friendship Group Web Application — Database Schema
-- ============================================================
-- Run this file to create the database and all required tables.
-- Usage: mysql -u root -p < database/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS if0_42045184_friendship_group_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE if0_42045184_friendship_group_db;

-- ============================================================
-- Table: users
-- Stores registered user accounts (member and admin).
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('member', 'admin') DEFAULT 'member',
    profile_photo VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
) ENGINE=InnoDB;

-- ============================================================
-- Table: photos
-- Stores photo gallery data uploaded by users.
-- ============================================================
CREATE TABLE IF NOT EXISTS photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    caption VARCHAR(150) NOT NULL,
    description TEXT NULL,
    file_path VARCHAR(255) NOT NULL,
    location VARCHAR(150) NULL,
    trip_date DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_photos_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table: votes
-- Stores voting form data created by users.
-- ============================================================
CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_by INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    status ENUM('draft', 'active', 'closed') DEFAULT 'active',
    deadline DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_votes_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table: vote_options
-- Stores options for each voting form.
-- ============================================================
CREATE TABLE IF NOT EXISTS vote_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vote_id INT NOT NULL,
    option_text VARCHAR(150) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vote_options_vote
        FOREIGN KEY (vote_id) REFERENCES votes(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table: vote_responses
-- Stores user vote submissions.
-- Each user can only vote once per voting form (unique constraint).
-- ============================================================
CREATE TABLE IF NOT EXISTS vote_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vote_id INT NOT NULL,
    option_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vote_responses_vote
        FOREIGN KEY (vote_id) REFERENCES votes(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_vote_responses_option
        FOREIGN KEY (option_id) REFERENCES vote_options(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_vote_responses_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    UNIQUE KEY unique_user_vote (vote_id, user_id)
) ENGINE=InnoDB;

-- ============================================================
-- Table: trip_agendas
-- Stores next trip agenda proposals submitted by users.
-- ============================================================
CREATE TABLE IF NOT EXISTS trip_agendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    destination VARCHAR(150) NOT NULL,
    proposed_date DATE NOT NULL,
    estimated_budget DECIMAL(12,2) NOT NULL,
    meeting_point VARCHAR(150) NULL,
    transportation_plan TEXT NULL,
    accommodation_plan TEXT NULL,
    activity_list TEXT NULL,
    description TEXT NOT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_trip_agendas_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Recommended Indexes for Performance
-- ============================================================

-- Users indexes
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_deleted_at ON users(deleted_at);

-- Photos indexes
CREATE INDEX idx_photos_user_id ON photos(user_id);
CREATE INDEX idx_photos_created_at ON photos(created_at);
CREATE INDEX idx_photos_deleted_at ON photos(deleted_at);

-- Votes indexes
CREATE INDEX idx_votes_created_by ON votes(created_by);
CREATE INDEX idx_votes_status ON votes(status);
CREATE INDEX idx_votes_deadline ON votes(deadline);
CREATE INDEX idx_votes_deleted_at ON votes(deleted_at);

-- Vote options indexes
CREATE INDEX idx_vote_options_vote_id ON vote_options(vote_id);

-- Vote responses indexes
CREATE INDEX idx_vote_responses_vote_id ON vote_responses(vote_id);
CREATE INDEX idx_vote_responses_user_id ON vote_responses(user_id);

-- Trip agendas indexes
CREATE INDEX idx_trip_agendas_user_id ON trip_agendas(user_id);
CREATE INDEX idx_trip_agendas_proposed_date ON trip_agendas(proposed_date);
CREATE INDEX idx_trip_agendas_deleted_at ON trip_agendas(deleted_at);
