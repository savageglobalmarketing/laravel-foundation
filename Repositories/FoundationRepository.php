<?php

namespace SavageGlobalMarketing\Foundation\Repositories;

use SavageGlobalMarketing\Foundation\Contracts\FoundationContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use SavageGlobalMarketing\Foundation\Utils\RepoUtils;

abstract class FoundationRepository implements FoundationContract
{
    use RepoUtils;

    protected Model $model;

    protected string $primaryKey;

    protected bool $withTrashed;

    public function __construct(Model $model, string $primaryKey = 'id')
    {
        $this->model = $model;
        $this->primaryKey = $primaryKey;
    }

    /**
     * @param array $data
     *
     * @return FoundationContract
     */
    public function make(array $data): FoundationContract
    {
        $data = array_filter($data, function ($key) {
            return in_array($key, $this->model->getFillable());
        }, ARRAY_FILTER_USE_KEY);

        $this->model->fill($data);

        $this->model->save();

        return $this;
    }

    /**
     * @param array $newData
     *
     * @return FoundationContract
     */
    public function update(array $newData): FoundationContract
    {
        $this->model->update($newData);

        return $this;
    }

    /**
     * Get first model by ID or Uuid.
     *
     * @param $id
     *
     * @return mixed
     */
    public function getById($id)
    {
        if ($this->hasUuid($this->model)) {
            $modelFound = $this->model->whereUuid($id);
        } else {
            $modelFound = $this->model->where($this->primaryKey, $id);
        }

        if ($this->withTrashed) {
            $modelFound = $modelFound->withTrashed();
        }

        return $modelFound->first();
    }

    /**
     * @param mixed $id
     * @param bool  $withTrashed
     *
     * @return FoundationContract
     */
    public function get($id, bool $withTrashed = false): self
    {
        $this->withTrashed = $withTrashed;

        $modelFound = $this->getById($id);

        if (!$modelFound) {
            abort(404, 'Resource Not Found');
        }

        $this->model = $modelFound;

        return $this;
    }

    /**
     * @param bool $force
     *
     * @return bool
     */
    public function delete($force = false): bool
    {
        if ($force) {
            return $this->model->forceDelete();
        }

        return $this->model->delete();
    }

    /**
     * @return Model
     */
    public function toModel(): Model
    {
        return $this->model;
    }

    /**
     * Returs model as array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->model->toArray();
    }

    /**
     * Performs filtering, pagination and sorting.
     *
     * @param array           $parms
     *
     * @return EloquentBuilder|ScoutBuilder
     */
    public function builder(array $parms)
    {
        $builder = $this->arrangeFilters($this->model::query(), $parms['filters'] ?? []);

        if (isset($parms['search_term'])) {
            $class = get_class($this->model);

            $builder = $class::search($parms['search_term'])
                ->constrain($builder);
        }

        if (isset($parms['sort']) && isset($parms['sort']['by'])) {
            $direction = $parms['sort']['direction'] ?? 'asc';

            if ($this->isRelation($parms['sort']['by'])) {
                $builder = $this->belongsToOrdering($builder, $parms['sort']['by'], $direction);

                // Avoid merge fields with join
                $builder = $builder->select($this->model->getTable() . '.*');
            } else {
                $builder = $builder->orderBy($parms['sort']['by'], $direction);
            }
        }

        return $builder;
    }
}
