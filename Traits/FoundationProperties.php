<?php

namespace SavageGlobalMarketing\Foundation\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait FoundationProperties
{
    protected Model $model;

    protected array $fillable = [];

    protected string $resourceClass = '';

    protected string $formRequestClass = '';

    protected array $services = [];

    /**
     * Load properties
     */
    private function load()
    {
        $cacheKey = str_replace('\\', '_', get_class($this)) . '_properties';

        $cache = Cache::rememberForever($cacheKey, function () {
            return [
                'resourceDiscover' => $this->resourceDiscover(),
                'fillableDiscover' => $this->fillableDiscover(),
                'formRequestDiscover' => $this->formRequestDiscover(),
                'services' => $this->servicesDiscover(),
            ];
        });

        $this->resourceClass = $cache['resourceDiscover'];
        $this->fillable = $cache['fillableDiscover'];
        $this->formRequestClass = $cache['formRequestDiscover'];
        $this->services = $cache['services'];
    }

    /**
     * Find default services based on model name
     */
    private function servicesDiscover()
    {
        $actions = ['query', 'create', 'get', 'update', 'destroy'];

        $services = [];

        foreach ($actions as $action) {
            if (isset($this->services[$action]))
                continue;

            $actionClass = str_replace('Models', 'Services', get_class($this->model));
            $actionClass .= '\\' . Str::studly($action) . class_basename($this->model) . 'Service';

            if (class_exists($actionClass))
                $services[$action] = $actionClass;
        }

        return $services;
    }

    /**
     * Find default resource based on model name
     */
    private function resourceDiscover()
    {
        if ($this->resourceClass)
            return $this->resourceClass;

        $resourceClass = str_replace('Models', 'Transformers', get_class($this->model)) . 'Resource';

        return class_exists($resourceClass) ? $resourceClass : null;

    }

    /**
     * Find default form request based on model name
     */
    private function formRequestDiscover()
    {
        if ($this->formRequestClass)
            return $this->formRequestClass;

        $requestClass = str_replace('Models', 'Http\\Requests', get_class($this->model)) . 'Request';

        return class_exists($requestClass) ? $requestClass : null;
    }

    /**
     * Find default fillable based on model name
     */
    private function fillableDiscover()
    {
        return empty($this->fillable) ? $this->model->getFillable() : $this->fillable;
    }

    /**
     * @param $model
     * @param int $code
     *
     * @return JsonResponse
     */
    private function resourceResponse($model, $code = 200)
    {
        return (new $this->resourceClass($model))
            ->response()
            ->setStatusCode($code);
    }

    /**
     * Returns a Api collection Resource
     *
     * @param $paginator
     * @param int $code
     *
     * @return JsonResponse
     */
    private function resourceCollectionResponse($paginator, $code = 200)
    {
        return $this->resourceClass::collection($paginator)
            ->response()
            ->setStatusCode($code);
    }
}
