<?php

namespace Admingenerator\GeneratorBundle\Tests\Generator;

use Admingenerator\GeneratorBundle\Tests\TestCase;
use Admingenerator\GeneratorBundle\Generator\Action;

class ActionTest extends TestCase
{

    public function testGetName(): void
    {
        $from_to_array = [
            'name' => 'name',
            'underscored_name' => 'underscored_name',
        ];

        $this->checkAction($from_to_array, 'getName');
    }

    public function testGetLabel(): void
    {
        $from_to_array = [
            'name' => 'Name',
            'underscored_name' => 'Underscored name',
        ];

        $this->checkAction($from_to_array, 'getLabel');
    }

    protected function checkAction(array $from_to_array, string $method): void
    {
        foreach ($from_to_array as $from => $to) {
            $action = new Action($from);
            $this->assertEquals($to, $action->$method());
        }
    }

}
