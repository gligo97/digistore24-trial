<?php
declare(strict_types=1);

namespace App\Controller;

use App\Message\SendMessage;
use App\Service\MessageService;
use Controller\MessageControllerTest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

/**
 * @see MessageControllerTest
 *
 * For method list() I'd relocate the logic into a Service class where the data will be processed.
 * Also, Enum class could be created for status
 * Keep the controller light as possible, treat the Service class as the brain
 *
 * For method send() I'd put dispatching into a try catch block and throw an error if it fails
 *
 */
class MessageController extends AbstractController
{
    public function __construct(
        private readonly MessageService $messageService,
        private readonly MessageBusInterface $bus
    ) {
    }

    #[Route('/messages', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $allowedStatuses = ['sent', 'read'];

        $status = $request->query->get('status');
        $status = is_scalar($status) ? (string)$status : null;

        if ($status !== null && !in_array($status, $allowedStatuses, true)) {
            return $this->json(
                ['error' => 'Invalid status. Allowed values: sent, read.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $messages = $this->messageService->processMessages($status);

        return $this->json(['messages' => $messages], headers: ['Content-Type' => 'application/json']);
    }

    #[Route('/messages/send', methods: ['GET'])]
    public function send(Request $request): Response
    {
        $text = $request->query->get('text');

        if (!is_scalar($text) || trim((string)$text) === '') {
            return new Response('Text is required', Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->bus->dispatch(new SendMessage((string)$text));
        } catch (Throwable) {
            return new Response('Internal Server Error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}