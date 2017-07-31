<?php

namespace AppBundle\Component\Export;

interface ExporterInterface
{
    public function getPreResult($fileName);
}
