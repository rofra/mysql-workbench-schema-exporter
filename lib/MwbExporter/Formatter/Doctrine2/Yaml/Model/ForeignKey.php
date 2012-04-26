<?php
/*
 *  The MIT License
 *
 *  Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace MwbExporter\Formatter\Doctrine2\Yaml\Model;

use MwbExporter\Core\Registry;
use MwbExporter\Core\Model\ForeignKey as Base;
use MwbExporter\Helper\Pluralizer;

class ForeignKey extends Base
{
    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    /**
     * Return the foreign key definition
     * Yaml format
     *
     * @return string
     */
    public function generateDefaultRelationName()
    {
        $relation_name = $this->referencedTable->getModelName();
        return $relation_name;
    }

    public function display()
    {
        $return = array();

        // Get the relation name relationunicitizer
        $relationUniquizer = Registry::get('relationunicitizer');

        /**
         * sometimes the link between to tables is broken
         */
        if($this->referencedTable == null){
            return $this->indentation(2) . '# There is another foreign key declaration but it seems broken.';
        }

        $return[] = $this->indentation(2) . $relationUniquizer->proposeRelationName($this->generateDefaultRelationName()) . ':';
        $return[] = $this->indentation(3) . 'class: ' . $this->referencedTable->getModelName();

        $ownerColumn = $this->data->xpath("value[@key='columns']");
        $return[] = $this->indentation(3) . 'local: ' . Registry::get((string) $ownerColumn[0]->link)->getColumnName();

        $referencedColumn = $this->data->xpath("value[@key='referencedColumns']");
        $return[] = $this->indentation(3) . 'foreign: ' . Registry::get((string) $referencedColumn[0]->link)->getColumnName();

        if((int)$this->config['many'] === 1){
            $return[] = $this->indentation(3) . 'foreignAlias: ' . Pluralizer::pluralize($this->owningTable->getModelName());
        } else {
            $return[] = $this->indentation(3) . 'foreignAlias: ' . $this->owningTable->getModelName();
        }

        $return[] = $this->indentation(3) . 'onDelete: ' . strtolower($this->config['deleteRule']);
        $return[] = $this->indentation(3) . 'onUpdate: ' . strtolower($this->config['updateRule']);

        return implode("\n", $return);
    }
}
