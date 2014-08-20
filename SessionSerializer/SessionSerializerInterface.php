<?php

namespace Admingenerator\GeneratorBundle\SessionSerializer;

/**
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
interface SessionSerializerInterface
{
    /**
     * Recursively serialize data, replaceing models by SerializedModel object
     *
     * @param  mixed $data  Data to be serialized.
     * @return mixed        Serialized data.
     */
    public function serialize($data);

    /**
     * Recursively deserialize data, replaceing SerializedModel objects with actual models
     * 
     * @param  mixed $data  Data to be deserialized.
     * @return mixed        Deserialized data.
     */
    public function deserialize($data);
}