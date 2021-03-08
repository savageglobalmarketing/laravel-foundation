<?php

namespace SavageGlobalMarketing\Foundation\Traits;

use Illuminate\Validation\Rule;

trait ValidPagination
{
    /**
     * Get the validation rules that applies to GET request.
     *
     * @return array
     */
    private function getRules()
    {
        return [
            'filters'        => 'array',
            'filters.*'      => 'array',
            'sort'           => 'array',
            'sort.direction' => 'in:asc,desc',
            'sort.by'        => Rule::in($this->getFillable()),
            'search_term'    => 'string',
            'page'           => 'array',
            'page.number'    => 'numeric',
            'page.size'      => 'numeric',
        ];
    }
}
