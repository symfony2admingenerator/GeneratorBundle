<?php

namespace Admingenerator\GeneratorBundle\SessionSerializer;

use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class DoctrineORMSessionSerializer implements SessionSerializerInterface
{
    private $doctrine;

    private $managers = array();

    private $metadatas = array();

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    private function getManagerFor($class)
    {
        if (!array_key_exists($class, $this->managers)) {
            $this->managers[$class] = $this->doctrine->getManagerForClass($class);
        }

        return $this->managers[$class];
    }

    /**
     * @param string $class
     */
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
                $primaryKeys = $this->getMetadataFor($class)
                    ->getIdentifierValues($data);

                /* Serialize only models with single identifier */
                if (count($primaryKeys) === 1) {
                    $pk = array_shift($primaryKeys);
                    $data = new SessionSerializedModel($class, $pk);
                } else {
                    throw new \LogicException("Serialize model with multiple identifiers not implemented.");
                }
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
