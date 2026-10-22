<?php

namespace FelipeVa\InertiaTable\Data;

use FelipeVa\InertiaTable\Contracts\TableComponent;
use FelipeVa\InertiaTable\Enums\FilterType;
use FelipeVa\InertiaTable\Traits\Makeable;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class Filter extends Data implements TableComponent
{
    use Makeable;

    /**
     * @param  DataCollection<int, FilterOption>|null  $options
     */
    public function __construct(
        public string $name,
        public string $label,

        #[DataCollectionOf(FilterOption::class)]
        public ?DataCollection $options = null,

        public FilterType $type = FilterType::SELECT,
        public ?string $value = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return $this
     */
    public function options(array $options): self
    {
        $this->options = FilterOption::collect($options, DataCollection::class);

        return $this;
    }

    /**
     * @return $this
     */
    public function type(FilterType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return $this
     */
    public function value(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
