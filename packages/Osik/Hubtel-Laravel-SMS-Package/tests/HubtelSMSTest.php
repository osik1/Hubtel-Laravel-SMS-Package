<?php

namespace Osik\HubtelLaravelSms\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Orchestra\Testbench\TestCase;
use Osik\HubtelLaravelSms\HubtelSms;
use Osik\HubtelLaravelSms\HubtelSmsServiceProvider;

class HubtelSmsTest extends TestCase
{
    protected $hubtelSms;
    
    protected function getPackageProviders($app)
    {
        return [HubtelSmsServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->hubtelSms = new HubtelSms(
            'test-client-id',
            'test-client-secret',
            'Test App'
        );
        
        // Replace the HTTP client with a mock
        $mockProperty = new \ReflectionProperty(HubtelSms::class, 'httpClient');
        $mockProperty->setAccessible(true);
        
        // Create mock responses
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'MessageId' => 'test-message-id-123',
                'Status' => 'Success',
                'NetworkId' => 'test-network'
            ])),
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        $mockProperty->setValue($this->hubtelSms, $client);
    }

    public function testSendSms()
    {
        $result = $this->hubtelSms->send('233201234567', 'Test message');
        
        $this->assertTrue($result['success']);
        $this->assertEquals('test-message-id-123', $result['message_id']);
    }

    public function testPhoneNumberFormatting()
    {
        // Use reflection to access protected method
        $method = new \ReflectionMethod(HubtelSms::class, 'formatPhoneNumber');
        $method->setAccessible(true);
        
        // Test Ghanaian number without country code
        $this->assertEquals('+233201234567', $method->invoke($this->hubtelSms, '0201234567'));
        
        // Test with international format
        $this->assertEquals('+233201234567', $method->invoke($this->hubtelSms, '233201234567'));
        
        // Test with plus prefix
        $this->assertEquals('+233201234567', $method->invoke($this->hubtelSms, '+233201234567'));
    }
}