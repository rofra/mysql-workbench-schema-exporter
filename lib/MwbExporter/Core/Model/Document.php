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

namespace MwbExporter\Core\Model;

use MwbExporter\Core\Registry;
use MwbExporter\Core\IFormatter;
use MwbExporter\Core\Model\PhysicalModel;
use MwbExporter\Core\Helper\Mwb;
use MwbExporter\Core\Helper\FileExporter;
use MwbExporter\Core\Helper\ZipFileExporter;

class Document extends Base
{
    protected $data = null;
    protected $attributes = null;

    protected $id = null;

    protected $physicalModel = null;

    public function __construct($mwbFile, IFormatter $formatter)
    {
        // load mwb simple_xml object
        $this->data = Mwb::readXML($mwbFile);

        // save formatter in registry
        Registry::set('formatter', $formatter);

        // save document in registry
        Registry::set('document', $this);
        $this->parse();

        // save this object in registry by workebench id
        Registry::set($this->id, $this);
    }

    protected function parse()
    {
        $this->attributes = $this->data->value->attributes();
        $this->data       = $this->data->value;

        $this->id = (string) $this->attributes['id'];

        $tmp = $this->data->xpath("value[@key='physicalModels']/value");
        $this->physicalModel = new PhysicalModel($tmp[0], $this);
    }

    public function display()
    {
        return $this->physicalModel->display();
    }

    public function export(FileExporter $exporter, $format = 'php')
    {
        if($exporter === null){
            throw new \Exception('You need the exporter object to do the export.');
        }

        $exporter->setSaveFormat($format);
        $this->physicalModel->export($exporter);
        $exporter->save();
    }

    public function zipExport($path = null, $format = 'php')
    {
        if($path === null){
            throw new \Exception('missing path for zip export');
        }

        $path = realpath($path);
        $zip = new ZipFileExporter($path);
        $this->export($zip, $format);

        return 'document zipped as ' . $zip->getFileName();
    }
}