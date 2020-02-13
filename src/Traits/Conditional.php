<?php

namespace Shetabit\Extractor\Traits;

trait Conditional
{
  /**
  * Run given callback if condition goes true.
  *
  * @param $condition
  * @param callable $callback
  *
  * @return $this
  */
  public function when($condition, callable $callback)
  {
    $condition = is_callable($condition) ? $condition($this) : $condition;

    if ($condition) {
      $callback($this);
    }

    return $this;
  }

  /**
  * Run given callback if condition goes false.
  *
  * @param $condition
  * @param callable $callback
  *
  * @return $this
  */
  public function whenNot($condition, callable $callback)
  {
    $condition = is_callable($condition) ? $condition($this) : $condition;

    if (!$condition) {
      $callback($this);
    }

    return $this;
  }
}
