<?php

namespace Teamup\Webhook\Payload;

use DateTimeImmutable;
use Teamup\Webhook\Attributes\PayloadItem;
use Teamup\Webhook\Attributes\PayloadObject;

#[PayloadObject]
class Event
{
    public string $id;
    public ?string $seriesId;
    public ?string $remoteId;
    public int $subcalendarId;
    /** @var int[] */
    #[PayloadItem(type: 'integer')]
    public array $subcalendarIds;
    public bool $allDay;
    public string $rrule;
    public string $title;
    public string $who;
    public string $location;
    public Notes $notes;
    public string $version;
    public bool $readonly;
    #[PayloadItem(key: 'tz')]
    public string $timezone;
    /** @var Attachment[] */
    #[PayloadItem(type: Attachment::class)]
    public array $attachments;
    public DateTimeImmutable $startDt;
    public DateTimeImmutable $endDt;
    public ?DateTimeImmutable $ristartDt;
    public ?DateTimeImmutable $rsstartDt;
    public DateTimeImmutable $creationDt;
    public ?DateTimeImmutable $updateDt;
    public ?DateTimeImmutable $deleteDt;
    public bool $commentsEnabled;
    public string $commentsVisibility;
    /** @var Comment[] */
    #[PayloadItem(type: Comment::class)]
    public array $comments;
    /** @var array<string, mixed>|null */
    public ?array $custom = null;
    public bool $signupEnabled;
    public DateTimeImmutable $signupDeadline;
    public string $signupVisibility;
    public int $signupLimit;
    public int $signupCount;
    public bool $signupDeadlineEnabled;
    /** @var Signup[] */
    #[PayloadItem(type: Signup::class)]
    public array $signups;
}
