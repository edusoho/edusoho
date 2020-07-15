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
        $orderBy = empty($conditions['orderBy']) ? 'latest' : $conditions['orderBy'];
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

        if ('recommendedSeq' == $orderBy) {
            $currentPage = $request->query->get('page') ? $request->query->get('page') : 1;
            $exercises = $this->searchExerciseOrderByRecommend($conditions, $currentPage, $orderBy);
        }

        return $this->render(
            'item-bank-exercise/explore/index.html.twig',
            [
                'exercises' => $exercises,
                'paginator' => $paginator,
                'filter' => $filter,
            ]
        );
    }

    protected function searchExerciseOrderByRecommend($conditions, $currentPage, $orderBy)
    {
        $conditions['recommended'] = 1;
        $recommendCount = $this->getExerciseService()->count($conditions);
        $recommendPage = (int) ($recommendCount / 20);
        $recommendLeft = $recommendCount % 20;

        if ($currentPage <= $recommendPage) {
            $exercises = $this->getExerciseService()->search(
                $conditions,
                $orderBy,
                ($currentPage - 1) * 20,
                20
            );
        } elseif (($recommendPage + 1) == $currentPage) {
            $exercises = $this->getExerciseService()->search(
                $conditions,
                $orderBy,
                ($currentPage - 1) * 20,
                20
            );
            $conditions['recommended'] = 0;
            $exercisesTemp = $this->getExerciseService()->search(
                $conditions,
                ['createdTime' => 'DESC'],
                0,
                20 - $recommendLeft
            );
            $exercises = array_merge($exercises, $exercisesTemp);
        } else {
            $conditions['recommended'] = 0;
            $exercises = $this->getExerciseService()->search(
                $conditions,
                ['createdTime' => 'DESC'],
                (20 - $recommendLeft) + ($currentPage - $recommendPage - 2) * 20,
                20
            );
        }

        return $exercises;
    }

    protected function getFilter($conditions)
    {
        $default = ['price' => 'all'];

        $filter = !isset($conditions['filter']) ? $default : $conditions['filter'];

        if (isset($filter['price']) && 'free' === $filter['price']) {
            $conditions['price'] = '0.00';
        }

        unset($conditions['filter']);

        return [$conditions, $filter];
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }
}
