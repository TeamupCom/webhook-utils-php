<?php
namespace Teamup\Webhook\Payload;

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;
use Andrey\JsonHandler\JsonHandler;
use JsonException;
use JsonSerializable;
use ReflectionException;

#[JsonObjectAttribute]
final class Attachment implements JsonSerializable
{
    public string $id;
    public ?string $name;
    public ?int $size;
    public ?string $mimetype;
    public ?string $preview;
    public ?string $thumbnail;
    public ?string $link;
    #[JsonItemAttribute(key: 'upload_dt')]
    public ?string $uploadDt;

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     * @throws JsonException
     */
    public function jsonSerialize(): array
    {
        return (new JsonHandler())->serialize($this);
    }
}
