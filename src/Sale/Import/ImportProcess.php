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


use Bitrix\Main\Application;use Bitrix\Sale\Location\Import\ImportProcess as BitrixImportProcess;use SomeWork\Exception\ImportException;

class ImportProcess extends Process
{
    const FILE_TYPE = [
        'text/plain',
        'text/csv',
        'application/vnd.ms-excel',
        'application/octet-stream',
    ];
    protected $import;
    protected $locationSets = [];
    protected $additional = [];
    protected $truncateReBalance = false;
    protected $file = [];

    /**
     * @param bool $delete
     *
     * @return $this
     */
    public function setDeleteAll($delete = true)
    {
        if ($delete) {
            $this->arOptions['ONLY_DELETE_ALL'] = (bool)$delete;
        } else {
            unset($this->arOptions['ONLY_DELETE_ALL']);
        }
        return $this;
    }

    /**
     * @param bool $drop
     *
     * @return $this
     */
    public function setDropAll($drop = true)
    {
        if ($drop) {
            $this->arOptions['DROP_ALL'] = (bool)$drop;
        } else {
            unset($this->arOptions['DROP_ALL']);
        }
        return $this;
    }

    /**
     * @param bool $preserve
     *
     * @return $this
     */
    public function setIntegrityPreserve($preserve = true)
    {
        if ($preserve) {
            $this->arOptions['INTEGRITY_PRESERVE'] = (bool)$preserve;
        } else {
            unset($this->arOptions['INTEGRITY_PRESERVE']);
        }
        return $this;
    }

    /**
     * @param array $locationSets
     *
     * @return $this
     */
    public function setLocationSets(array $locationSets)
    {
        $this->locationSets = $locationSets;
        return $this;
    }

    /**
     * Set truncate ReBalance for better performance
     *
     * @param bool $truncate
     *
     * @return $this
     */
    public function setTruncateReBalance($truncate = true)
    {
        $this->truncateReBalance = (bool)$truncate;
        return $this;
    }

    /**
     * @param $depth
     *
     * @return $this
     */
    public function setDepthLimit($depth)
    {
        $this->arOptions['DEPTH_LIMIT'] = (int)$depth;
        return $this;
    }

    /**
     * @return int
     * @throws \Bitrix\Main\DB\SqlQueryException
     * @throws \SomeWork\Exception\InvalidArgumentException
     */
    protected function performIteration()
    {
        $this->onBeforePerformIteration();
        $percent = $this->getImport()->performIteration();
        $this->onAfterPerformIteration();
        return $percent;
    }

    /**
     * @return $this
     * @throws \Bitrix\Main\DB\SqlQueryException
     * @throws \SomeWork\Exception\InvalidArgumentException
     */
    protected function onBeforePerformIteration()
    {
        $this->updateTimeLimit();
        $this->truncateReBalance();
        return $this;
    }

    /**
     * @return $this
     * @throws \SomeWork\Exception\InvalidArgumentException
     */
    protected function updateTimeLimit()
    {
        return $this->setTimeLimit($this->getTimeLimit() * 2 + 1);
    }

    /**
     * @param int $timeLimit
     *
     * @return static
     * @throws \SomeWork\Exception\InvalidArgumentException
     */
    public function setTimeLimit($timeLimit)
    {
        $_REQUEST['OPTIONS']['TIME_LIMIT'] = $timeLimit;
        parent::setTimeLimit($timeLimit);
        return $this;
    }

    /**
     * @return $this
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    protected function truncateReBalance()
    {
        if ($this->truncateReBalance && 0 === $this->getStep()) {
            $tableName = BitrixImportProcess::TREE_REBALANCE_TEMP_TABLE_NAME;
            if (Application::getConnection()->isTableExists($tableName)) {
                Application::getConnection()->query("truncate table {$tableName}");
            }
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getStep()
    {
        return array_key_exists('STEP', $this->arOptions) ? (int)$this->arOptions['STEP'] : 0;
    }

    /**
     * @return BitrixImportProcess
     */
    protected function getImport()
    {
        if ($this->import === null) {
            $this->onBeforeCreateImport();
            $this->import = new BitrixImportProcess($this->arOptions);
            $this->onAfterCreateImport();
        }
        return $this->import;
    }

    protected function onBeforeCreateImport()
    {
        $_REQUEST['OPTIONS'] = $this->arOptions;
        $_REQUEST['ADDITIONAL'] = $this->additional;
        $this->sourceCheck();
    }

    protected function sourceCheck()
    {
        if (BitrixImportProcess::SOURCE_FILE === $this->getSource()) {
            $this->localSourceSet();
        } else {
            $this->remoteSourceSet();
        }
    }

    protected function remoteSourceSet()
    {
        $_REQUEST['LOCATION_SETS'] = $this->locationSets;
    }

    protected function onAfterCreateImport()
    {
        if (0 !== $this->getStep()) {
            return;
        }
        $this->reset();
    }

    protected function localSourceSet()
    {
        $_FILES[md5($this->file['tmp_name'])] = [
            'tmp_name' => $this->file['tmp_name'],
            'type'     => $this->file['type'],
        ];
    }

    /**
     * @return $this
     */
    protected function reset()
    {
        $this->getImport()->reset();
        return $this;
    }

    /**
     * @return $this
     */
    protected function onAfterPerformIteration()
    {
        $this->setStep($this->getImport()->getStep());
        return $this;
    }

    /**
     * @param $step
     *
     * @return $this
     */
    protected function setStep($step)
    {
        $this->arOptions['STEP'] = (int)$step;
        return $this;
    }

    /**
     * @param $source
     *
     * @return $this
     * @throws \SomeWork\Exception\ImportException
     */
    protected function setSource($source)
    {
        if (!in_array($source, [BitrixImportProcess::SOURCE_FILE, BitrixImportProcess::SOURCE_REMOTE], true)) {
            throw new ImportException('Wrong source type passed');
        }
        $this->arOptions['SOURCE'] = $source;
        return $this;
    }

    /**
     * @param array $arAdditional
     *
     * @return $this
     */
    protected function setAdditional(array $arAdditional)
    {
        $this->additional = $arAdditional;
        return $this;
    }    /**
     * @return string
     * @throws \SomeWork\Exception\ImportException
     */
    protected function getSource()
    {
        if (!array_key_exists('SOURCE', $this->arOptions)) {
            $this->setSource(BitrixImportProcess::SOURCE_REMOTE);
        }
        return $this->arOptions['SOURCE'];
    }

    /**
     * @param $file
     *
     * @return $this
     */
    protected function setFile($file)
    {
        $this->file['tmp_name'] = $file;
        return $this;
    }

    /**
     * @param $fileType
     *
     * @return $this
     */
    protected function setFileType($fileType)
    {
        $this->file['type'] = $fileType;
        return $this;
    }

}