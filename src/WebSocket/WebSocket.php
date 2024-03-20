<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\WebSocket;

/**
 * WebSocket Component which contains all the logic for sending notifications to the client browsers.
 * Class WebSocketComponent
 * @package App\Core\Components
 *
 * TODO: Need to refactor the send methods sendNotifications
 */
class WebSocket
{

    const TYPE_INDICATOR = 1;
    const TYPE_ACTIVITY = 3;

    public function sendNotifications(array $notifications)
    {

        foreach ($notifications as $notification) {
            switch ($notification['type']) {
                case self::TYPE_INDICATOR:
                    // this was added by @gurav, but not sure why
                    if (isset($notification['options'])) {
                        $this->sendToastrIndicator($notification['options']);
                    } else {
                        $this->sendToastrIndicator($notification);
                    }
                    break;
            }
        }
    }

    public function sendWebsocketNotification(array $data)
    {

        $this->sendNotifications([$data]);
    }
    /**
     * @param $options
     * Sends a specific notification, the data array will be different depending on what you want to send to the client.
     */
    public function sendToastrIndicator(array $options)
    {
        $data = [
            'type' => 'indicator',
            'user_ids' => $options['user_ids'] ?? $options['user_ids'] ?? [],
            'data' => $options,
        ];
        $this->sendWebSocketRequest($data);
    }

    public function sendErrorMessage($options)
    {

        $data = [
            'uri' => '/error/broadcast',
            'data' => $options,
        ];
        $this->sendWebSocketRequest($data);
    }

    public function sendVideoUploadComplete(array $options): void
    {

        $data = [
            'uri' => '/video/upload/complete',
            'data' => $options,
        ];
        $this->sendWebSocketRequest($data);
    }

    public function sendPhotoUploadComplete(array $options): void
    {

        $data = [
            'uri' => '/photo/upload/complete',
            'data' => $options,
        ];
        $this->sendWebSocketRequest($data);
    }

    public function sendActivity($options)
    {

        $data = [
            'uri' => '/activity',
            'data' => $options,
        ];
        $this->sendWebSocketRequest($data);
    }

    //sends a websocket notification via curl to the websocket notification server
    private function sendWebSocketRequest($data)
    {

        $ch = curl_init();
        $host = $_ENV['WEBSOCKET_HOST'];
        curl_setopt($ch, CURLOPT_URL, $host . $data['uri']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
        curl_close($ch);

        if (isset($error_msg)) {
        }
        return $response;
    }
}
