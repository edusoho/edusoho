<?php
namespace Topxia\Service\Order\OrderRefundProcessor;

interface OrderRefundProcessor 
{
	public function getLayout();

	public function findByLikeTitle($title);

	public function auditRefundOrder($id, $pass, $data);

	public function cancelRefundOrder($id);

	public function getTarget($id);

	public function applyRefundOrder($orderId, $amount, $reason, $container);

	public function getTargetMember($targetId, $userId);

}