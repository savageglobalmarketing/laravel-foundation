<?php

namespace SavageGlobalMarketing\Foundation\Services;

use SavageGlobalMarketing\Foundation\Contracts\FoundationContract;

abstract class GetService
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
     * @param int|string $id
     *
     * @return FoundationContract
     */
    public function run($id): FoundationContract
    {
        return $this->repo->get($id);
    }
}
