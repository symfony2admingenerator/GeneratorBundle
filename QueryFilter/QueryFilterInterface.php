<?php

namespace Admingenerator\GeneratorBundle\QueryFilter;

interface QueryFilterInterface
{
    /**
     * @return mixed the query object interface depending on the ORM
     *
     * @api
     */
    public function getQuery(): mixed;

    /**
     * Add filter for Default db types (types, not found
     * by others add*Filter() methods
     *
     * @param string $field the db field
     * @param string $value the search value
     *
     * @api
     */
    public function addDefaultFilter(string $field, string $value): void;

}
