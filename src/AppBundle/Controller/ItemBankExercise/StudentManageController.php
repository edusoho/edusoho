<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\ItemBankExerciseMemberException;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Translator;

class StudentManageController extends BaseController
{
    public function studentsAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        $conditions = [
            'exerciseId' => $exercise['id'],
            'role' => 'student',
        ];

        $keyword = $request->query->get('keyword', '');
        if (!empty($keyword)) {
            $conditions['userIds'] = $this->getUserService()->getUserIdsByKeyword($keyword);
        }

        $paginator = new Paginator(
            $request,
            $this->getExerciseMemberService()->count($conditions),
            50
        );

        $students = $this->getExerciseMemberService()->search(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('item-bank-exercise/student-manage/index.html.twig', [
            'exercise' => $exercise,
            'students' => $students,
            'followings' => $this->findCurrentUserFollowings(),
            'users' => $this->getUserService()->findUsersByIds(array_column($students, 'userId')),
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']),
            'paginator' => $paginator,
        ]);
    }

    public function studentRecordsAction(Request $request, $exerciseId, $type)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        return $this->render(
            'item-bank-exercise/student-manage/records.html.twig',
            [
                'exercise' => $exercise,
                'type' => $type,
                'questionBank' => $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']),
            ]
        );
    }

    public function createExerciseStudentAction(Request $request, $exerciseId)
    {
        $operateUser = $this->getUser();
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        if (!$operateUser->isAdmin() && empty($exercise)) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $user = $this->getUserService()->getUserByLoginField($data['queryfield'], true);

            $this->getExerciseMemberService()->becomeStudent($exerciseId, $user['id'], $data);

            $this->setFlashMessage('success', 'site.add.success');

            return $this->redirect(
                $this->generateUrl(
                    'item_bank_exercise_manage_students',
                    ['exerciseId' => $exerciseId]
                )
            );
        }

        return $this->render(
            'item-bank-exercise/student-manage/add-modal.html.twig',
            [
                'exercise' => $exercise,
            ]
        );
    }

    public function findCurrentUserFollowings()
    {
        $user = $this->getCurrentUser();
        $followings = $this->getUserService()->findAllUserFollowing($user->getId());
        if (!empty($followings)) {
            return ArrayToolkit::index($followings, 'id');
        }

        return [];
    }

    public function remarkAction(Request $request, $exerciseId, $userId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        $user = $this->getUserService()->getUser($userId);
        $member = $this->getExerciseMemberService()->getExerciseMember($exerciseId, $userId);

        if (empty($member)) {
            $this->createNewException(ItemBankExerciseMemberException::NOTFOUND_MEMBER());
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getExerciseMemberService()->remarkStudent($exercise['id'], $user['id'], $data['remark']);

            return $this->createJsonResponse(['success' => 1]);
        }

        return $this->render(
            'item-bank-exercise/student-manage/remark-modal.html.twig',
            [
                'member' => $member,
                'user' => $user,
                'exercise' => $exercise,
            ]
        );
    }

    public function checkStudentAction(Request $request, $exerciseId)
    {
        $keyword = $request->query->get('value');
        $user = $this->getUserService()->getUserByLoginField($keyword, true);

        $response = true;
        if (!$user) {
            $response = $this->trans('item_bank_exercise.student_manage.student_not_exist');
        } else {
            $isExerciseStudent = $this->getExerciseMemberService()->isExerciseMember($exerciseId, $user['id']);

            if ($isExerciseStudent) {
                $response = $this->trans('item_bank_exercise.student_manage.student_exist');
            } else {
                $isExerciseTeacher = $this->getExerciseService()->isExerciseTeacher($exerciseId, $user['id']);

                if ($isExerciseTeacher) {
                    $response = $this->trans('item_bank_exercise.student_manage.can_not_add_teacher');
                }
            }
        }

        return $this->createJsonResponse($response);
    }

    public function showAction(Request $request, $exerciseId, $userId)
    {
        if (!$this->getCurrentUser()->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        return $this->forward('AppBundle:Student:show', [
            'request' => $request,
            'userId' => $userId,
        ]);
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->createService('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }
}
