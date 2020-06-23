<?php


namespace AppBundle\Controller\ItemBankExercise;


use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

class ExerciseController extends BaseController
{
    public function openAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', $this->trans('item_bank_exercise.exercise_create.forbidden'));
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }
        $exercise = $this->getExerciseService()->getByQuestionBankId($questionBank['id']);
        if (!empty($exercise)){
            return $this->redirect($this->generateUrl('item_bank_exercise_manage_base',['exerciseId' => $exercise['id']]));
        }
        return $this->render('question-bank/question/exercise-set.html.twig', [
            'questionBank' => $questionBank,
        ]);
    }

    public function createAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', $this->trans('item_bank_exercise.exercise_create.forbidden'));
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        if ($request->isMethod('POST')) {
            $seq_exercise = $this->getExerciseService()->search([],['seq' => 'DESC'],0,1);
            $maxSeqExercise = empty($seq_exercise) ? [] : $seq_exercise[0];
            $seq = empty($maxSeqExercise) ? 1 : $maxSeqExercise['seq'] + 1;
            $data = [
                'title' => $questionBank['name'],
                'questionBankId' => $questionBank['id'],
                'categoryId' => $questionBank['categoryId'],
                'seq' => $seq,
            ];
            $exercise = $this->getExerciseService()->create($data);

            return $this->redirect($this->generateUrl('item_bank_exercise_manage_base',['exerciseId' => $exercise['id']]));
        }

        return $this->render(
            'question-bank/question/create-modal.html.twig',
            [
                'questionBank' => $questionBank,
            ]
        );
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }
}