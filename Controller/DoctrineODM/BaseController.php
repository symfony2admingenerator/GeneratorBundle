<?php

namespace Admingenerator\GeneratorBundle\Controller\DoctrineODM;

use Admingenerator\GeneratorBundle\Controller\AdminBaseController;
use Symfony\Component\HttpFoundation\Request;

/**
 * A base controller for DoctrineODM
 *
 * @author cedric Lombardot
 */
abstract class BaseController extends AdminBaseController
{
    protected ?Request $request = null;

    protected function getDoctrineMongoDB(): \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
    {
        if (!$this->container->has('odm_manager')) {
            throw new \LogicException('The DoctrineMongoDBBundle is not registered in your application.');
        }

        return $this->container->get('odm_manager');
    }

    protected function getDocumentManager(): \Doctrine\ODM\MongoDB\DocumentManager
    {
        if (!$this->container->has('odm_document_manager')) {
            throw new \LogicException('The DoctrineMongoDBBundle is not registered in your application.');
        }

        return $this->container->get('odm_document_manager');
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'odm_manager' => '?'.\Doctrine\Bundle\MongoDBBundle\ManagerRegistry::class,
                'odm_document_manager' => '?'.\Doctrine\ODM\MongoDB\DocumentManager::class
            ]
        );
    }
}
