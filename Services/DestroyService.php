<?php

namespace SavageGlobalMarketing\Foundation\Services;

use SavageGlobalMarketing\Foundation\Contracts\FoundationContract;
use Illuminate\Database\Eloquent\Builder;

abstract class DestroyService
{
    protected FoundationContract $repo;

    /**
     * Service constructor.
     *
     * @param FoundationContract $repo
     */
    public function __construct(FoundationContract $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Execute service
     *
     * @return bool
     */
    public function run(): bool
    {
        return $this->repo->delete();
    }
}
