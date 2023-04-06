<?php

namespace Biigle\Tests\Modules\AuthLSLogin;

use Biigle\Modules\AuthLSLogin\LSLoginServiceProvider;
use TestCase;

class LSLoginServiceProviderTest extends TestCase
{
    public function testServiceProvider()
    {
        $this->assertTrue(class_exists(LSLoginServiceProvider::class));
    }
}
