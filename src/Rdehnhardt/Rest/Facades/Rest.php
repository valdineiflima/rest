<?php

namespace Rdehnhardt\Rest\Facades;

use Illuminate\Support\Facades\Facade;

class Rest extends Facade {

    protected static function getFacadeAccessor() {
        return 'rest';
    }

}