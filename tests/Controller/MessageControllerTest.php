<?php
declare(strict_types=1);

namespace Controller;

use App\Message\SendMessage;
use App\Service\MessageService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class MessageControllerTest extends WebTestCase
{
    use InteractsWithMessenger;

    private KernelBrowser $client;

    /**
     * @var MockObject&MessageService
     */
    private $messageService;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->messageService = $this->createMock(MessageService::class);
        $this->client->getContainer()->set(MessageService::class, $this->messageService);
    }

    public function testSendMessageDispatch(): void
    {
        $this->client->request('GET', '/messages/send', [
            'text' => 'Hello World',
        ]);

        self::assertResponseIsSuccessful();

        // This is using https://packagist.org/packages/zenstruck/messenger-test
        $this->transport('sync')
            ->queue()
            ->assertContains(SendMessage::class, 1);
    }

    /**
     * @throws \JsonException
     */
    public function testListWithoutStatusReturnsMessages(): void
    {
        $this->messageService
            ->expects($this->once())
            ->method('processMessages')
            ->with(null)
            ->willReturn([
                ['uuid' => 'uuid1', 'text' => 'Hello', 'status' => 'sent'],
                ['uuid' => 'uuid2', 'text' => 'World', 'status' => 'read'],
            ]);

        $this->client->request('GET', '/messages');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $content = $this->client->getResponse()->getContent();
        $this->assertNotFalse($content, 'Response content is false, cannot decode JSON');

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data);

        $this->assertArrayHasKey('messages', $data);
        $this->assertIsArray($data['messages']);
        $this->assertCount(2, $data['messages']);

        $this->assertArrayHasKey('uuid', $data['messages'][0]);
        $this->assertEquals('uuid1', $data['messages'][0]['uuid']);
    }

    /**
     * @throws \JsonException
     */
    public function testListWithValidStatusReturnsFilteredMessages(): void
    {
        $this->messageService
            ->expects($this->once())
            ->method('processMessages')
            ->with('sent')
            ->willReturn([
                ['uuid' => 'uuid1', 'text' => 'Hello', 'status' => 'sent'],
            ]);

        $this->client->request('GET', '/messages?status=sent');

        self::assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        $this->assertNotFalse($content, 'Response content is false, cannot decode JSON');

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('messages', $data);
        $this->assertIsArray($data['messages']);
        $this->assertCount(1, $data['messages']);
        $this->assertArrayHasKey('status', $data['messages'][0]);
        $this->assertEquals('sent', $data['messages'][0]['status']);
    }

    /**
     * @throws \JsonException
     */
    public function testListWithInvalidStatusReturnsBadRequest(): void
    {
        $this->client->request('GET', '/messages?status=invalid');

        self::assertResponseStatusCodeSame(400);
        $content = $this->client->getResponse()->getContent();
        $this->assertNotFalse($content, 'Response content is false, cannot decode JSON');

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Invalid status', (string)$data['error']);
    }
}