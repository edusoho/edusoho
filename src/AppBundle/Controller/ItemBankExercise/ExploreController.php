<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\Service\ExerciseService;
use Symfony\Component\HttpFoundation\Request;

class ExploreController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        list($conditions, $filter) = $this->getFilter($conditions);
        $orderBy = $conditions['orderBy'];
        unset($conditions['orderBy']);
        $conditions['status'] = 'published';

        $paginator = new Paginator(
            $this->get('request'),
            $this->getExerciseService()->count($conditions),
            20
        );

        $exercises = $this->getExerciseService()->search(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'item-bank-exercise/explore/index.html.twig',
            [
                'exercises' => $exercises,
                'paginator' => $paginator,
            ]
        );
    }

    protected function getFilter($conditions)
    {
        $default = array('price' => 'all');

        $filter = !isset($conditions['filter']) ? $default : $conditions['filter'];

        if (isset($filter['price']) && 'free' === $filter['price']) {
            $conditions['price'] = '0.00';
        }

        unset($conditions['filter']);

        return array($conditions, $filter);
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }
}
