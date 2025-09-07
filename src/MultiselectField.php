<?php

declare(strict_types=1);

namespace Dpsoft\NovaMultiselectFilter;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class MultiselectField extends Field
{
    public $component = 'nova-multiselect-field';

    public function fillForAction(NovaRequest $request, $model)
    {
        if ($this->isSingleSelect() or $this->getMax() === 1) {
            $value = $request->input($this->attribute);
            $model->{$this->attribute} = json_decode($value)[0] ?? null;
            return;
        }
        $model->{$this->attribute} = json_decode($request->input($this->attribute), true);
    }

    protected function getMax()
    {
        return isset($this->meta['max']) ? (int)$this->meta['max'] : null;
    }

    protected function isSingleSelect()
    {
        return isset($this->meta['singleSelect']) && $this->meta['singleSelect'] === true;
    }

    public function options($options)
    {
        return $this->withMeta(['options' => collect($options ?? [])->map(function ($label, $value) {
            return ['label' => $label, 'value' => $value];
        })->values()->all()]);
    }

    public function placeholder($placeholder)
    {
        return $this->withMeta(['placeholder' => $placeholder]);
    }

    public function max($max)
    {
        return $this->withMeta(['max' => $max]);
    }

    public function singleSelect($singleSelect = true)
    {
        return $this->withMeta(['singleSelect' => $singleSelect]);
    }

    public function optionsLimit($optionsLimit)
    {
        return $this->withMeta(['optionsLimit' => $optionsLimit]);
    }

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

    public function limit($limit)
    {
        return $this->withMeta(['limit' => (int)$limit]);
    }
}
