<?php

namespace Admingenerator\GeneratorBundle\Tests\QueryFilter;

use Admingenerator\GeneratorBundle\Tests\TestCase;
use Admingenerator\GeneratorBundle\QueryFilter\DoctrineQueryFilter;

class QueryFilterTest extends TestCase
{
    protected $queryFilter;

    public function setUp()
    {
        parent::setUp();

        if (!class_exists('Doctrine\DBAL\DriverManager')) {
            $this->markTestSkipped('The "doctrine" service is not found.');
        }

        $this->queryFilter = $this->initQueryFilter();
    }

    public function testAddStringFilter()
    {
        $this->queryFilter->addStringFilter('title', 'test');

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q WHERE q.title LIKE :q_title', $this->queryFilter->getQuery()->getDql());
    }

    public function testAddTextFilter()
    {
        $this->queryFilter->addTextFilter('desc', 'test');

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q WHERE q.desc LIKE :q_desc', $this->queryFilter->getQuery()->getDql());
    }

    public function testAddDefaultFilter()
    {
        $this->queryFilter->addDefaultFilter('title', 'test');

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q WHERE q.title = :q_title', $this->queryFilter->getQuery()->getDql());
    }

    public function testFilterOnAssociation()
    {
        $this->queryFilter->addTextFilter('producer.name', 'test');

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q INNER JOIN q.producer producer_table_filter_join WHERE producer_table_filter_join.name LIKE :producer_table_filter_join_name', $this->queryFilter->getQuery()->getDql());
    }

    public function testMultipleFiltersOnAssociation()
    {
        $this->queryFilter->addTextFilter('producer.name', 'test');
        $this->queryFilter->addBooleanFilter('producer.published', true);

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q INNER JOIN q.producer producer_table_filter_join WHERE producer_table_filter_join.name LIKE :producer_table_filter_join_name AND producer_table_filter_join.published = :producer_table_filter_join_published', $this->queryFilter->getQuery()->getDql());
    }

    public function testCall()
    {
        $this->queryFilter->addFooFilter('title', 'test');

        $this->assertEquals('SELECT q FROM Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie q WHERE q.title = :q_title', $this->queryFilter->getQuery()->getDql());
    }

    protected function initQueryFilter()
    {
        $em =  $this->_getTestEntityManager();
        $qb = new \Doctrine\ORM\QueryBuilder($em);

        $query = $qb->select('q')
                    ->from('Admingenerator\GeneratorBundle\Tests\QueryFilter\Entity\Movie', 'q');
        $queryFilter = new DoctrineQueryFilter($query);

        return $queryFilter;
    }

    /**
     * Creates an EntityManager for testing purposes.
     *
     * @return \Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine\EntityManagerMock
     */
    protected function _getTestEntityManager($conn = null, $conf = null, $eventManager = null)
    {
        $metadataCache = new \Doctrine\Common\Cache\ArrayCache;

        $config = new \Doctrine\ORM\Configuration();

        $config->setMetadataCacheImpl($metadataCache);
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver());
        $config->setQueryCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
        $config->setProxyDir(__DIR__ . '/Proxies');
        $config->setProxyNamespace('Doctrine\Tests\Proxies');

        if ($conn === null) {
            $conn = array(
                'driverClass'  => '\Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine\DriverMock',
                'wrapperClass' => '\Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine\ConnectionMock',
                'user'         => 'john',
                'password'     => 'wayne'
            );
        }

        if (is_array($conn)) {
            $conn = \Doctrine\DBAL\DriverManager::getConnection($conn, $config, $eventManager);
        }

        return \Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine\EntityManagerMock::create($conn, $config, $eventManager);
    }
}
