-- Migration: Tambah kolom avatar pada tabel users jika belum ada
ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL AFTER name;
