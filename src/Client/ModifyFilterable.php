<?php

namespace Marcz\Search\Client;

interface ModifyFilterable
{
    public function apply($key, $value);
}
