<?php

namespace SavageGlobalMarketing\Foundation\Services;

use SavageGlobalMarketing\Foundation\Contracts\FoundationContract;

abstract class QueryService
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
     * @param array $parms
     */
    public function run(array $parms)
    {
        return $this->repo->builder($parms);
    }
}
