<?php
namespace Topxia\Service\Order\OrderRefundProcessor;

interface OrderRefundProcessor 
{
	public function getLayout();

	public function findByLikeTitle($title);

	public function auditRefundOrder($id, $pass, $data);

	public function cancelRefundOrder($id);
}