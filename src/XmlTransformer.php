<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/18/15
 * Time: 2:26 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Api\Xml;

use NilPortugues\Api\Transformer\Helpers\RecursiveDeleteHelper;
use NilPortugues\Api\Transformer\Helpers\RecursiveFormatterHelper;
use NilPortugues\Api\Transformer\Helpers\RecursiveRenamerHelper;
use NilPortugues\Api\Transformer\Transformer;
use NilPortugues\Serializer\Serializer;

/**
 * Class XmlTransformer.
 */
class XmlTransformer extends Transformer
{
    const META_KEY = 'meta';
    const LINKS = 'links';

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $presenter = new XmlPresenter();

        return $presenter->output($this->serialization($value));
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function serialization($value)
    {
        if (is_array($value) && !empty($value[Serializer::MAP_TYPE])) {
            $data = [];
            foreach ($value[Serializer::SCALAR_VALUE] as $v) {
                $data[] = $this->serializeObject($v);
            }
        } else {
            $data = $this->serializeObject($value);
        }

        return $data;
    }

    /**
     * @param array $value
     *
     * @return mixed
     */
    protected function serializeObject(array $value)
    {
        if (null !== $this->mappings) {
            /** @var \NilPortugues\Api\Mapping\Mapping $mapping */
            foreach ($this->mappings as $class => $mapping) {
                RecursiveDeleteHelper::deleteProperties($this->mappings, $value, $class);
                RecursiveRenamerHelper::renameKeyValue($this->mappings, $value, $class);
            }
        }

        $this->setResponseMeta($value);
        $this->setResponseLinks($value);
        self::formatScalarValues($value);
        RecursiveDeleteHelper::deleteKeys($value, [Serializer::CLASS_IDENTIFIER_KEY]);
        self::flattenObjectsWithSingleKeyScalars($value);

        return $value;
    }

    /**
     * @param array $response
     */
    private function setResponseMeta(array &$response)
    {
        if (!empty($this->meta)) {
            $response[self::META_KEY] = $this->meta;
        }
    }

    /**
     * @param array $response
     */
    private function setResponseLinks(array &$response)
    {
        $links = array_filter(
            array_merge(
                $this->buildSelfLink($response),
                $this->buildLinks(),
                $this->getResponseAdditionalLinks($response, $response[Serializer::CLASS_IDENTIFIER_KEY])
            )
        );

        if (!empty($links)) {
            $response[self::LINKS] = $this->addHrefToLinks($links);
        }
    }

    /**
     * @param array $response
     *
     * @return array
     */
    protected function buildSelfLink(array &$response)
    {
        $link = [];

        if (!empty($type = $response[Serializer::CLASS_IDENTIFIER_KEY])) {
            list($idValues, $idProperties) = RecursiveFormatterHelper::getIdPropertyAndValues(
                $this->mappings,
                $response,
                $type
            );

            $href = self::buildUrl(
                $this->mappings,
                $idProperties,
                $idValues,
                $this->mappings[$type]->getResourceUrl(),
                $type
            );

            if ($href != $this->mappings[$type]->getResourceUrl()) {
                $link[self::SELF_LINK] = $href;
            }
        }

        return $link;
    }

    /**
     * @param \NilPortugues\Api\Mapping\Mapping[] $mappings
     * @param                                     $idProperties
     * @param                                     $idValues
     * @param                                     $url
     * @param                                     $type
     *
     * @return mixed
     */
    protected static function buildUrl(array &$mappings, $idProperties, $idValues, $url, $type)
    {
        $outputUrl = \str_replace($idProperties, $idValues, $url);

        if ($outputUrl !== $url) {
            return $outputUrl;
        }

        $outputUrl = self::secondPassBuildUrl([$mappings[$type]->getClassAlias()], $idValues, $url);

        if ($outputUrl !== $url) {
            return $outputUrl;
        }

        $className = $mappings[$type]->getClassName();
        $className = \explode('\\', $className);
        $className = \array_pop($className);

        $outputUrl = self::secondPassBuildUrl([$className], $idValues, $url);
        if ($outputUrl !== $url) {
            return $outputUrl;
        }

        return $url;
    }

    /**
     * @param $idPropertyName
     * @param $idValues
     * @param $url
     *
     * @return mixed
     */
    protected static function secondPassBuildUrl($idPropertyName, $idValues, $url)
    {
        if (!empty($idPropertyName)) {
            $outputUrl = self::toCamelCase($idPropertyName, $idValues, $url);
            if ($url !== $outputUrl) {
                return $outputUrl;
            }

            $outputUrl = self::toLowerFirstCamelCase($idPropertyName, $idValues, $url);
            if ($url !== $outputUrl) {
                return $outputUrl;
            }

            $outputUrl = self::toUnderScore($idPropertyName, $idValues, $url);
            if ($url !== $outputUrl) {
                return $outputUrl;
            }
        }

        return $url;
    }

    /**
     * @param $original
     * @param $idValues
     * @param $url
     *
     * @return mixed
     */
    protected static function toCamelCase($original, $idValues, $url)
    {
        foreach ($original as &$o) {
            $o = '{'.self::underscoreToCamelCase(self::camelCaseToUnderscore($o)).'}';
        }

        return \str_replace($original, $idValues, $url);
    }

    /**
     * Converts a underscore string to camelCase.
     *
     * @param string $string
     *
     * @return string
     */
    protected static function underscoreToCamelCase($string)
    {
        return \str_replace(' ', '', \ucwords(\strtolower(\str_replace(['_', '-'], ' ', $string))));
    }

    /**
     * Transforms a given string from camelCase to under_score style.
     *
     * @param string $camel
     * @param string $splitter
     *
     * @return string
     */
    protected static function camelCaseToUnderscore($camel, $splitter = '_')
    {
        $camel = \preg_replace(
            '/(?!^)[[:upper:]][[:lower:]]/',
            '$0',
            \preg_replace('/(?!^)[[:upper:]]+/', $splitter.'$0', $camel)
        );

        return \strtolower($camel);
    }

    /**
     * @param $original
     * @param $idValues
     * @param $url
     *
     * @return mixed
     */
    protected static function toLowerFirstCamelCase($original, $idValues, $url)
    {
        foreach ($original as &$o) {
            $o = self::underscoreToCamelCase(self::camelCaseToUnderscore($o));
            $o[0] = \strtolower($o[0]);
            $o = '{'.$o.'}';
        }

        return \str_replace($original, $idValues, $url);
    }

    /**
     * @param $original
     * @param $idValues
     * @param $url
     *
     * @return mixed
     */
    protected static function toUnderScore($original, $idValues, $url)
    {
        foreach ($original as &$o) {
            $o = '{'.self::camelCaseToUnderscore($o).'}';
        }

        return \str_replace($original, $idValues, $url);
    }
}
