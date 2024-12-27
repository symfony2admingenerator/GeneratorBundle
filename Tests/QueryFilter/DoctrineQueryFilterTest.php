<?php

namespace Admingenerator\GeneratorBundle\Tests\QueryFilter;

use Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine\EntityManagerMock;
use Admingenerator\GeneratorBundle\Tests\TestCase;
use Admingenerator\GeneratorBundle\QueryFilter\DoctrineQueryFilter;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\QueryBuilder;

class DoctrineQueryFilterTest extends TestCase
{
    protected DoctrineQueryFilter $queryFilter;

    public function setUp(): void
    {
        parent::setUp();

        if (!class_exists('Doctrine\DBAL\DriverManager')) {
            $this->markTestSkipped('The "doctrine" service is not found.');
        }

        $this->queryFilter = $this->initQueryFilter();
    }

    public function testAddStringFilter(): void
    {
        $this->queryFilter->addStringFilter('title', 'test');

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q WHERE q.title LIKE :q_title_0', $this->queryFilter->getQuery()->getDql());
    }

    public function testAddTextFilter(): void
    {
        $this->queryFilter->addTextFilter('desc', 'test');

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q WHERE q.desc LIKE :q_desc_0', $this->queryFilter->getQuery()->getDql());
    }

    public function testAddDefaultFilter(): void
    {
        $this->queryFilter->addDefaultFilter('title', 'test');

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q WHERE q.title = :q_title_0', $this->queryFilter->getQuery()->getDql());
    }

    public function testFilterOnAssociation(): void
    {
        $this->queryFilter->addTextFilter('producer.name', 'test');

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q INNER JOIN q.producer producer_table_filter_join WHERE producer_table_filter_join.name LIKE :producer_table_filter_join_name_0', $this->queryFilter->getQuery()->getDql());
    }

    public function testMultipleFiltersOnAssociation(): void
    {
        $this->queryFilter->addTextFilter('producer.name', 'test');
        $this->queryFilter->addBooleanFilter('producer.published', true);

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q INNER JOIN q.producer producer_table_filter_join WHERE producer_table_filter_join.name LIKE :producer_table_filter_join_name_0 AND producer_table_filter_join.published = :producer_table_filter_join_published_1', $this->queryFilter->getQuery()->getDql());
    }

    public function testCall(): void
    {
        $this->queryFilter->addFooFilter('title', 'test');

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q WHERE q.title = :q_title_0', $this->queryFilter->getQuery()->getDql());
    }

    protected function initQueryFilter(): DoctrineQueryFilter
    {
        $em =  $this->_getTestEntityManager();
        $qb = new QueryBuilder($em);

        $query = $qb->select('q')
                    ->from('Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie', 'q');
        return new DoctrineQueryFilter($query);
    }

    /**
     * Creates an EntityManager for testing purposes.
     *
     * @return EntityManagerMock
     */
    protected function _getTestEntityManager($conn = null, $conf = null, $eventManager = null): EntityManagerMock
    {

        $config = new Configuration();

        $config->setMetadataDriverImpl(new AttributeDriver([]));
        $config->setProxyDir(__DIR__ . '/Proxies');
        $config->setProxyNamespace('Doctrine\Tests\Proxies');

        if ($conn === null) {
            $conn = [
                'driverClass'  => '\Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine\DriverMock',
                'wrapperClass' => '\Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine\ConnectionMock',
                'user'         => 'john',
                'password'     => 'wayne'
            ];
        }

        if (is_array($conn)) {
            $conn = DriverManager::getConnection($conn, $config);
        }

        return EntityManagerMock::create($conn, $config, $eventManager);
    }
}
