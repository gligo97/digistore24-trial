<?php

namespace App\Tests\Service;

use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Service\MessageService;
use PHPUnit\Framework\TestCase;

class MessageServiceTest extends TestCase
{
    public function testProcessMessagesWithStatus(): void
    {
        $mockMessage = $this->createMock(Message::class);
        $mockMessage->method('getUuid')->willReturn('uuid-123');
        $mockMessage->method('getText')->willReturn('Status message');
        $mockMessage->method('getStatus')->willReturn('sent');

        $mockRepo = $this->createMock(MessageRepository::class);
        $mockRepo
            ->expects($this->once())
            ->method('findMessagesByStatus')
            ->with('sent')
            ->willReturn([$mockMessage]);

        $service = new MessageService($mockRepo);
        $result  = $service->processMessages('sent');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals([
            'uuid'   => 'uuid-123',
            'text'   => 'Status message',
            'status' => 'sent',
        ], $result[0]);
    }

    public function testProcessMessagesReturnsFormattedArray(): void
    {
        $mockMessage1 = $this->createMock(Message::class);
        $mockMessage1->method('getUuid')->willReturn('uuid-1');
        $mockMessage1->method('getText')->willReturn('Hello');
        $mockMessage1->method('getStatus')->willReturn('sent');

        $mockMessage2 = $this->createMock(Message::class);
        $mockMessage2->method('getUuid')->willReturn('uuid-2');
        $mockMessage2->method('getText')->willReturn('World');
        $mockMessage2->method('getStatus')->willReturn('read');

        $mockRepo = $this->createMock(MessageRepository::class);
        $mockRepo
            ->expects($this->once())
            ->method('findMessagesByStatus')
            ->with(null)
            ->willReturn([$mockMessage1, $mockMessage2]);

        $service = new MessageService($mockRepo);
        $result  = $service->processMessages();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertEquals([
            'uuid'   => 'uuid-1',
            'text'   => 'Hello',
            'status' => 'sent',
        ], $result[0]);

        $this->assertEquals([
            'uuid'   => 'uuid-2',
            'text'   => 'World',
            'status' => 'read',
        ], $result[1]);
    }
}