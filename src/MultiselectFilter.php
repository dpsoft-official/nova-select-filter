<?php declare(strict_types=1);


namespace Dpsoft\NovaMultiselectFilter;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

abstract class MultiselectFilter extends Filter
{
    public $component = 'nova-multiselect-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param Request $request
     * @param Builder $query
     * @param $value
     * @return Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query;
    }

    /**
     * Get the filter's options.
     *
     * @param Request $request
     * @return array|callable
     */
    public function options(Request $request)
    {
        return [];
    }

    /**
     * Sets the placeholder value displayed on the field.
     *
     * @param $placeholder
     * @return MultiselectFilter
     */
    public function placeholder($placeholder)
    {
        return $this->withMeta(['placeholder' => $placeholder]);
    }

    /**
     * Sets the max number of options the user can select.
     *
     * @param $placeholder
     * @return MultiselectFilter
     */
    public function max($max)
    {
        return $this->withMeta(['max' => $max]);
    }

    /**
     * Enables the field to be used as a single select.
     *
     * This forces the value saved to be a single value and not an array.
     *
     * @param bool $singleSelect
     * @return MultiselectFilter
     **/
    public function singleSelect($singleSelect = true)
    {
        return $this->withMeta(['singleSelect' => $singleSelect]);
    }

    /**
     * Sets the maximum number of options displayed at once.
     *
     * @param $optionsLimit
     * @return MultiselectFilter
     */
    public function optionsLimit($optionsLimit)
    {
        return $this->withMeta(['optionsLimit' => $optionsLimit]);
    }

    /**
     * Enables vue-multiselect's group-select feature which allows the
     * user to select the whole group at once.
     *
     * @param bool $groupSelect
     * @return MultiselectFilter
     */
    public function groupSelect($groupSelect = true)
    {
        return $this->withMeta(['groupSelect' => $groupSelect]);
    }

    public function ajaxEndpoint($endpoint)
    {
        return $this->withMeta(['ajaxEndpoint' => $endpoint]);
    }

    public function ajaxMethod($method = 'get')
    {
        return $this->withMeta(['ajaxMethod' => strtolower((string)$method)]);
    }

    public function ajaxParam($param = 'search')
    {
        return $this->withMeta(['ajaxParam' => $param]);
    }

    public function debounce($milliseconds = 300)
    {
        return $this->withMeta(['debounce' => (int)$milliseconds]);
    }

    public function minChars($count = 0)
    {
        return $this->withMeta(['minChars' => (int)$count]);
    }

    public function model($model)
    {
        return $this->withMeta(['model' => (string)$model]);
    }

    public function searchColumn($column)
    {
        return $this->withMeta(['searchColumn' => (string)$column]);
    }

    /**
     * Formats the options available for select.
     *
     * @param $container
     * @param $request
     * @return array
     */
    public function getFormattedOptions($container, $request)
    {
        $options = $this->options($container->make($request));
        $options = \collect($options ?? []);

        $isOptionGroup = $options->contains(function ($label, $value) {
            return is_array($label);
        });

        if ($isOptionGroup) {
            return $options
                ->map(function ($value, $key) {
                    return collect($value + ['value' => $key]);
                })
                ->groupBy('group')
                ->map(function ($value, $key) {
                    return ['label' => $key, 'values' => $value->map->only(['label', 'value'])->toArray()];
                })
                ->values()
                ->toArray();
        }

        return $options->map(function ($label, $value) {
            return ['label' => $label, 'value' => $value];
        })->values()->all();
    }

    /**
     * Prepare the filter for JSON serialization.
     *
     * @return array
     */
    public
    function jsonSerialize()
    {
        return array_merge([
            'class' => $this->key(),
            'name' => $this->name(),
            'component' => $this->component(),
            'options' => $this->getFormattedOptions(Container::getInstance(), Request::class),
            'currentValue' => $this->default() ?? '',
        ], $this->meta());
    }
}
