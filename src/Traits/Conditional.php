<?php

namespace Shetabit\Extractor\Traits;

trait Conditional
{
    /**
     * Run given callback if condition goes true.
     *
     * @param (callable(static): mixed)|mixed $condition
     * @param callable(static): mixed         $callback
     */
    public function when(mixed $condition, callable $callback) : static
    {
        if ($this->resolveCondition($condition)) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Run given callback if condition goes false.
     *
     * @param (callable(static): mixed)|mixed $condition
     * @param callable(static): mixed         $callback
     */
    public function whenNot(mixed $condition, callable $callback) : static
    {
        if (!$this->resolveCondition($condition)) {
            $callback($this);
        }

        return $this;
    }

    /**
     * A condition can be given as a value or as something that answers with one.
     */
    private function resolveCondition(mixed $condition) : mixed
    {
        return is_callable($condition) ? $condition($this) : $condition;
    }
}
