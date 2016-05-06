<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CardController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user     = $this->getCurrentUser();
        $cardType = $request->query->get('cardType');

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，请先登录！');
        }

        if ($cardType == 'moneyCard') {
            if (!$this->isPluginInstalled('moneyCard') || ($this->isPluginInstalled('moneyCard') && version_compare($this->getWebExtension()->getPluginVersion('moneyCard'), '1.1.1', '<='))) {
                return $this->render('TopxiaWebBundle:Card:index.html.twig', array(
                    'cards' => null
                ));
            }
        }

        if (empty($cardType) || !in_array($cardType, array('coupon', 'moneyCard'))) {
            $cardType = "coupon";
        }

        $cards   = $this->getCardService()->findCardsByUserIdAndCardType($user['id'], $cardType);
        $cardIds = ArrayToolkit::column($cards, 'cardId');
        $cards   = $this->sortCards($cards);
        $filter  = $request->query->get('filter');

        if (!empty($filter)) {
            $groupCards = ArrayToolkit::group($cards, 'status');

            if ($filter == 'useable') {
                $cards = isset($groupCards['useable']) ? $groupCards['useable'] : null;
            } elseif ($filter == 'used') {
                $cards = isset($groupCards['used']) ? $groupCards['used'] : null;
            } elseif ($filter == 'outdate') {
                $cards = isset($groupCards['outdate']) ? $groupCards['outdate'] : null;
            } elseif ($filter == 'invalid') {
                $cards = isset($groupCards['invalid']) ? $groupCards['invalid'] : null;
            } else {
                $cards = isset($groupCards['useable']) ? $groupCards['useable'] : null;
            }
        }

        $cardsDetail = $this->getCardService()->findCardDetailsByCardTypeAndCardIds($cardType, $cardIds);
        return $this->render('TopxiaWebBundle:Card:index.html.twig', array(
            'cards'       => empty($cards) ? null : $cards,
            'cardDetails' => ArrayToolkit::index($cardsDetail, 'id')
        ));
    }

    public function useableCouponsAction($targetType, $targetId, $totalPrice, $priceType)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $cards      = $this->getCardService()->findCardsByUserIdAndCardType($user['id'], 'coupon');
        $cards      = $this->sortCards($cards);
        $groupCards = ArrayToolkit::group($cards, 'status');

        if (isset($groupCards['useable'])) {
            $cardIds      = ArrayToolkit::column($groupCards['useable'], 'cardId');
            $cardDetails  = $this->getCardService()->findCardDetailsByCardTypeAndCardIds('coupon', $cardIds);
            $useableCards = array();

            foreach ($cardDetails as $key => $value) {
                $useable = $this->isUseable($value, $targetType, $targetId);

                if ($useable) {
                    if ($value['type'] == 'minus') {
                        $cardDetails[$key]['truePrice'] = $totalPrice - $value['rate'];
                        $useableCards[]                 = $cardDetails[$key];
                    } else {
                        $cardDetails[$key]['truePrice'] = $totalPrice * ($value['rate'] / 10);
                        $useableCards[]                 = $cardDetails[$key];
                    }
                }
            }

            $higherTop = array();
            $lowerTop  = array();

            foreach ($useableCards as $key => $useableCard) {
                if ($useableCard['truePrice'] > 0) {
                    $useableCards[$key]['decrease'] = 0 - $useableCard['truePrice'];
                    $lowerTop[]                     = $useableCards[$key];
                } else {
                    $useableCards[$key]['decrease'] = 0 - $useableCard['truePrice'];
                    $higherTop[]                    = $useableCards[$key];
                }
            }

            $higherTop    = $this->getCardService()->sortArrayByField($higherTop, 'decrease');
            $lowerTop     = $this->getCardService()->sortArrayByField($lowerTop, 'decrease');
            $useableCards = array_merge(array_reverse($higherTop), $lowerTop);
        }

        return $this->render('TopxiaWebBundle:Order:order-item-coupon.html.twig', array(
            'targetType' => $targetType,
            'targetId'   => $targetId,
            'totalPrice' => $totalPrice,
            'priceType'  => $priceType,
            'coupons'    => isset($useableCards) ? $useableCards : null
        ));
    }

    public function isUseable($cardDetail, $targetType, $targetId)
    {
        if ($cardDetail['targetType'] == 'all' || $cardDetail['targetType'] == 'fullDiscount') {
            return true;
        }

        if ($cardDetail['targetType'] == $targetType && ($cardDetail['targetId'] == 0 || $cardDetail['targetId'] == $targetId)) {
            return true;
        }
    }

    public function cardInfoAction(Request $request)
    {
        $cardType = $request->query->get('cardType');
        $cardId   = $request->query->get('cardId');
        $card     = $this->getCardService()->getCardByCardIdAndCardType($cardId, $cardType);

        $cardDetail = $this->getCardService()->findCardDetailByCardTypeAndCardId($cardType, $cardId);
        $response   = $this->render('TopxiaWebBundle:Card:receive-show.html.twig', array(
            'cardType'   => $cardType,
            'cardId'     => $cardId,
            'cardDetail' => $cardDetail
        ));

        $response->headers->setCookie(new Cookie("modalOpened", '0'));
        return $response;
    }

    protected function sortCards($cards)
    {
        $cards       = $this->getCardService()->sortArrayByfield($cards, 'createdTime');
        $cards       = ArrayToolkit::group($cards, 'status');
        $sortedCards = array();

        $currentTime  = time();
        $usedCards    = isset($cards['used']) ? $cards['used'] : array();
        $invalidCards = isset($cards['invalid']) ? $cards['invalid'] : array();
        $outDateCards = array();
        $receiveCards = array();

        if (isset($cards['receive'])) {
            foreach ($cards['receive'] as $card) {
                if ($card['deadline'] + 86400 < $currentTime) {
                    $card['status'] = 'outdate';
                    $outDateCards[] = $card;
                } else {
                    $card['status'] = 'useable';
                    $receiveCards[] = $card;
                }
            }
        }

        $sortedCards = array_merge($receiveCards, $usedCards, $outDateCards, $invalidCards);

        return $sortedCards;
    }

    protected function getCardService()
    {
        return $this->getServiceKernel()->createService('Card.CardService');
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }
}
