<?php

namespace Tests\Unit\AppBundle\Common;

use Biz\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;

class PaginatorTest extends BaseTestCase
{
    public function testToArray()
    {
        $paginator = $this->initPaginator();
        $array = Paginator::toArray($paginator);
        $expectArray = array(
            'firstPage' => 1,
            'currentPage' => 2,
            'firstPageUrl' => '/all_cards?batchId=1&page=1',
            'previousPageUrl' => '/all_cards?batchId=1&page=1',
            'pages' => array(1, 2, 3, 4, 5),
            'pageUrls' => array(
                '/all_cards?batchId=1&page=1',
                '/all_cards?batchId=1&page=2',
                '/all_cards?batchId=1&page=3',
                '/all_cards?batchId=1&page=4',
                '/all_cards?batchId=1&page=5',
            ),
            'lastPageUrl' => '/all_cards?batchId=1&page=5',
            'lastPage' => 5,
            'nextPageUrl' => '/all_cards?batchId=1&page=3',
        );
        $this->assertArrayEquals($expectArray, $array);
    }

    public function testGetItemCount()
    {
        $paginator = $this->initPaginator();
        $itemCount = $paginator->getItemCount();
        $this->assertEquals(100, $itemCount);
    }

    public function testSetPageRange()
    {
        $range = 10;
        $paginator = $this->initPaginator();
        $return = $paginator->setPageRange($range);
        $this->assertNotNull($return);
    }

    public function testGetPerPageCount()
    {
        $paginator = $this->initPaginator();
        $perPageCount = $paginator->getPerPageCount();
        $this->assertEquals(20, $perPageCount);
    }

    protected function initPaginator()
    {
        $request = new Request(
            array(
                'page' => 2,
            ),
            array(),
            array(),
            array(),
            array(),
            array(
                'REQUEST_URI' => '/all_cards?batchId=1&page=2',
            )
        );
        $total = 100;
        $perPage = 20;
        $paginator = new Paginator($request, $total, $perPage);

        return $paginator;
    }
}
