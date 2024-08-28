<?php
namespace Teamup\Webhook\Payload;

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;
use Andrey\JsonHandler\JsonHandler;
use JsonException;
use JsonSerializable;
use ReflectionException;

#[JsonObjectAttribute]
final class Comment implements JsonSerializable
{
    public int $id;
    public ?string $name;
    public ?string $email;
    #[JsonItemAttribute(key: 'email_hash')]
    public ?string $emailHash;
    public CommentMessage $message;
    #[JsonItemAttribute(key: 'remote_id')]
    public ?string $remoteId;
    #[JsonItemAttribute(key: 'creation_dt')]
    public ?string $creationDt;
    #[JsonItemAttribute(key: 'update_dt')]
    public ?string $updateDt;
    public ?string $updater;

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
