<?php

namespace GraphQL\SchemaObject;

use GraphQL\InlineFragment;

/**
 * Class UnionObject
 *
 * @package GraphQL\SchemaObject
 */
abstract class UnionObject extends QueryObject
{
    protected function addPossibleType(QueryObject $possibleType)
    {
        $fragment = new InlineFragment($possibleType::OBJECT_NAME, $possibleType);
        $this->selectField($fragment);
    }
}