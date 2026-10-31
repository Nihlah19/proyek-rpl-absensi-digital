-- Migration: Add app_mode to iot_devices for remote switching between Scan Absen & Tambah Kartu
ALTER TABLE iot_devices 
ADD COLUMN IF NOT EXISTS app_mode ENUM('absen', 'regis') DEFAULT 'absen' AFTER status;
