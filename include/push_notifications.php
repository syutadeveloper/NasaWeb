<?php
/**
 * Web Push Notifications Service
 * Отправка push уведомлений через Web Push API
 */

class PushNotificationService {
    private $conn;
    private $vapid_public_key;
    private $vapid_private_key;
    private $vapid_subject;
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
        $this->vapid_public_key = VAPID_PUBLIC_KEY;
        $this->vapid_private_key = VAPID_PRIVATE_KEY;
        $this->vapid_subject = VAPID_SUBJECT;
    }
    
    /**
     * Отправить push уведомление
     */
    public function sendPushNotification($device_token, $title, $body, $data = []) {
        try {
            // Простая реализация Web Push (в реальном проекте используйте библиотеку)
            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => '/assets/img/icon-192.png',
                'badge' => '/assets/img/badge-72.png',
                'data' => $data,
                'actions' => [
                    [
                        'action' => 'view',
                        'title' => 'Посмотреть'
                    ],
                    [
                        'action' => 'close',
                        'title' => 'Закрыть'
                    ]
                ]
            ]);
            
            // Здесь должна быть реальная отправка через Web Push API
            // Для демо просто логируем
            error_log("Push notification sent to {$device_token}: {$title}");
            
            return true;
            
        } catch (Exception $e) {
            error_log("Push notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Отправить критическое оповещение всем подписчикам
     */
    public function sendCriticalAlert($alert) {
        try {
            // Получаем всех подписчиков в радиусе оповещения
            $sql = "SELECT ud.*, s.category_alerts 
                    FROM user_devices ud 
                    LEFT JOIN subscriptions s ON ud.user_id = s.user_id 
                    WHERE ud.latitude IS NOT NULL 
                    AND ud.longitude IS NOT NULL 
                    AND (s.category_alerts = 1 OR s.category_alerts IS NULL)
                    AND (
                        6371 * acos(
                            cos(radians(?)) * cos(radians(ud.latitude)) * 
                            cos(radians(ud.longitude) - radians(?)) + 
                            sin(radians(?)) * sin(radians(ud.latitude))
                    ) <= ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $alert['latitude'],
                $alert['longitude'],
                $alert['latitude'],
                $alert['radius_km']
            ]);
            
            $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $sent = 0;
            $failed = 0;
            
            foreach ($devices as $device) {
                $title = "🚨 " . $this->getAlertTitle($alert);
                $body = $alert['message_ru'] ?: $alert['message_en'];
                
                if ($this->sendPushNotification($device['push_token'], $title, $body, [
                    'alert_id' => $alert['id'],
                    'type' => $alert['type'],
                    'severity' => $alert['severity']
                ])) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
            
            // Логируем результат
            $this->logNotification($alert['id'], count($devices), $sent, $failed);
            
            return [
                'total' => count($devices),
                'sent' => $sent,
                'failed' => $failed
            ];
            
        } catch (Exception $e) {
            error_log("Critical alert sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получить заголовок оповещения
     */
    private function getAlertTitle($alert) {
        $titles = [
            'earthquake' => 'Землетрясение',
            'fire' => 'Пожар',
            'flood' => 'Наводнение',
            'storm' => 'Шторм',
            'air_quality' => 'Качество воздуха',
            'other' => 'Оповещение'
        ];
        
        return $titles[$alert['type']] ?? 'Оповещение';
    }
    
    /**
     * Логировать уведомление
     */
    private function logNotification($alert_id, $recipients, $sent, $failed) {
        try {
            $sql = "INSERT INTO audit_logs (alert_id, recipients_count, delivered_count, failed_count, payload) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $alert_id,
                $recipients,
                $sent,
                $failed,
                json_encode(['timestamp' => date('Y-m-d H:i:s')])
            ]);
        } catch (Exception $e) {
            error_log("Logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Отправить тестовое уведомление
     */
    public function sendTestNotification($device_token) {
        return $this->sendPushNotification(
            $device_token,
            "🧪 Тестовое уведомление",
            "Это тестовое уведомление от Disaster Alert",
            ['test' => true]
        );
    }
}
?>
