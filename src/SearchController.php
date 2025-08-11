<?php declare(strict_types=1);

namespace Dpsoft\NovaMultiselectFilter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $modelClass = (string) $request->get('model');
        $column = (string) $request->get('column', 'name');
        $term = (string) $request->get('search', '');
        if ($modelClass === '' || $term === '') {
            return \response()->json([]);
        }
        if (!class_exists($modelClass)) {
            return \response()->json([]);
        }
        $model = new $modelClass();
        if (!$model instanceof Model) {
            return \response()->json([]);
        }
        if (!Schema::hasColumn($model->getTable(), $column)) {
            return \response()->json([]);
        }
        $query = $modelClass::query()->latest();
        $query->where($column, 'LIKE', '%'.$term.'%');
        $results = $query->limit(50)->get();
        $options = $results->map(function ($row) use ($column) {
            return [
                'label' => (string) $row->{$column},
                'value' => (string) $row->getKey(),
            ];
        })->values();
        return \response()->json($options);
    }
}


