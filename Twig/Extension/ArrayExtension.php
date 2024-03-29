<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Stéphane Escandell
 */
class ArrayExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        $options = ['is_safe' => ['html']];
        return [
            'mapBy'     => new TwigFilter('mapBy', $this->mapBy(...), $options),
            'flatten'   => new TwigFilter('flatten', $this->flatten(...), $options),
            'intersect' => new TwigFilter('intersect', $this->intersect(...), $options),
            'clean'     => new TwigFilter('clean', $this->clean(...), $options),
            'unique'    => new TwigFilter('unique', array_unique(...), $options),
        ];
    }

    /**
     * Map collection by key. For objects, return the property, use
     * get method or is method, if available.
     *
     * @param  array                     $input Array of arrays or objects.
     * @param  string                    $key   Key to map by.
     * @throws \InvalidArgumentException If array item is not an array or object.
     * @throws \LogicException           If array item could not be mapped by given key.
     * @return array                     Mapped array.
     */
    public function mapBy(array $input, string $key): array
    {
        return array_map(function ($item) use ($key) {
            if (is_array($item)) {
                if (!array_key_exists($key, $item)) {
                    throw new \LogicException("Could not map item by key \"$key\". Array key does not exist.");
                }

                return $item[$key];
            }

            // TODO: use PropertyAccessor ??
            if (!is_object($item)) {
                throw new \InvalidArgumentException("Item must be an array or object.");
            }

            $ref = new \ReflectionClass($item);

            if ($ref->hasProperty($key) && $ref->getProperty($key)->isPublic()) {
                return $item->$key;
            }

            if ($ref->hasMethod($key) && !$ref->getMethod($key)->isPrivate()) {
                return $item->$key();
            }

            $get = 'get'.ucfirst($key);
            if ($ref->hasMethod($get) && !$ref->getMethod($get)->isPrivate()) {
                return $item->$get();
            }

            $is = 'is'.ucfirst($key);
            if ($ref->hasMethod($is) && !$ref->getMethod($is)->isPrivate()) {
                return $item->$is();
            }

            throw new \LogicException("Could not map item by key \"$key\". Cannot access the property directly or through getter/is method.");

        }, $input);
    }

    /**
     * Flatten nested arrays.
     *
     * @param  array $input Array of arrays.
     * @return array Flat array.
     */
    public function flatten(array $input): array
    {
        $it = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($input)
        );

        return iterator_to_array($it, false);
    }

    /**
     * Computes the intersection of arrays
     */
    public function intersect(): array
    {
        return call_user_func_array('array_intersect', func_get_args());
    }

    /**
     * Remove entries with value $car
     */
    public function clean(array $input, string $car = ''): array
    {
        return array_filter($input, function($v) use ($car) {
            return $car != $v;
        });
    }

    public function getName(): string
    {
        return 'admingenerator_array';
    }
}
