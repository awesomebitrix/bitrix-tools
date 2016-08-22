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

namespace SomeWork\Sale\Import;


use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Sale\Location\Util\Process as BitrixProcess;
use SomeWork\Exception\InvalidArgumentException;
use SomeWork\Exception\ServerConfigException;

abstract class Process
{
    protected $arOptions = [];

    /**
     * Process constructor.
     * @throws \Bitrix\Main\LoaderException
     * @throws \SomeWork\Exception\ServerConfigException
     */
    public function __construct()
    {
        $this->loadModule();
        $this->serverConfig();
    }

    /**
     * @throws LoaderException
     */
    protected function loadModule()
    {
        if (!Loader::includeModule('sale')) {
            throw new LoaderException('Cant load module "sale"');
        }
    }

    /**
     * @throws \SomeWork\Exception\ServerConfigException
     */
    protected function serverConfig()
    {
        if (!set_time_limit(0)) {
            throw new ServerConfigException('Cant change time limit');
        }

        if (!ini_set('memory_limit', '1024M')) {
            throw new ServerConfigException('Cant change memory limit');
        }
    }

    /**
     * @param int $time
     *
     * @return $this
     */
    public function setInitialTime($time)
    {
        $this->arOptions['INITIAL_TIME'] = (int)$time;
        return $this;
    }

    /**
     * @param bool $useLock
     *
     * @return $this
     */
    public function setUseLock($useLock = true)
    {
        $this->arOptions['USE_LOCK'] = (bool)$useLock;
        return $this;
    }

    public function getTimeLimit()
    {
        $timeLimit = $this->arOptions['TIME_LIMIT'] ?: BitrixProcess::MIN_TIME_LIMIT;
        $this->setTimeLimit($timeLimit);
        return $timeLimit;
    }

    /**
     * @param int $timeLimit
     *
     * @return $this
     * @throws \SomeWork\Exception\InvalidArgumentException
     */
    public function setTimeLimit($timeLimit)
    {
        $timeLimit = (int)$timeLimit;
        if ($timeLimit < BitrixProcess::MIN_TIME_LIMIT) {
            throw new InvalidArgumentException('Time Limit');
        }
        $this->arOptions['TIME_LIMIT'] = $timeLimit;

        return $this;
    }
}