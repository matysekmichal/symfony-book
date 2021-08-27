<?php

namespace App\Services\SpamChecker;

use App\Entity\Comment;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception as HttpClientException;

interface SpamCheckerInterface
{
    /**
     * @throws RuntimeException
     * @throws HttpClientException\RedirectionExceptionInterface
     * @throws HttpClientException\ClientExceptionInterface
     * @throws HttpClientException\TransportExceptionInterface
     * @throws HttpClientException\ServerExceptionInterface
     */
    public function isCommentSpam(Comment $comment, array $context): bool;
}
