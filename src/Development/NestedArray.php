<?php

namespace LucaCracco\RoboDrupal\Development;

/**
 * Class NestedArray.
 *
 * @package SpoonsPlugin
 */
class NestedArray {

    /**
     * Deeply merges arrays. Borrowed from drupal.org/project/core.
     *
     * @return array
     *   The merged array.
     */
    public static function mergeDeep(): array {
        return self::mergeDeepArray(func_get_args());
    }

    /**
     * Deeply merges arrays. Borrowed from drupal.org/project/core.
     *
     * @param array $arrays
     *   An array of array that will be merged.
     * @param bool $preserve_integer_keys
     *   Whether to preserve integer keys.
     *
     * @return array
     *   The merged array.
     */
    public static function mergeDeepArray(array $arrays, $preserve_integer_keys = FALSE): array {
        $result = [];
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_int($key) && !$preserve_integer_keys) {
                    $result[] = $value;
                }
                /** @noinspection NotOptimalIfConditionsInspection */
                elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                    $result[$key] = self::mergeDeepArray([$result[$key], $value], $preserve_integer_keys);
                }
                else {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

}
