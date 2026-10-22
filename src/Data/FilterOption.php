<?php

namespace FelipeVa\InertiaTable\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class FilterOption extends Data
{
    public function __construct(
        public string $value,
        public string $label,
    ) {}
}
