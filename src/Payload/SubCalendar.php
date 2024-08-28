<?php
namespace Teamup\Webhook\Payload;

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;
use Andrey\JsonHandler\JsonHandler;
use JsonException;
use JsonSerializable;
use ReflectionException;

#[JsonObjectAttribute]
final class SubCalendar implements JsonSerializable
{
    public int $id;
    public string $name;
    public bool $active;
    #[JsonItemAttribute(key: 'created_at')]
    public int $createdAt;
    #[JsonItemAttribute(key: 'updated_at')]
    public ?int $updatedAt;

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
