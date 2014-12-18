<?php

namespace Admingenerator\GeneratorBundle\SessionSerializer;

/**
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class PropelSessionSerializer implements SessionSerializerInterface
{
    private $metadatas = array();

    private function getQueryFor($class)
    {
        if (class_exists($queryClass = $class.'Query')) {
            return new $queryClass();
        } else {
            return null;
        }
    }

    /**
     * @param string $class
     */
    private function getMetadataFor($class)
    {
        if (!array_key_exists($class, $this->metadatas)) {
            if ($query = $this->getQueryFor($class)) {
                $this->metadatas[$class] = $query->getTableMap();
            } else {
                $this->metadatas[$class] = null;
            }
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

            /* Serialize only if object has query class */
            if ($metadata = $this->getMetadataFor($class)) {
                $primaryKeys = $metadata->getPrimaryKeys();

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

            $data = $this->getQueryFor($class)->findPK($pk);
        }

        return $data;
    }
}
