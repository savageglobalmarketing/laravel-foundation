<?php

namespace SavageGlobalMarketing\Foundation\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueOnTenant implements Rule
{
    protected $model;

    /**
     * Create a new rule instance.
     *
     * @param $modelClass
     */
    public function __construct($modelClass)
    {
        $this->model = new $modelClass;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            if (auth()->check() && auth()->user()->currentTennat()->id)
            return $this->model::where($attribute, $value)
                ->where('tenant_id', auth()->user()->currentTennat()->id)
                    ->first() ? true : false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute has already been taken.';
    }
}
