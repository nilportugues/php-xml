<?php

namespace NilPortugues\Api\Xml;

use NilPortugues\Api\Mapping\Mapper;
use NilPortugues\Serializer\DeepCopySerializer;

class XmlSerializer extends DeepCopySerializer
{
    /**
     * XmlSerializer constructor.
     *
     * @param Mapper $mapper
     */
    public function __construct(Mapper $mapper)
    {
        parent::__construct(new XmlTransformer($mapper));
    }

    /**
     * @return XmlTransformer
     */
    public function getTransformer()
    {
        return $this->serializationStrategy;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        return parent::serialize($value);
    }
}
