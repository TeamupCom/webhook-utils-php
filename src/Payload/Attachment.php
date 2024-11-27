<?php

namespace Teamup\Webhook\Payload;

use DateTimeImmutable;
use Teamup\Webhook\Attributes\PayloadObject;

#[PayloadObject]
class Attachment
{
    public string $id;
    public string $name;
    public int $size;
    public string $mimetype;
    public DateTimeImmutable $uploadDt;
    public string $link;
    public string $thumbnail;
    public string $preview;
    public DateTimeImmutable $uploadDate;
}
