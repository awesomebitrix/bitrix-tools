<?php
/**
 * Copyright 2016 Igor Pinchuk
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 20.08.2016
 * Time: 17:33
 */

namespace SomeWork\Sale\Import;


use Bitrix\Main\Loader;
use SomeWork\Exception\LoadModuleException;
use SomeWork\Exception\ServerConfigException;

class LocationImport
{
    public function __construct()
    {
        $this->loadModule();
        $this->serverConfig();
    }

    protected function loadModule()
    {
        if (!Loader::includeModule('sale')) {
            throw new LoadModuleException('sale');
        }
    }

    protected function serverConfig()
    {
        if (!set_time_limit(0)) {
            throw new ServerConfigException('Cant disable time limit');
        }

        if (!ini_set('memory_limit', '1024M')) {
            throw new ServerConfigException('Cant set new memory limit');
        }
    }
}