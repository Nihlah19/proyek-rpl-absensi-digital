<?php
class IotDevice extends Model {
    public function getByDeviceCode($deviceCode) {
        $this->db->query("SELECT * FROM iot_devices WHERE device_code = :device_code");
        $this->db->bind(':device_code', $deviceCode);
        return $this->db->single();
    }

    public function updateHeartbeat($id, $ip_address) {
        // Update last_seen dan ip_address (jangan overwrite kolom location/nama)
        $this->db->query("UPDATE iot_devices SET last_seen = CURRENT_TIMESTAMP, ip_address = :ip_address WHERE id = :id");
        $this->db->bind(':ip_address', $ip_address);
        $this->db->bind(':id', $id);
        $this->db->execute();
    }

    public function logActivity($device_id, $payload, $response_code) {
        $this->db->query("INSERT INTO iot_logs (device_id, payload, response_code) VALUES (:device_id, :payload, :response_code)");
        $this->db->bind(':device_id', $device_id);
        $this->db->bind(':payload', $payload);
        $this->db->bind(':response_code', $response_code);
        $this->db->execute();
    }
}

