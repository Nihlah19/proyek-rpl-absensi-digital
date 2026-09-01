# API Documentation untuk Modul IoT (ESP32)

Semua endpoint berkomunikasi menggunakan JSON (`Content-Type: application/json`).
Setiap request wajib menyertakan header `X-Api-Key` untuk autentikasi device.

## Base URL
`/api/iot/{action}/{type}`

## Format Response Global
Response selalu dikembalikan dengan struktur JSON berikut:
```json
{
    "success": true,
    "message": "Pesan status",
    "data": { ... }
}
```

---

## 1. Heartbeat
Mengirim status bahwa device ESP32 sedang online dan aktif.

**URL:** `/api/iot/heartbeat`  
**Method:** `POST`  
**Headers:**
- `X-Api-Key`: [String API Key]

**Request Body:**
```json
{
    "device_code": "ESP32_RFID_001",
    "device_type": "RFID_SISWA",
    "firmware_version": "1.0.0",
    "ip_address": "192.168.1.50"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Heartbeat acknowledged",
    "data": {
        "timestamp": "2026-08-28 07:00:00"
    }
}
```

---

## 2. Absensi Siswa (RFID)
Endpoint untuk memproses scan kartu RFID siswa.

**URL:** `/api/iot/attendance/student`  
**Method:** `POST`  
**Headers:**
- `X-Api-Key`: [String API Key]

**Request Body:**
```json
{
    "device_code": "ESP32_RFID_001",
    "rfid_uid": "A1B2C3D4",
    "timestamp": "2026-08-28T07:10:00Z" 
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Absensi siswa berhasil",
    "data": {
        "nama": "Siswa 5",
        "status": "hadir",
        "jam_datang": "07:10:00",
        "jam_pulang": "15:30:00"
    }
}
```

**Error Responses:**
- `400 Bad Request`: Format salah, atau batas absensi belum dibuka (terlalu pagi).
- `401 Unauthorized`: API Key salah.
- `403 Forbidden`: Device nonaktif atau salah tipe.
- `404 Not Found`: RFID tidak terdaftar.
- `409 Conflict`: Siswa sudah melakukan absensi (duplikasi) hari ini.

---

## 3. Absensi Guru (Barcode/QR)
Endpoint untuk memproses scan QR Code atau Barcode guru. Mendukung logika 2-arah (Datang dan Pulang).

**URL:** `/api/iot/attendance/teacher`  
**Method:** `POST`  
**Headers:**
- `X-Api-Key`: [String API Key]

**Request Body:**
```json
{
    "device_code": "ESP32_QR_001",
    "barcode": "QR-GURU-001",
    "timestamp": "2026-08-28T07:15:00Z"
}
```

**Success Response (Absen Datang - 200):**
```json
{
    "success": true,
    "message": "Absensi masuk guru berhasil",
    "data": {
        "status": "hadir",
        "jam_datang": "07:15:00"
    }
}
```

**Success Response (Absen Pulang - 200):**
```json
{
    "success": true,
    "message": "Absensi pulang guru berhasil",
    "data": {
        "jam_pulang": "16:00:00"
    }
}
```

**Error Responses:**
- Serupa dengan endpoint Siswa.
- `409 Conflict`: Guru sudah absen pulang.
