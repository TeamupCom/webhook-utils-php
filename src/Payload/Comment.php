<?php

namespace Teamup\Webhook\Payload;

use DateTimeImmutable;
use Teamup\Webhook\Attributes\PayloadObject;

#[PayloadObject]
class Comment
{
    public int $id;
    public string $name;
    public string $email;
    public ?string $emailHash;
    public CommentMessage $message;
    public ?string $remoteId;
    public DateTimeImmutable $creationDt;
    public ?DateTimeImmutable $updateDt;
    public string $updater;
}
