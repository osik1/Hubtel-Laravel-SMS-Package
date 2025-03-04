<?php

namespace Osik\HubtelLaravelSms\Facades;

use Illuminate\Support\Facades\Facade;

class HubtelSms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hubtel-sms';
    }
}