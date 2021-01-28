<?php

namespace AppBundle\Controller\My;

use Biz\Card\Service\CardService;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class CardController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $cardType = $request->query->get('cardType');

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，请先登录！');
        }

        if ('moneyCard' == $cardType) {
            if (!$this->isPluginInstalled('moneyCard') || ($this->isPluginInstalled('moneyCard') && version_compare($this->getWebExtension()->getPluginVersion('moneyCard'), '1.1.1', '<='))) {
                return $this->render('card/index.html.twig', array(
                    'cards' => null,
                ));
            }
        }

        if (empty($cardType) || !in_array($cardType, array('coupon', 'moneyCard'))) {
            $cardType = 'coupon';
        }

        $couponSetting = $this->getSettingService()->get('coupon', array());
        if ('coupon' == $cardType && empty($couponSetting['enabled'])) {
            return $this->createMessageResponse('error', '无法访问该页面');
        }

        $cards = $this->getCardService()->findCardsByUserIdAndCardType($user['id'], $cardType);
        $cardIds = ArrayToolkit::column($cards, 'cardId');
        $cards = $this->sortCards($cards);
        $filter = $request->query->get('filter');

        if (!empty($filter)) {
            $groupCards = ArrayToolkit::group($cards, 'status');

            if ('used' == $filter) {
                $cards = isset($groupCards['used']) ? $groupCards['used'] : null;
            } elseif ('outdate' == $filter) {
                $cards = isset($groupCards['outdate']) ? $groupCards['outdate'] : null;
            } elseif ('invalid' == $filter) {
                $cards = isset($groupCards['invalid']) ? $groupCards['invalid'] : null;
            } else {
                $cards = isset($groupCards['useable']) ? $groupCards['useable'] : null;
            }
        }

        $cardsDetail = $this->getCardService()->findCardDetailsByCardTypeAndCardIds($cardType, $cardIds);

        return $this->render('card/index.html.twig', array(
            'cards' => empty($cards) ? null : $cards,
            'cardDetails' => ArrayToolkit::index($cardsDetail, 'id'),
        ));
    }

    public function availableCouponsAction($targetType, $targetId, $totalPrice, $priceType)
    {
        $availableCoupons = $this->availableCouponsByIdAndType($targetId, $targetType);

        if ($availableCoupons) {
            $higherTop = array();
            $lowerTop = array();

            foreach ($availableCoupons as $key => &$coupon) {
                if ('minus' == $coupon['type']) {
                    $coupon['truePrice'] = $totalPrice - $coupon['rate'];
                } else {
                    $coupon['truePrice'] = $totalPrice * ($coupon['rate'] / 10);
                }

                if ($coupon['truePrice'] > 0) {
                    $coupon['decrease'] = 0 - $coupon['truePrice'];
                    $lowerTop[] = $coupon;
                } else {
                    $coupon['decrease'] = 0 - $coupon['truePrice'];
                    $higherTop[] = $coupon;
                }
            }

            $higherTop = $this->getCardService()->sortArrayByField($higherTop, 'decrease');
            $lowerTop = $this->getCardService()->sortArrayByField($lowerTop, 'decrease');
            $availableCoupons = array_merge(array_reverse($higherTop), $lowerTop);
        }

        return $this->render('order/order-item-coupon.html.twig', array(
            'targetType' => $targetType,
            'targetId' => $targetId,
            'totalPrice' => $totalPrice,
            'priceType' => $priceType,
            'coupons' => $availableCoupons,
        ));
    }

    private function availableCouponsByIdAndType($id, $type)
    {
        if ('course' == $type) {
            $course = $this->getCourseService()->getCourse($id);
            $id = $course['courseSetId'];
        }

        return $this->getCardService()->findCurrentUserAvailableCouponForTargetTypeAndTargetId(
            $type, $id
        );
    }

    public function cardInfoAction(Request $request)
    {
        $cardType = $request->query->get('cardType');
        $cardId = $request->query->get('cardId');
        $card = $this->getCardService()->getCardByCardIdAndCardType($cardId, $cardType);

        $cardDetail = $this->getCardService()->findCardDetailByCardTypeAndCardId($cardType, $cardId);
        $response = $this->render('card/receive-show.html.twig', array(
            'cardType' => $cardType,
            'cardId' => $cardId,
            'cardDetail' => $cardDetail,
        ));

        $response->headers->setCookie(new Cookie('modalOpened', '0'));

        return $response;
    }

    protected function sortCards($cards)
    {
        $cards = $this->getCardService()->sortArrayByField($cards, 'createdTime');
        $cards = ArrayToolkit::group($cards, 'status');
        $sortedCards = array();

        $currentTime = time();
        $usedCards = isset($cards['used']) ? $cards['used'] : array();
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

    /**
     * @return CardService
     */
    protected function getCardService()
    {
        return $this->getBiz()->service('Card:CardService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
