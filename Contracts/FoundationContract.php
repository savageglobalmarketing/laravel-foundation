<?php

namespace SavageGlobalMarketing\Foundation\Contracts;

use Illuminate\Database\Eloquent\Model;

interface FoundationContract
{
    public function builder(array $parms);

    public function make(array $data): self;

    public function update(array $newData): self;

    public function getById($id): Model;

    public function get($id, bool $withTrashed = false): self;

    public function delete(bool $force = false): bool;

    public function toModel(): Model;

    public function toArray(): array;
}
