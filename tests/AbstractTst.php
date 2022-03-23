<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/DataProcessor/TempModel.php';

/**
 * Class AbstractTst
 */
class AbstractTst extends \PHPUnit\Framework\TestCase
{
    #region Helpers

    /**
     * @param string|object $class_or_object
     * @param string $property_name
     *
     * @return mixed
     */
    protected function getStaticProperty($class_or_object, string $property_name)
    {
        $reflection = new \ReflectionClass($class_or_object);
        $property = $reflection->getProperty($property_name);
        $property->setAccessible(true);

        return $property->getValue();
    }

    /**
     * @param string|object $class_or_object
     * @param string $property_name
     * @param mixed $value
     */
    protected function setStaticProperty($class_or_object, string $property_name, $value)
    {
        $reflection = new \ReflectionClass($class_or_object);
        $property = $reflection->getProperty($property_name);
        $property->setAccessible(true);
        $property->setValue($class_or_object, $value);
    }

    #endregion
}