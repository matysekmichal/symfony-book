<?php

namespace App\Message;

use App\Repository\CommentRepository;
use App\Services\SpamChecker\SpamCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Contracts\HttpClient\Exception as HttpClientException;

final class CommentMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SpamCheckerInterface   $spamChecker,
        private CommentRepository      $commentRepository,
        private MessageBusInterface    $bus,
        private WorkflowInterface      $commentStateMachine,
        private LoggerInterface        $logger)
    {
    }

    /**
     * @throws HttpClientException\TransportExceptionInterface
     * @throws HttpClientException\ServerExceptionInterface
     * @throws HttpClientException\RedirectionExceptionInterface
     * @throws HttpClientException\ClientExceptionInterface
     */
    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment) {
            return;
        }

        if ($this->commentStateMachine->can($comment, 'accept')) {
            $score = $this->spamChecker->isCommentSpam($comment, $message->getContext());
            $transition = 'accept';
            if (2 === $score) {
                $transition = 'reject_spam';
            } elseif (1 === $score) {
                $transition = 'might_be_spam';
            }
            $this->commentStateMachine->apply($comment, $transition);
            $this->entityManager->flush();

            $this->bus->dispatch($message);
        } elseif ($this->commentStateMachine->can($comment, 'publish') || $this->commentStateMachine->can($comment, 'publish_ham')) {
            $this->commentStateMachine->apply($comment, $this->commentStateMachine->can($comment, 'publish') ? 'publish' : 'publish_ham');
            $this->entityManager->flush();
        } elseif ($this->logger) {
            $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }
    }
}
