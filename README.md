# Hubtel Laravel SMS Package

A Laravel package for sending SMS messages via the Hubtel API.

## Installation

You can install the package via composer:

```bash
composer require osik/hubtel-laravel-sms
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Osik\HubtelLaravelSms\HubtelSmsServiceProvider" --tag="config"
```

Then, add your Hubtel credentials to your `.env` file:

```
HUBTEL_CLIENT_ID=your-client-id
HUBTEL_CLIENT_SECRET=your-client-secret
HUBTEL_SENDER_ID=Your App
```

## Usage

### Basic Usage

```php
use Osik\HubtelLaravelSms\Facades\HubtelSms;

// Send a single SMS
$result = HubtelSms::send('233201234567', 'Hello from Laravel!');

// Check if the message was sent successfully
if ($result['success']) {
    $messageId = $result['message_id'];
    echo "Message sent successfully with ID: {$messageId}";
} else {
    echo "Failed to send message: " . $result['error'];
}
```

### Send to Multiple Recipients

```php
$recipients = ['233201234567', '233207654321', '233241234567'];
$results = HubtelSms::sendBulk($recipients, 'Bulk message from Laravel!');

foreach ($results as $phone => $result) {
    if ($result['success']) {
        echo "Message to {$phone} sent successfully\n";
    } else {
        echo "Failed to send to {$phone}: {$result['error']}\n";
    }
}
```

### Check Message Status

```php
$status = HubtelSms::checkStatus('message-id-from-send-response');

if ($status['success']) {
    echo "Status: " . $status['data']['Status'];
} else {
    echo "Failed to check status: " . $status['error'];
}
```

### Check Account Balance

```php
$balance = HubtelSms::getBalance();

if ($balance['success']) {
    echo "Current balance: " . $balance['data']['Balance'];
} else {
    echo "Failed to check balance: " . $balance['error'];
}
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.