<?php

namespace Teamup\Webhook\Payload;

use DateTimeImmutable;
use Teamup\Webhook\Attributes\PayloadObject;

#[PayloadObject]
class Signup
{
    public int $id;
    public string $name;
    public ?string $emailHash;
    public ?string $remoteId;
    public bool $readonly;
    public DateTimeImmutable $creationDt;
    public ?DateTimeImmutable $updateDt;
}
