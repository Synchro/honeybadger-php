<?php

namespace Honeybadger;

/**
 * The ArgumentValueNormalizer "normalizes" backtrace args for improved serialisation:
 * - To limit size, the number of keys included in an array is limited.
 * - Additionally, the nesting level is limited. Higher levels of nesting will return "..." for non-primitives.
 * - Arrays and objects are serialised properly at each level.
 *
 * The normalized values can then be JSON encoded as usual.
 */
class ArgumentValueNormalizer
{
    protected const MAX_KEYS_IN_ARRAY = 50;
    protected const MAX_DEPTH = 10;

    public static function normalize($value, int $currentDepth = 0)
    {
        switch (gettype($value)) {
            case 'array':
                if ($currentDepth > static::MAX_DEPTH) {
                    $n = count($value);
                    $items = $n > 1 ? 'items' : 'item';
                    return "Array($n $items)";
                }

                return static::normalizeArray($value, $currentDepth);

            case 'object':
                return static::normalizeObject($value);

            default:
                return $value;
        }
    }

    protected static function normalizeArray(array $array, int $currentDepth = 0): array
    {
        $normalized = [];
        $keyCount = 0;
        foreach ($array as $key => $item) {
            $keyCount++;
            if ($keyCount > static::MAX_KEYS_IN_ARRAY) {
                break;
            }
            $normalized[$key] = static::normalize($item, $currentDepth + 1);
        }
        return $normalized;
    }

    /**
     * Currently, objects are normalized to the class name. This isn't ideal (see GH issue 133),
     * but we can change it at any time.
     */
    protected static function normalizeObject(object $object)
    {
        return get_class($object);
    }
}
