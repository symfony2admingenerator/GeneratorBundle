<?php

namespace Admingenerator\GeneratorBundle\Tests\Generator;

use Doctrine\Common\Util\Inflector;
use Admingenerator\GeneratorBundle\Tests\TestCase;
use Admingenerator\GeneratorBundle\Generator\Column;

class ColumnTest extends TestCase
{

    public function testGetName()
    {
        $from_to_array = array(
            'name' => 'name',
            'underscored_name' => 'underscored_name',
        );

        $this->checkColumn($from_to_array, 'getName');
    }

    public function testGetGetter()
    {
        $from_to_array = array(
            'name' => 'name',
            'underscored_name' => 'underscoredName',
        );

        $this->checkColumn($from_to_array, 'getGetter');
    }

    public function testGetLabel()
    {
        $from_to_array = array(
            'name' => 'Name',
            'underscored_name' => 'Underscored name',
        );

        $this->checkColumn($from_to_array, 'getLabel');
    }

    public function testSetProperty()
    {
        $options = array(
            'label' => 'my label',
            'getter' => 'getFoo',
            'sort_on' => 'foo',
            'sortOn' => 'foo',
            'dbType' => 'text',
            'formType' => 'choices',
            'formOptions' => array('foo' => 'bar'),
        );

        $column = new Column("test", false);

        foreach ($options as $option => $value) {
            $column->setProperty($option, $value);
            $this->assertEquals($value, call_user_func_array(array($column, 'get'.Inflector::classify($option)), array()));
        }
    }

    public function testSetAddFormOptionsPhpFunction()
    {
        $column = new Column("test", false);

        $column->setAddFormOptions(array('years' => array('.range' => array('from' => 1900, 'to' => 1915, 'step'=> 5 ))));

        $options = $column->getFormOptions();

        $this->assertEquals(array(1900, 1905, 1910, 1915), $options['years']);
    }

    public function testFiltersGroups()
    {
        $column = new Column('test', false);

        $column->setGroups(array('group1', 'group2'));
        $column->setFiltersGroups(array('group3', 'group4'));

        $this->assertEquals(array('group3', 'group4'), $column->getFiltersGroups());

        $column->setFiltersGroups(array());

        $this->assertEquals(array(), $column->getFiltersGroups());
    }

    public function testFiltersGroupsFallbackOnGroupsIfNotCustomized()
    {
        $column = new Column('test', false);

        $column->setGroups(array('group1', 'group2'));

        $this->assertEquals(array('group1', 'group2'), $column->getFiltersGroups());
    }


    /**
     * @param string $method
     */
    protected function checkColumn($from_to_array, $method)
    {
        foreach ($from_to_array as $from => $to) {
            $column = new Column($from, false);
            $this->assertEquals($to, $column->$method());
        }
    }

}
