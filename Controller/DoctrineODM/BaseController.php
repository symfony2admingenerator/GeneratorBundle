<?php

namespace Admingenerator\GeneratorBundle\Controller\DoctrineODM;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * A base controller for DoctrineODM
 *
 * @author cedric Lombardot
 *
 */
abstract class BaseController extends AbstractController
{
    protected ?Request $request = null;

    protected function getDoctrineMongoDB(): \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
    {
        if (!$this->container->has('doctrine_mongodb')) {
            throw new \LogicException('The DoctrineMongoDBBundle is not registered in your application.');
        }

        return $this->container->get('doctrine_mongodb');
    }

    protected function getDocumentManager(): \Doctrine\ODM\MongoDB\DocumentManager
    {
        return $this->get('doctrine.odm.mongodb.document_manager');
    }

    /**
     * Ensure the translator and logger services are available for usage
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'translator' => interface_exists('Symfony\Contracts\Translation\TranslatorInterface')
                    ? \Symfony\Contracts\Translation\TranslatorInterface::class
                    : \Symfony\Component\Translation\TranslatorInterface::class,
                'logger' => LoggerInterface::class,
            ]
        );
    }
}
