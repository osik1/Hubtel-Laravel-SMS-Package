<?php

namespace Osik\HubtelLaravelSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class HubtelSms
{
    /**
     * The Hubtel API client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The Hubtel API client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The default sender ID.
     *
     * @var string
     */
    protected $senderId;

    /**
     * The Hubtel API base URL.
     *
     * @var string
     */
    protected $baseUrl = 'https://sms.hubtel.com/v1/messages/send';

    /**
     * HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Create a new HubtelSms instance.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $senderId
     * @return void
     */
    public function __construct(string $clientId, string $clientSecret, string $senderId)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->senderId = $senderId;
        $this->httpClient = new Client();
    }

    /**
     * Send an SMS message.
     *
     * @param string $to Recipient's phone number (E.164 format recommended)
     * @param string $message The message content
     * @param string|null $senderId Optional custom sender ID
     * @return array Response from Hubtel API
     */
    public function send(string $to, string $message, ?string $senderId = null): array
    {
        $sender = $senderId ?: $this->senderId;

        try {
            $response = $this->httpClient->post($this->baseUrl, [
                'auth' => [$this->clientId, $this->clientSecret],
                'json' => [
                    'From' => $sender,
                    'To' => $this->formatPhoneNumber($to),
                    'Content' => $message,
                    'RegisteredDelivery' => true
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            return [
                'success' => true,
                'message_id' => $responseData['MessageId'] ?? null,
                'data' => $responseData
            ];
        } catch (GuzzleException $e) {
            Log::error('Hubtel SMS Error: ' . $e->getMessage(), [
                'to' => $to,
                'sender' => $sender,
                'exception' => $e
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e->getCode()
            ];
        }
    }

    /**
     * Send an SMS message to multiple recipients.
     *
     * @param array $recipients Array of phone numbers
     * @param string $message The message content
     * @param string|null $senderId Optional custom sender ID
     * @return array Results for each recipient
     */
    public function sendBulk(array $recipients, string $message, ?string $senderId = null): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            $results[$recipient] = $this->send($recipient, $message, $senderId);
        }

        return $results;
    }

    /**
     * Format the phone number to E.164 format if needed.
     *
     * @param string $number
     * @return string
     */
    protected function formatPhoneNumber(string $number): string
    {
        // Remove any non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // Handle Ghanaian numbers without country code
        if (strlen($number) === 9 && substr($number, 0, 1) === '0') {
            return '+233' . substr($number, 1);
        }

        // If the number doesn't have a + prefix but seems like an international format
        if (strlen($number) > 9 && substr($number, 0, 1) !== '+') {
            return '+' . $number;
        }

        return $number;
    }

    /**
     * Check SMS delivery status.
     *
     * @param string $messageId The message ID returned from send method
     * @return array Status information
     */
    public function checkStatus(string $messageId): array
    {
        try {
            $response = $this->httpClient->get("{$this->baseUrl}/{$messageId}", [
                'auth' => [$this->clientId, $this->clientSecret]
            ]);

            return [
                'success' => true,
                'data' => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (GuzzleException $e) {
            Log::error('Hubtel SMS Status Check Error: ' . $e->getMessage(), [
                'message_id' => $messageId,
                'exception' => $e
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e->getCode()
            ];
        }
    }

    /**
     * Get account balance.
     *
     * @return array Balance information
     */
    public function getBalance(): array
    {
        try {
            $response = $this->httpClient->get('https://api.hubtel.com/v1/account/balance', [
                'auth' => [$this->clientId, $this->clientSecret]
            ]);

            return [
                'success' => true,
                'data' => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (GuzzleException $e) {
            Log::error('Hubtel Balance Check Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e->getCode()
            ];
        }
    }
}