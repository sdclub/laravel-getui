<?php 
namespace Sdclub\IGeTui\Facades;

use Illuminate\Support\Facades\Facade;

class IGeTui extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'igetui';
    }

}
