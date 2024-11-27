<?php

namespace Teamup\Webhook;

use InvalidArgumentException;
use JsonException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Teamup\Webhook\Attributes\PayloadItem;
use Teamup\Webhook\Attributes\PayloadObject;

readonly class Hydrator implements HydratorInterface
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    public function hydrate(string|array $json, object|string $objOrClass): object
    {
        $jsonArr = is_string($json) ? $this->decode($json) : $json;
        $reflectionClass = new ReflectionClass($objOrClass);
        return $this->processClass($reflectionClass, $jsonArr);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    private function processClass(ReflectionClass $class, array $jsonArr): object
    {
        $instance = $class->newInstance();
        $skipAttributeCheck = ($class->getAttributes(PayloadObject::class)[0] ?? null) !== null;
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $property->setValue($instance, $this->processProperty($property, $jsonArr, $skipAttributeCheck));
        }
        return $instance;
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    private function processProperty(ReflectionProperty $property, array $jsonArr, bool $skipAttributeCheck): mixed
    {
        $attributes = $property->getAttributes(PayloadItem::class);
        $attr = $attributes[0] ?? null;
        if ($attr === null && !$skipAttributeCheck) {
            return null;
        }

        /** @var PayloadItem $item */
        $item = $attr?->newInstance() ?? new PayloadItem();
        if ($item->discriminator !== null) {
            foreach ($item->discriminator as $key => $type) {
                if (array_key_exists($key, $jsonArr)) {
                    $item->key = $key;
                    $item->type = $type;
                }
            }
        }

        $key = $item->key ?? $this->convertKey($property->getName());
        if ($item->required && !array_key_exists($key, $jsonArr)) {
            throw new InvalidArgumentException(sprintf('required item <%s> not found', $key));
        }

        if ($property->getType()?->isBuiltin()) {
            return $this->handleBuiltin($jsonArr, $key, $property, $item);
        }

        if ($jsonArr[$key] === null) {
            if ($property->getType()?->allowsNull()) {
                return null;
            }
            throw new InvalidArgumentException("{$key} should not be null");
        }

        return $this->handleCustomType($jsonArr[$key], $item->type ?? $property->getType()?->getName());
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    private function handleBuiltin(array $jsonArr, string $key, ReflectionProperty $property, PayloadItem $item): mixed
    {
        if ($item->type !== null && $property->getType()?->getName() === 'array') {
            $output = [];
            $classExists = class_exists($item->type);
            foreach ($jsonArr[$key] ?? [] as $k => $v) {
                $value = $v;
                if ($classExists) {
                    $value = $this->handleCustomType($value, $item->type);
                } elseif (gettype($v) !== $item->type) {
                    throw new LogicException(
                        sprintf('expected array with items of type <%s> but found <%s>', $item->type, gettype($v))
                    );
                }
                $output[$k] = $value;
            }
            return $output;
        }
        return $jsonArr[$key] ?? ($property->hasDefaultValue() ? $property->getDefaultValue() : null);
    }

    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    private function handleCustomType(mixed $value, string $type): mixed
    {
        $typeReflection = new ReflectionClass($type);
        if ($typeReflection->isEnum()) {
            return call_user_func($type . '::tryFrom', $value);
        }

        if ($typeReflection->getConstructor()?->getNumberOfParameters()) {
            $params = [$value];
            if (is_array($value)) {
                $params = array_values($value);
            }
            return new ($type)(...$params);
        }

        return $this->hydrate(
            $value,
            new ($type)(),
        );
    }

    /**
     * @throws JsonException
     */
    private function decode(string $json): mixed
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Map keys from pascalCase to underscore_case
     *
     */
    private function convertKey(string $key): string
    {
        $in = str_split($key);
        $out = '';
        foreach ($in as $iValue) {
            if ($iValue < 'a') {
                $out .= '_';
                // if already is an underscore, just skip case conversion
                // but still add another underscore before it
                if ($iValue !== '_') {
                    $out .= chr((ord($iValue) - ord('A')) + ord('a'));
                } else {
                    $out .= $iValue;
                }
            } else {
                $out .= $iValue;
            }
        }
        return $out;
    }

}
