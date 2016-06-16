<?php

namespace NilPortugues\Api\Xml;

use DOMDocument;
use SimpleXMLElement;

class XmlPresenter
{
    /**
     * @var array
     */
    private $linkKeys = [
        XmlTransformer::LINKS_HREF,
    ];

    /**
     * @param array $array
     *
     * @return string
     */
    public function output(array $array)
    {
        $xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data></data>');
        $this->arrayToXml($array, $xmlData);
        $xml = $xmlData->asXML();
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($xml);
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->formatOutput = true;
        $xmlDoc->substituteEntities = false;

        return rtrim(html_entity_decode($xmlDoc->saveXML()), "\n");
    }

    /**
     * Converts an array to XML using SimpleXMLElement.
     *
     * @param array            $data
     * @param SimpleXMLElement $xmlData
     */
    protected function arrayToXml(array &$data, SimpleXMLElement $xmlData)
    {
        foreach ($data as $key => $value) {
            $key = ltrim($key, '_');
            if (\is_array($value)) {
                if (\is_numeric($key)) {
                    $key = 'resource';
                }
                if (false === empty($value[XmlTransformer::LINKS_HREF])) {
                    $subnode = $xmlData->addChild('link');
                    $subnode->addAttribute('rel', $key);
                    foreach ($this->linkKeys as $linkKey) {
                        if (!empty($value[$linkKey])) {
                            $subnode->addAttribute($linkKey, $value[$linkKey]);
                        }
                    }
                } else {
                    if (!empty($value[XmlTransformer::LINKS][XmlTransformer::LINKS_HREF])) {
                        $subnode = $xmlData->addChild('resource');
                        $subnode->addAttribute(
                            XmlTransformer::LINKS_HREF,
                            $value[XmlTransformer::LINKS][XmlTransformer::LINKS_HREF]
                        );
                        if ($key !== 'resource') {
                            $subnode->addAttribute('rel', $key);
                        }
                    } else {
                        $subnode = $xmlData->addChild($key);
                    }
                }
                $this->arrayToXml($value, $subnode);
            } else {
                if ($key !== XmlTransformer::LINKS) {
                    if ($value === true || $value === false) {
                        $value = ($value)  ? 'true' : 'false';
                    }

                    if ($key === XmlTransformer::LINKS_HREF) {
                        break;
                    }

                    $xmlData->addChild("$key", '<![CDATA['.html_entity_decode($value).']]>');
                }
            }
        }
    }
}
