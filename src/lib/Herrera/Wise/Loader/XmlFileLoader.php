<?php

namespace Herrera\Wise\Loader;

use DOMDocument;
use DOMElement;

/**
 * A loader for simple XML files.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class XmlFileLoader extends AbstractFileLoader
{
    /**
     * {@inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        return (is_string($resource)
            && ('xml' === strtolower(pathinfo($resource, PATHINFO_EXTENSION))))
            && ((null === $type) || ('xml' === $type));
    }

    /**
     * @override
     */
    protected function doLoad($file)
    {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;

        $doc->load($file);
        $doc->schemaValidate(__DIR__ . '/../../../../../res/schema.xsd');

        return $this->toArray($doc->documentElement);
    }

    /**
     * Converts a DOM elements to native PHP values.
     *
     * @param DOMElement $node  The node.
     *
     * @return mixed The result.
     */
    private function toArray(DOMElement $node)
    {
        $value = null;

        switch ($node->nodeName) {
            case 'array':
                $value = array();

                if ($node->hasChildNodes()) {
                    for ($i = 0; $i < $node->childNodes->length; $i++) {
                        /** @var $child DOMElement */
                        $child = $node->childNodes->item($i);

                        if ($child->hasAttribute('key')) {
                            $value[$child->getAttribute('key')] = $this->toArray($child);
                        } else {
                            $value[] = $this->toArray($child);
                        }
                    }
                }
                break;
            case 'bool':
                $value = (bool) $node->textContent;
                break;
            case 'float':
                $value = (float) $node->textContent;
                break;
            case 'int':
                $value = (int) $node->textContent;
                break;
            case 'str':
                $value = $node->textContent;
                break;
        }

        return $value;
    }
}
