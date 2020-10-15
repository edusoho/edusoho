<?php

namespace AppBundle\Controller\InformationCollect;

use AppBundle\Controller\BaseController;
use Biz\InformationCollect\FormItem\FormItemFectory;
use Biz\InformationCollect\InformationCollectException;
use Symfony\Component\HttpFoundation\Request;
use Biz\InformationCollect\Service\EventService;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class InformationCollectController extends BaseController
{
    public function indexAction(Request $request, $eventId)
    {
        $event = $this->getEventService()->get($eventId);
        if (empty($event)) {
            throw $this->createNewException(InformationCollectException::NOTFOUND_COLLECTION());
        }

        return $this->render('information-collection/index.html.twig', ['eventId' => $eventId]);
    }

    public function eventFormAction(Request $request, $eventId)
    {
        $event = $this->getEventService()->get($eventId);
        if (empty($event)) {
            throw $this->createNewException(InformationCollectException::NOTFOUND_COLLECTION());
        }

        $eventItems = $this->getEventService()->findItemsByEventId($eventId);
        $formItems = FormItemFectory::getFormItems();

        $formBuilder = $this->createFormBuilder();
        $eventFormItems = [];
        foreach ($eventItems as $item) {
            if (empty($formItems[$item['code']])) {
                continue;
            }

            $instance = new $formItems[$item['code']];
            $formItem = $instance->getData();

            $eventFormItems[] = $formItem;

            $builderOptions = empty($formItem['builderOptions']) ? [] : $formItem['builderOptions'];
            $builderOptions['required'] = $item['required'];
            $formBuilder->add($formItem['field'], $formItem['builderType'], $builderOptions);
        }


        return $this->render('information-collection/form.html.twig', [
            'eventFormItems' => $eventFormItems,
            'formItemsView' => $formBuilder->getForm()->createView(),
            'event' => $event
        ]);
    }

    /**
     *
     * @return EventService
     */
    private function getEventService()
    {
        return $this->createService('InformationCollect:EventService');
    }
}
