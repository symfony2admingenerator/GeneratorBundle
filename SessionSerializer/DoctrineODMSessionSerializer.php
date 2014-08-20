<?php

namespace Admingenerator\GeneratorBundle\SessionSerializer;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class DoctrineODMSessionSerializer implements SessionSerializerInterface
{
    private $documentManager;

    private $managers = array();

    private $metadatas = array();

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    private function getManagerFor($class)
    {
        if (!array_key_exists($class, $this->managers)) {
            $this->managers[$class] = $this->documentManager->getManagerForClass($class);
        }

        return $this->managers[$class];
    }

    private function getMetadataFor($class)
    {
        if (!array_key_exists($class, $this->metadatas)) {
            $this->metadatas[$class] = $this->getManagerFor($class)
                ->getMetadataFactory()
                ->getMetadataFor($class);
        }

        return $this->metadatas[$class];
    }

    public function serialize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $item) {
                $data[$key] = $this->serialize($item);
            }
        }

        if (is_object($data)) {
            $class = get_class($data);
            $em = $this->getManagerFor($class);

            /* Serialize only if object is managed by Doctrine */
            if (null !== $em) {
                $pk = $this->getMetadataFor($class)
                    ->getIdentifierValue($data);

                /* DoctrineODM allows only single identifier */
                $data = new SessionSerializedModel($class, $pk);
            }
        }

        return $data;
    }

    public function deserialize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $item) {
                $data[$key] = $this->deserialize($item);
            }
        }

        /* Deserialize models */
        if (is_object($data) && $data instanceof SessionSerializedModel) {
            $class = $data->getClass();
            $pk = $data->getPk();

            $data = $this->getManagerFor($class)->getRepository($class)->find($pk);
        }

        return $data;
    }
}