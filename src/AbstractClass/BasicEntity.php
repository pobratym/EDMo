<?php

namespace WebXID\EDMo\AbstractClass;

use WebXID\BasicClasses\Entity;
use WebXID\EDMo\Rules;

/**
 * Class BasicEntity
 *
 * @package WebXID\EDMo\AbstractClass
 */
abstract class BasicEntity extends Entity
{


    #region Abstract methods

    /**
     * @return Rules
     */
    abstract public static function getRules() : Rules;

    #endregion

    #region Actions

    /**
     * Calls after a model saved in DB by $this->save() method
     *
     * @return void
     */
    protected function savedAction()
    {
        $this->makeNotNovice();
    }

    /**
     * Calls after a model deleted in DB by $this->delete() method
     *
     * @return void
     */
    protected function deletedAction()
    {
        $this->makeNovice();
    }

    #endregion
}
