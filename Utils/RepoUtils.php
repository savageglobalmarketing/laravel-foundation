<?php


namespace Maxcelos\Foundation\Utils;


use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

trait RepoUtils
{
    /**
     * Checks if model uses Dyrynda\Database\Support\GeneratesUuid trait.
     *
     * @return bool
     */
    public function hasUuid($model)
    {
        $modelTraits = class_uses($model);

        return in_array('Dyrynda\Database\Support\GeneratesUuid', $modelTraits);
    }

    /**
     * Arrange order by relationship fields
     *
     * @param $builder
     * @param $sortBy
     * @param $direction
     *
     * @return EloquentBuilder
     */
    public function belongsToOrdering($builder, $sortBy, $direction): EloquentBuilder
    {
        [$relation, $field] = explode('.', $sortBy);

        $parent = $this->model->$relation()->getParent();
        $related = $this->model->$relation()->getRelated();
        $fk = $this->model->$relation()->getForeignKeyName();
        $pk = $this->model->$relation()->getOwnerKeyName();

        return $builder->join(
            $related->getTable(),
            $parent->getTable() . '.' . $fk,
            $related->getTable() . '.' . $pk
        )->orderBy(
            $related->getTable() . '.' . $field,
            $direction
        );
    }



    /**
     * Manage filters.
     *
     * @param EloquentBuilder $modelSearch
     * @param array           $filters
     *
     * @return EloquentBuilder
     */
    public function arrangeFilters(EloquentBuilder $modelSearch, array $filters = []): EloquentBuilder
    {
        foreach ($filters as $name => $values) {
            $values = $this->castValues($name, $values);

            if ($this->isRelation($name)) {
                [$relation, $field] = explode('.', $name);

                $related = $this->model->$relation()->getRelated();

                $modelSearch = $modelSearch->orWhereHas($relation, function ($query) use ($relation, $field, $values, $related) {
                    $query->where(function ($query) use ($values, $relation, $field, $related) {

                        $casts = $related->getCasts();
                        foreach ($values as $value) {
                            $value = $this->castValue($field, $value, $casts, $related);
                            $field = $field != 'uuid' ? $field : 'id';
                            $query->orWhere($field, 'like', '%'.$value.'%');
                        }
                    });
                });
            } else {
                $modelSearch = $modelSearch->where(function ($query) use ($name, $values) {
                    foreach ($values as $value) {
                        $query->orWhere($name, 'like', '%'.$value.'%');
                    }
                });
            }
        }

        return $modelSearch;
    }

    /**
     * Check if filter is a model relationship.
     *
     * @param $filter
     * @return bool
     */
    public function isRelation($filter): bool
    {
        return count(explode('.', $filter)) > 1;
    }

    /**
     * Run casting in all filter values.
     *
     * @param string $name
     * @param array  $values
     *
     * @return array
     */
    public function castValues(string $name, array $values): array
    {
        $casts = $this->model->getCasts();

        return array_map(function ($item) use ($name, $casts) {
            return $this->castValue($name, $item, $casts);
        }, $values);
    }

    /**
     * Cast a filter value.
     *
     * @param string $name
     * @param string $value
     * @param array $casts
     * @param Model $model
     *
     * @return mixed|string
     */
    public function castValue(string $name, string $value, array $casts, $model = null)
    {
        if (! $model) {
            $model = $this->model;
        }

        if (($casts[$name] ?? null) === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if (($casts[$name] ?? null) === EfficientUuid::class) {
            return optional($model->whereUuid($value)->first())->id;
        }

        return $value;
    }
}
