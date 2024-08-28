<?php
namespace Teamup\Webhook\Payload;

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;
use Andrey\JsonHandler\JsonHandler;
use JsonException;
use JsonSerializable;
use ReflectionException;
use Teamup\Webhook\Trigger;

#[JsonObjectAttribute]
final class Event implements JsonSerializable
{
    public Trigger $trigger;
    public string $id;
    #[JsonItemAttribute(key: 'series_id')]
    public ?string $seriesId;
    #[JsonItemAttribute(key: 'remote_id')]
    public ?string $remoteId;
    #[JsonItemAttribute(key: 'subcalendar_id')]
    public ?int $subcalendarId;
    /** @var int[] */
    #[JsonItemAttribute(key: 'subcalendar_ids', type: 'integer')]
    public ?array $subcalendarIds;
    #[JsonItemAttribute(key: 'all_day')]
    public ?bool $allDay;
    public ?string $rrule;
    public ?string $title;
    public ?string $who;
    public ?string $location;
    public ?Note $notes;
    public ?string $version;
    public ?bool $readonly;
    public ?string $tz;
    /** @var null|Attachment[] */
    #[JsonItemAttribute(type: Attachment::class)]
    public ?array $attachments;
    #[JsonItemAttribute(key: 'start_dt')]
    public ?string $startDt;
    #[JsonItemAttribute(key: 'end_dt')]
    public ?string $endDt;
    #[JsonItemAttribute(key: 'ristart_dt')]
    public ?string $ristartDt;
    #[JsonItemAttribute(key: 'rsstart_dt')]
    public ?string $rsstartDt;
    #[JsonItemAttribute(key: 'creation_dt')]
    public ?string $creationDt;
    #[JsonItemAttribute(key: 'update_dt')]
    public ?string $updateDt;
    #[JsonItemAttribute(key: 'delete_dt')]
    public ?string $deleteDt;
    #[JsonItemAttribute(key: 'comments_enabled')]
    public ?bool $commentsEnabled;
    #[JsonItemAttribute(key: 'comments_visibility')]
    public ?bool $commentsVisibility;
    /** @var Comment[]|null */
    #[JsonItemAttribute(type: Comment::class)]
    public ?array $comments;
    #[JsonItemAttribute(key: 'signup_enabled')]
    public ?bool $signupEnabled;
    #[JsonItemAttribute(key: 'signup_deadline')]
    public ?string $signupDeadline;
    #[JsonItemAttribute(key: 'signup_visibility')]
    public ?string $signupVisibility;
    #[JsonItemAttribute(key: 'signup_limit')]
    public ?int $signupLimit;
    #[JsonItemAttribute(key: 'signup_count')]
    public ?int $signupCount;
    #[JsonItemAttribute(key: 'signup_deadline_enabled')]
    public ?bool $signupDeadlineEnabled;
    /** @var Signup[]|null */
    #[JsonItemAttribute(type: Signup::class)]
    public ?array $signups;

    /**
     * @return array<string, mixed>
     * @throws ReflectionException
     * @throws JsonException
     */
    public function jsonSerialize(): array
    {
        return (new JsonHandler())->serialize($this);
    }
}
