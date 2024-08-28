<?php
namespace Teamup\Webhook\Payload;

use Andrey\JsonHandler\Attributes\JsonItemAttribute;
use Andrey\JsonHandler\Attributes\JsonObjectAttribute;
use Andrey\JsonHandler\JsonHandler;
use JsonException;
use JsonSerializable;
use ReflectionException;

#[JsonObjectAttribute]
final class Payload implements JsonSerializable
{
    /** @var Event[] */
    #[JsonItemAttribute(type: Event::class)]
    public array $events;
    /** @var SubCalendar[] */
    #[JsonItemAttribute(key: 'sub_calendars', type: SubCalendar::class)]
    public array $subCalendars;
    public int $now;


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
