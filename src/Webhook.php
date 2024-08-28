<?php

namespace Teamup\Webhook;

use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\RequestInterface;
use ReflectionException;
use Teamup\Webhook\Exception\InvalidSignatureException;

class Webhook
{
    /** @var array<Trigger, HandlerInterface[]> */
    private array $handlers = [];

    public function __construct(private readonly Parser $parser)
    { }

    public function registerHandler(Trigger $trigger, HandlerInterface $handler): void
    {
        if (!isset($this->handlers[$trigger->value])) {
            $this->handlers[$trigger->value] = [];
        }
        $this->handlers[$trigger->value][] = $handler;
    }

    /**
     * @throws ReflectionException
     * @throws InvalidSignatureException
     * @throws JsonException
     */
    public function handle(RequestInterface $request): void
    {
        $payload = $this->parser->extract($request);
        foreach ($payload->events as $event) {
            $handlers = [ ...($this->handlers[$event->trigger->value] ?? []), ...($this->handlers[Trigger::Any->value] ?? []) ];
            if (count($handlers) === 0) {
                throw new InvalidArgumentException(sprintf('no handler registered for trigger \'%s\'', $event->trigger->value));
            }

            foreach ($handlers as $handler) {
                $handler($request, $event, $payload);
            }
        }
    }
}
