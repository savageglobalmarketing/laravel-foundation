<?php

namespace SavageGlobalMarketing\Foundation\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use SavageGlobalMarketing\Foundation\Traits\FoundationProperties;
use Nwidart\Modules\Routing\Controller;

abstract class FoundationController extends Controller
{
    use FoundationProperties;

    public function __construct(Model $model)
    {
        $this->model = $model;

        $this->load();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        // Run FormRequest validations
        app($this->formRequestClass);

        // Check for authorization
        $this->authorize('index', get_class($this->model));

        $paginator = app($this->services['query'])
            ->run($request->all())
            ->jsonPaginate();

        // Returns a ApiResource
        return $this->resourceCollectionResponse($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        // Run FormRequest validations
        app($this->formRequestClass);

        $this->authorize('create', get_class($this->model));

        $repo = app($this->services['create'])->run($request->only($this->fillable));

        return $this->resourceResponse($repo->toModel(), 201);
    }

    /**
     * Show the specified resource.
     *
     * @param int|string $id
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function show($id)
    {
        $model = app($this->services['get'])->run($id)->toModel();

        $this->authorize('view', $model);

        return $this->resourceResponse($model);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int|string $id
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function update(Request $request, $id)
    {
        // Run FormRequest validations
        app($this->formRequestClass);

        $repo = app($this->services['get'])->run($id);

        $this->authorize('update', $repo->toModel());

        $repo = (new $this->services['update']($repo))->run($request->only($this->fillable));

        return $this->resourceResponse($repo->toModel());
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param int|string $id
     *
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $repo = app($this->services['get'])->run($id);

        $this->authorize('delete', $repo->toModel());

        (new $this->services['destroy']($repo))->run();

        return response()->noContent(); // 204
    }
}
