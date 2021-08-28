<?php

namespace App\Message;

use App\Repository\CommentRepository;
use App\Services\SpamChecker\SpamCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\Exception as HttpClientException;

final class CommentMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SpamCheckerInterface   $spamChecker,
        private CommentRepository      $commentRepository)
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

        if ($this->spamChecker->isCommentSpam($comment, $message->getContext())) {
            $comment->setState('spam');
        } else {
            $comment->setState('published');
        }

        $this->entityManager->flush();
    }
}
