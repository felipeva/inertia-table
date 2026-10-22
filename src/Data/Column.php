<?php

namespace FelipeVa\InertiaTable\Data;

use FelipeVa\InertiaTable\Contracts\TableComponent;
use FelipeVa\InertiaTable\Traits\Makeable;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class Column extends Data implements TableComponent
{
    use Makeable;

    public function __construct(
        public string $name,
        public string $label,
        public bool|string $sorted = false,
        public bool $hidden = false,
        public bool $sortable = false,
    ) {}

    public function sortable(bool $value = true): self
    {
        $this->sortable = $value;

        return $this;
    }

    public function hidden(bool $value = true): self
    {
        $this->hidden = $value;

        return $this;
    }
}
