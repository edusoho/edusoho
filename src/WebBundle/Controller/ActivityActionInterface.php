<?php
/**
 * User: retamia
 * Date: 2016/10/24
 * Time: 13:26
 */

namespace WebBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

interface ActivityActionInterface
{
    public function showAction(Request $request, $id);

    public function editAction(Request $request, $id);

    public function createAction(Request $request);
}