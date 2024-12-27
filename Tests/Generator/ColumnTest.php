<?php

namespace Admingenerator\GeneratorBundle\Tests\Generator;

use Doctrine\Inflector\InflectorFactory;
use Admingenerator\GeneratorBundle\Tests\TestCase;
use Admingenerator\GeneratorBundle\Generator\Column;

class ColumnTest extends TestCase
{

    public function testGetName(): void
    {
        $from_to_array = [
            'name' => 'name',
            'underscored_name' => 'underscored_name',
        ];

        $this->checkColumn($from_to_array, 'getName');
    }

    public function testGetGetter(): void
    {
        $from_to_array = [
            'name' => 'name',
            'underscored_name' => 'underscoredName',
        ];

        $this->checkColumn($from_to_array, 'getGetter');
    }

    public function testGetLabel(): void
    {
        $from_to_array = [
            'name' => 'Name',
            'underscored_name' => 'Underscored name',
        ];

        $this->checkColumn($from_to_array, 'getLabel');
    }

    public function testSetProperty(): void
    {
        $options = [
            'label' => 'my label',
            'getter' => 'getFoo',
            'sort_on' => 'foo',
            'sortOn' => 'foo',
            'dbType' => 'text',
            'formType' => 'choices',
            'formOptions' => ['foo' => 'bar'],
            'filterType' => 'choice',
            'filterOptions' => ['bar' => 'foo'],
        ];

        $column = new Column("test", false);

        foreach ($options as $option => $value) {
            $column->setProperty($option, $value);
            $this->assertEquals($value, call_user_func_array([$column, 'get'.InflectorFactory::create()->build()->classify($option)], []));
        }
    }

    public function testSetAddFormFilterOptionsPhpFunction(): void
    {
        $column = new Column("test", false);

        $addOptions = ['years' => ['.range' => ['start' => 1900, 'end' => 1915, 'step' => 5]]];

        $column->setAddFormOptions($addOptions);

        $options = $column->getFormOptions();

        $testOptions = [1900, 1905, 1910, 1915];

        $this->assertEquals($testOptions, $options['years']);

        $column->setAddFilterOptions($addOptions);

        $options = $column->getFilterOptions();

        $this->assertEquals($testOptions, $options['years']);
    }

    public function testCredentials(): void
    {
        $column = new Column('test', false);

        $column->setCredentials(['credential1', 'credential2']);
        $column->setFiltersCredentials(['credential3', 'credential4']);

        $this->assertEquals(['credential1', 'credential2'], $column->getCredentials());
        $this->assertEquals(['credential3', 'credential4'], $column->getFiltersCredentials());

        $column->setFiltersCredentials([]);

        $this->assertEquals([], $column->getFiltersCredentials());
    }

    public function testFiltersCredentialsFallbackOnCredentialsIfNotCustomized(): void
    {
        $column = new Column('test', false);

        $column->setCredentials(['credential1', 'credential2']);

        $this->assertEquals(['credential1', 'credential2'], $column->getFiltersCredentials());
    }


    protected function checkColumn(array $from_to_array, string $method): void
    {
        foreach ($from_to_array as $from => $to) {
            $column = new Column($from, false);
            $this->assertEquals($to, $column->$method());
        }
    }

}
