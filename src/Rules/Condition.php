<?php

namespace WebXID\EDMo\Rules;

/**
 * Class Condition
 *
 * @package WebXID\EDMo\Rules
 */
class Condition
{
    const IT_REQUIRD = 'itRequired';
    const MIN_LEN = 'minLen';
    const MAX_LEN = 'maxLen';
    const MIN_VALUE = 'minValue';
    const MAX_VALUE = 'maxValue';
    const EQUALS = 'equals';
    const NOT_EQUALS = 'notEquals';
    const REGEXP = 'regexp';
    const IN_ARRAY = 'inArray';
    const NOT_IN_ARRAY = 'notInArray';
    const PHONE = 'phone';
    const EMAIL = 'email';
    const IP_ADDRESS = 'ipAddress';
    const CALLBACK = 'callback';
    const FILTER_VAR = 'filter_var';
}
