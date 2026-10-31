<?php
class IotDevice extends Model {
    public function getByDeviceCode($deviceCode) {
        $this->db->query("SELECT * FROM iot_devices WHERE device_code = :device_code");
        $this->db->bind(':device_code', $deviceCode);
        return $this->db->single();
    }

    public function getDeviceById($id) {
        $now = date('Y-m-d H:i:s');
        $this->db->query("
            SELECT 
                id, device_code, device_name, device_type, location, ip_address, status, app_mode, last_seen, wifi_ssid,
                pending_host, pending_ssid, pending_pass, last_scanned_uid, last_scan_time,
                CASE
                    WHEN last_seen IS NULL THEN 'offline'
                    WHEN last_seen >= DATE_SUB(:now, INTERVAL 5 MINUTE) THEN 'online'
                    ELSE 'offline'
                END AS iot_status
            FROM iot_devices 
            WHERE id = :id
        ");
        $this->db->bind(':now', $now);
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getAllDevices() {
        $now = date('Y-m-d H:i:s');
        $this->db->query("
            SELECT
                id, device_code, device_name, device_type, location, ip_address, status, app_mode, last_seen, wifi_ssid,
                pending_host, pending_ssid, pending_pass, last_scanned_uid, last_scan_time,
                CASE
                    WHEN last_seen IS NULL THEN 'offline'
                    WHEN last_seen >= DATE_SUB(:now, INTERVAL 5 MINUTE) THEN 'online'
                    ELSE 'offline'
                END AS iot_status
            FROM iot_devices
            ORDER BY device_type, device_name
        ");
        $this->db->bind(':now', $now);
        return $this->db->resultSet();
    }

    public function updateHeartbeat($id, $ip_address) {
        $this->db->query("UPDATE iot_devices SET last_seen = CURRENT_TIMESTAMP, ip_address = :ip_address WHERE id = :id");
        $this->db->bind(':ip_address', $ip_address);
        $this->db->bind(':id', $id);
        $this->db->execute();
    }

    public function updateHeartbeatWithNetwork($id, $ip_address, $wifi_ssid = null) {
        if (!empty($wifi_ssid)) {
            $this->db->query("UPDATE iot_devices SET last_seen = CURRENT_TIMESTAMP, ip_address = :ip_address, wifi_ssid = :wifi_ssid WHERE id = :id");
            $this->db->bind(':wifi_ssid', $wifi_ssid);
        } else {
            $this->db->query("UPDATE iot_devices SET last_seen = CURRENT_TIMESTAMP, ip_address = :ip_address WHERE id = :id");
        }
        $this->db->bind(':ip_address', $ip_address);
        $this->db->bind(':id', $id);
        $this->db->execute();
    }

    public function updateDeviceDetails($id, $data) {
        $sql = "UPDATE iot_devices SET device_name = :device_name, location = :location, status = :status";
        
        if (isset($data['pending_ssid'])) {
            $sql .= ", pending_ssid = :pending_ssid";
        }
        if (isset($data['pending_pass'])) {
            $sql .= ", pending_pass = :pending_pass";
        }
        if (isset($data['pending_host'])) {
            $sql .= ", pending_host = :pending_host";
        }
        
        $sql .= " WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(':device_name', $data['device_name']);
        $this->db->bind(':location',    $data['location']);
        $this->db->bind(':status',      $data['status']);
        $this->db->bind(':id',          $id);

        if (isset($data['pending_ssid'])) {
            $this->db->bind(':pending_ssid', $data['pending_ssid']);
        }
        if (isset($data['pending_pass'])) {
            $this->db->bind(':pending_pass', $data['pending_pass']);
        }
        if (isset($data['pending_host'])) {
            $this->db->bind(':pending_host', $data['pending_host']);
        }

        return $this->db->execute();
    }

    public function clearPendingConfig($id) {
        $this->db->query("UPDATE iot_devices SET pending_ssid = NULL, pending_pass = NULL, pending_host = NULL WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function logActivity($device_id, $payload, $response_code) {
        $this->db->query("INSERT INTO iot_logs (device_id, payload, response_code) VALUES (:device_id, :payload, :response_code)");
        $this->db->bind(':device_id', $device_id);
        $this->db->bind(':payload', $payload);
        $this->db->bind(':response_code', $response_code);
        $this->db->execute();
    }

    public function updateLastScan($id, $uid) {
        $this->db->query("UPDATE iot_devices SET last_scanned_uid = :uid, last_scan_time = CURRENT_TIMESTAMP WHERE id = :id");
        $this->db->bind(':uid', $uid);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function setAppMode($id, $mode) {
        $mode = in_array($mode, ['absen', 'regis']) ? $mode : 'absen';
        $this->db->query("UPDATE iot_devices SET app_mode = :app_mode WHERE id = :id");
        $this->db->bind(':app_mode', $mode);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
