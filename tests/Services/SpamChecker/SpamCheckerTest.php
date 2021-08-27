<?php

namespace App\Tests\Services\SpamChecker;

use App\Entity\Comment;
use App\Services\SpamChecker\SpamChecker;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception as HttpException;

class SpamCheckerTest extends TestCase
{
    /**
     * @throws HttpException\TransportExceptionInterface|HttpException\ServerExceptionInterface|HttpException\RedirectionExceptionInterface|HttpException\ClientExceptionInterface
     */
    public function testSpamScoreWithInvalidRequest(): void
    {
        $httpClient = new MockHttpClient([new MockResponse('invalid', ['response_headers' => ['x-akismet-debug-help: Invalid key']])]);
        $spamChecker = new SpamChecker($httpClient, '123456');

        $this->expectException(RuntimeException::class);
        $spamChecker->isCommentSpam(new Comment(), []);
    }

    /**
     * @throws HttpException\TransportExceptionInterface|HttpException\ServerExceptionInterface|HttpException\RedirectionExceptionInterface|HttpException\ClientExceptionInterface
     */
    public function testSpamScoreShouldBeTrueWhenServiceRecognizeAsSpam(): void
    {
        $httpClient = new MockHttpClient([new MockResponse('true')]);
        $spamChecker = new SpamChecker($httpClient, '123456');
        $isSpam = $spamChecker->isCommentSpam(new Comment(), []);

        $this->assertTrue($isSpam);
    }

    /**
     * @throws HttpException\TransportExceptionInterface|HttpException\ServerExceptionInterface|HttpException\RedirectionExceptionInterface|HttpException\ClientExceptionInterface
     */
    public function testSpamScoreShouldBeFalseWhenServiceRecognizeAsNotSpam(): void
    {
        $httpClient = new MockHttpClient([new MockResponse('false')]);
        $spamChecker = new SpamChecker($httpClient, '123456');
        $isSpam = $spamChecker->isCommentSpam(new Comment(), []);

        $this->assertFalse($isSpam);
    }

    /**
     * @throws HttpException\TransportExceptionInterface|HttpException\ServerExceptionInterface|HttpException\RedirectionExceptionInterface|HttpException\ClientExceptionInterface
     */
    public function testSpamScoreShouldBeTrueWhenServiceRecognizeAsBlatantSpam(): void
    {
        $httpClient = new MockHttpClient([new MockResponse('false', ['response_headers' => ['x-aksamit-pro-tip: discard']])]);
        $spamChecker = new SpamChecker($httpClient, '123456');
        $isSpam = $spamChecker->isCommentSpam(new Comment(), []);

        $this->assertTrue($isSpam);
    }
}
