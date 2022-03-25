<?php

use WebXID\EDMo;

/**
 * Class DataProcessorTest
 */
class DataProcessorTest extends AbstractTst
{
    #region DataProcessor::init()

    /**
     *
     */
    public function testInit()
    {
        $processor = EDMo\DataProcessor::init(Test\DataProcessor\TempSingleKeyModel::class);

        $this->assertInstanceOf(EDMo\DataProcessor::class, $processor);
    }

    #endregion

    #region DataProcessor::all()

    /**
     *
     */
    public function testAll()
    {
        $processor = EDMo\DataProcessor::init(Test\DataProcessor\TempSingleKeyModel::class);

        $this->assertInstanceOf(EDMo\DataProcessor\AbstractSearch::class, $processor->all());
    }

    #endregion

    #region DataProcessor::find()

    /**
     *
     */
    public function testFind()
    {
        $processor = EDMo\DataProcessor::init(Test\DataProcessor\TempSingleKeyModel::class);

        $result = $processor->find([
            'id' => [1, 2],
            'title' => 'Aaaaa'
        ], EDMo\DB\Build::RELATION_OR);

        $this->assertInstanceOf(EDMo\DataProcessor\Find::class, $result);
    }

    #endregion

    #region DataProcessor::search()

    /**
     *
     */
    public function testSearch()
    {
        $processor = EDMo\DataProcessor::init(Test\DataProcessor\TempSingleKeyModel::class);

        $result = $processor->search(' title LIKE :title ', [':title' => '%fff%']);

        $this->assertInstanceOf(EDMo\DataProcessor\Search::class, $result);
    }

    #endregion

    #region DataProcessor::addNew()

    /**
     *
     */
    public function testAddNew()
    {
        $processor = EDMo\DataProcessor::init(Test\DataProcessor\TempSingleKeyModel::class);

        $result = $processor->addNew();

        $this->assertInstanceOf(EDMo\DataProcessor\AddNew::class, $result);
    }

    #endregion

    #region DataProcessor::update()

    /**
     *
     */
    public function testUpdate()
    {
        $processor = EDMo\DataProcessor::init(Test\DataProcessor\TempSingleKeyModel::class);

        $result = $processor->update(" id IN (:id) ")
            ->binds([':id' => [1, 2]]);

        $this->assertInstanceOf(EDMo\DataProcessor\Update::class, $result);
    }

    #endregion

    #region DataProcessor::delete()

    /**
     *
     */
    public function testDelete()
    {
        $processor = EDMo\DataProcessor::init(Test\DataProcessor\TempSingleKeyModel::class);

        $result = $processor->delete(" id IN (:id) ")
            ->binds([':id' => [1, 2]]);

        $this->assertInstanceOf(EDMo\DataProcessor\Delete::class, $result);
    }

    #endregion
}