<?php
declare(strict_types=1);

namespace Repository;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MessageRepositoryTest extends KernelTestCase
{
    private MessageRepository $messageRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var  MessageRepository $repository */
        $repository              = self::getContainer()->get(MessageRepository::class);
        $this->messageRepository = $repository;
    }

    public function testFindAllReturnsEmptyArray(): void
    {
        $this->assertSame([], $this->messageRepository->findAll());
    }
}