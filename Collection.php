<?php

class Collection {

    /**
     * @var array
     */
    private array $values = [];

    public function __construct(){}

    public function hasAll(array $keys): bool {
        if (empty($this->values)) return false;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->values)) {
                return false;
            }
        }
        return true;
    }

    public function hasAny(array $keys): bool {
        if (empty($this->values)) return false;
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->values)) {
                return true;
            }
        }
        return false;
    }

    public function first(int $amount) {
        if ($amount < 0) {
            return $this->last($amount * -1);
        }

        $amount = min(count($this->values), $amount);
        $iter = new ArrayIterator($this->values);

        return iterator_to_array(new LimitIterator($iter, 0, $amount));
    }

    public function firstKey(int $amount) {
        if ($amount < 0) {
            return $this->lastKey($amount * -1);
        }

        $amount = min(count($this->values), $amount);
        $keys = array_slice(array_keys($this->values), 0, $amount);

        return $amount === 1 ? $keys[0] : $keys;
    }

    public function last($amount) {
        $arr = $this->values;
        if ($amount < 0) {
            return $this->first($amount * -1);
        }

        if (!$amount) {
            return [];
        }

        return array_slice($arr, -$amount);
    }

    public function lastKey(int $amount) {
        $arr = array_keys($this->values);
        if ($amount < 0) {
            return $this->firstKey($amount * -1);
        }

        if (!$amount) {
            return [];
        }

        return array_slice($arr, -$amount);
    }

    public function keyAt(int $index) {
        $index = floor($index);
        $arr = array_keys($this->values);

        if (array_key_exists($index, $arr)) {
            return $arr[$index];
        }

        return null;
    }

    public function randomKey(int $amount) {
        $arr = array_keys($this->values);
        if (empty($arr) || !$amount) {
            return [];
        }

        $randomElements = [];
        for ($i = 0; $i < min($amount, count($arr)); $i++) {
            $randomIndex = array_rand($arr);
            $randomElements[] = array_splice($arr, $randomIndex, 1)[0];
        }

        return $randomElements;
    }

    public function every($fn, $thisArg = null): bool {
        if (!is_callable($fn)) {
            throw new \TypeError("$fn is not a function");
        }

        if ($thisArg !== null) {
            $fn = $fn(...)->bindTo($thisArg);
        }

        foreach ($this->values as $key => $val) {
            if (!$fn($val, $key, $this->values)) {
                return false;
            }
        }

        return true;
    }

    public function sort($compareFunction = null): ?array {
        $entries = $this->values;

        if ($compareFunction === null) {
            asort($entries);
        } else {
            uasort($entries, function ($a, $b) use ($compareFunction) {
                return $compareFunction($a, $b);
            });
        }

        $this->values = $entries;

        return $this->values;
    }

    public function intersection(Collection $other): self {
        $intersection = new self();

        foreach ($this->values as $key => $value) {
            if ($other->has($key) && $other->get($key) === $value) {
                $intersection->set($key, $value);
            }
        }

        return $intersection;
    }

    public function difference(Collection $other): self {
        $diffCollection = new self();

        foreach ($this->values as $key => $value) {
            if (!$other->has($key)) {
                $diffCollection->set($key, $value);
            }
        }

        return $diffCollection;
    }

    public function symmetricDifference(Collection $other): self {
        $symmetricDiffCollection = new self();

        foreach ($this->values as $key => $value) {
            if ($other->has($key)) {
                $other->unset($key);
            } else {
                $symmetricDiffCollection->set($key, $value);
            }
        }

        foreach ($other->values as $key => $value) {
            $symmetricDiffCollection->set($key, $value);
        }

        return $symmetricDiffCollection;
    }

    public function reduce(callable $fn, $initialValue = 0){
        $accumulator = $initialValue;

        foreach ($this->values as $key => $value) {
            $accumulator = $fn($accumulator, $value, $key, $this);
        }

        return $accumulator;
    }

    public function reverse(): self {
        $reversedCollection = new self();
        $reversedCollection->values = array_reverse($this->values);

        return $reversedCollection;
    }

    public function has($key): bool {
        return array_key_exists($key, $this->values);
    }

    public function get($key): mixed {
        return $this->values[$key] ?? null;
    }

    public function set($key, $value): void {
        $this->values[$key] = $value;
    }

    public function unset($key): void {
        unset($this->values[$key]);
    }

    public function __toJSON(): array {
        return $this->values;
    }
}