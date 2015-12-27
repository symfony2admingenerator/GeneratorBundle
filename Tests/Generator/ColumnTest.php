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
            'filterType' => 'choice',
            'filterOptions' => array('bar' => 'foo'),
        );

        $column = new Column("test", false);

        foreach ($options as $option => $value) {
            $column->setProperty($option, $value);
            $this->assertEquals($value, call_user_func_array(array($column, 'get'.Inflector::classify($option)), array()));
        }
    }

    public function testSetAddFormFilterOptionsPhpFunction()
    {
        $column = new Column("test", false);

        $addOptions = array('years' => array('.range' => array('from' => 1900, 'to' => 1915, 'step' => 5)));

        $column->setAddFormOptions($addOptions);

        $options = $column->getFormOptions();

        $testOptions = array(1900, 1905, 1910, 1915);

        $this->assertEquals($testOptions, $options['years']);

        $column->setAddFilterOptions($addOptions);

        $options = $column->getFilterOptions();

        $this->assertEquals($testOptions, $options['years']);
    }

    public function testCredentials()
    {
        $column = new Column('test', false);

        $column->setCredentials(array('credential1', 'credential2'));
        $column->setFiltersCredentials(array('credential3', 'credential4'));

        $this->assertEquals(array('credential1', 'credential2'), $column->getCredentials());
        $this->assertEquals(array('credential3', 'credential4'), $column->getFiltersCredentials());

        $column->setFiltersCredentials(array());

        $this->assertEquals(array(), $column->getFiltersCredentials());
    }

    public function testFiltersCredentialsFallbackOnCredentialsIfNotCustomized()
    {
        $column = new Column('test', false);

        $column->setCredentials(array('credential1', 'credential2'));

        $this->assertEquals(array('credential1', 'credential2'), $column->getFiltersCredentials());
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
