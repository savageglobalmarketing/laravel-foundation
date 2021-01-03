<?php

namespace SavageGlobalMarketing\Foundation;

class Fillable
{
    protected string $fillable;

    public function __construct($fillable = '')
    {
        $this->fillable = $fillable;
    }

    /**
     * Converts fillable to array.
     *
     * @return array
     */
    public function getFillableArray(): array
    {
        $fields = explode(',', $this->fillable);
        $fields = array_merge($fields, config('foundation.default_model_fields') ?? []);

        $array = [];
        foreach ($fields as $field) {
            $schema = explode(':', $field);

            if (isset($schema[1])) {
                $array[$schema[1]] = $schema[0];
            } else {
                $array[$schema[0]] = 'string';
            }
        }

        return $array;
    }

    /**
     * Fillable as text array.
     *
     * @return string
     */
    public function getFillablePlain($withoutTenantID = true): string
    {
        $arrays = $this->getFillableArray();

        if ($withoutTenantID) {
            unset($arrays['tenant_id']);
        }

        $fillable = json_encode(array_keys($arrays));

        $fillable = str_replace('"', "'", $fillable);

        return str_replace(',', ', ', $fillable);
    }

    /**
     * Fillable as text array.
     *
     * @return string
     */
    public function getReverseFillablePlain($withoutTenantID = true): string
    {
        $fillableArray = $this->getFillableArray();

        if ($withoutTenantID) {
            unset($fillableArray['tenant_id']);
        }

        $fillable = array_map(function ($type, $name) {
            return $name.':'.$type;
        }, $fillableArray, array_keys($fillableArray));

        return implode(',', $fillable);
    }

    /**
     * Fillable as text array keys.
     *
     * @return string
     */
    public function getFillableEmpty(): string
    {
        $fields = $this->getFillableArray();

        $fillable = '['.PHP_EOL;

        foreach ($fields as $name => $field) {
            $fillable .= "\t\t\t'".$name."' => '',".PHP_EOL;
        }

        $fillable .= "\t\t]";

        return $fillable;
    }

    /**
     * Fillable in API Resource format.
     *
     * @return string
     */
    public function getFillableForResource(): string
    {
        $fillable = '['.PHP_EOL;
        $fillable .= "\t\t\t'".'uuid\' => $this->uuid,'.PHP_EOL;

        $fields = $this->getFillableArray();
        foreach ($fields as $name => $field) {
            $fillable .= "\t\t\t'".$name.'\' => $this->'.$name.','.PHP_EOL;
        }

        $fillable .= "\t\t\t'".'created_at\' => $this->created_at,'.PHP_EOL;
        $fillable .= "\t\t\t'".'updated_at\' => $this->updated_at,'.PHP_EOL;
        $fillable .= "\t\t\t'".'deleted_at\' => $this->deleted_at,'.PHP_EOL;

        $fillable .= "\t\t\t'".'creator\' => UserStampsResource::make($this->creator),'.PHP_EOL;
        $fillable .= "\t\t\t'".'editor\' => UserStampsResource::make($this->editor),'.PHP_EOL;
        $fillable .= "\t\t\t'".'destroyer\' => UserStampsResource::make($this->destroyer),'.PHP_EOL;

        $fillable .= "\t\t]";

        return $fillable;
    }
}
