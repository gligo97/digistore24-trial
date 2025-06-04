<?php

namespace App\Service;

use App\Repository\MessageRepository;

class MessageService
{
    public function __construct(private readonly MessageRepository $messageRepository)
    {
    }

    /**
     * @return array<int, array{uuid: string|null, text: string|null, status: string|null}>
     */
    public function processMessages(?string $status = null): array
    {
        $messages = $this->messageRepository->findMessagesByStatus($status);

        $result = [];
        foreach ($messages as $message) {
            $result[] = [
                'uuid'   => $message->getUuid(),
                'text'   => $message->getText(),
                'status' => $message->getStatus(),
            ];
        }

        return $result;
    }
}