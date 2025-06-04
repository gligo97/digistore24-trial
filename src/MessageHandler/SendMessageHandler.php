<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Message;
use App\Message\SendMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final class SendMessageHandler
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(SendMessage $sendMessage): void
    {
        $message = (new Message())
            ->setUuid(Uuid::v6()->toRfc4122())
            ->setText($sendMessage->getText())
            ->setStatus('sent');

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }
}