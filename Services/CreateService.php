<?php

namespace SavageGlobalMarketing\Foundation\Services;

use SavageGlobalMarketing\Foundation\Contracts\FoundationContract;

abstract class CreateService
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
     * @param array $data
     *
     * @return FoundationContract
     */
    public function run(array $data): FoundationContract
    {
        return $this->repo->make($data);
    }
}
