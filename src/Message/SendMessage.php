<?php
declare(strict_types=1);

namespace App\Message;

class SendMessage
{
    public function __construct(
        private string $text,
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }
}