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


namespace SomeWork\Sale\Enum;


class ErrorMessageEnum
{
    const MODULE_LOAD = 'Cant load required bitrix module';
    const TIME_LIMIT_CHANGE = 'Cant change time limit';
    const MEMORY_LIMIT_CHANGE = 'Cant change memory limit';

    const REQUIRED_SOURCE = 'Invalid source for current method';
    const INVALID_SOURCE = 'Invalid source';
}