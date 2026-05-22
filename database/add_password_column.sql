-- Run this in phpMyAdmin if manual sign up shows database errors
USE ormoc_job_db;

ALTER TABLE users ADD COLUMN password VARCHAR(255) NULL AFTER email;
