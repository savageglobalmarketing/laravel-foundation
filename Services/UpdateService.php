<?php

namespace Maxcelos\Foundation\Services;

use Maxcelos\Foundation\Contracts\FoundationContract;

abstract class UpdateService
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
        return $this->repo->update($data);
    }
}
