<?php

namespace App\Message;

final class CommentMessage
{
    public function __construct(private int $id, private $context = [])
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
