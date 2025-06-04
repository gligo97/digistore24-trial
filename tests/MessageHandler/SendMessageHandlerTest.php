<?php

namespace App\Tests\MessageHandler;

use App\Entity\Message;
use App\Message\SendMessage;
use App\MessageHandler\SendMessageHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class SendMessageHandlerTest extends TestCase
{

    public function testInvokePersistsAndFlushesMessage(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($message) {
                if (!$message instanceof Message) {
                    return false;
                }

                if ($message->getStatus() !== 'sent') {
                    return false;
                }

                if ($message->getText() !== 'Test message') {
                    return false;
                }

                $uuid = $message->getUuid();
                if ($uuid === null) {
                    return false;
                }

                if (!preg_match('/^[0-9a-f\-]{36}$/', $uuid)) {
                    return false;
                }
                return true;
            }));


        $entityManager
            ->expects($this->once())
            ->method('flush');

        $handler = new SendMessageHandler($entityManager);

        $sendMessage = new SendMessage('Test message');

        $handler($sendMessage);
    }
}