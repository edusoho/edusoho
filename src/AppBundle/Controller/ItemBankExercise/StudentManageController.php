<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Common\TimeMachine;
use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\ItemBankExerciseMemberException;
use Biz\ItemBankExercise\OperateReason;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

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
            20
        );

        $students = $this->getExerciseMemberService()->search(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('item-bank-exercise-manage/student-manage/index.html.twig', [
            'exercise' => $exercise,
            'students' => $students,
            'followings' => $this->findCurrentUserFollowings(),
            'users' => $this->getUserService()->findUsersByIds(array_column($students, 'userId')),
            'userProfiles' => $this->getUserService()->findUserProfilesByIds(array_column($students, 'userId')),
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']),
            'paginator' => $paginator,
            'canExport' => $this->getCurrentUser()->hasPermission('custom_export_permission'),
        ]);
    }

    public function wrongQuestionsAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        return $this->render('item-bank-exercise-manage/wrong-question/index.html.twig', [
            'exercise' => $exercise,
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']),
        ]);
    }

    public function aiCompanionStudyAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        return $this->render('item-bank-exercise-manage/ai-companion-study/index.html.twig', [
            'exercise' => $exercise,
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']),
        ]);
    }

    public function studentRecordsAction(Request $request, $exerciseId, $type)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        return $this->render(
            'item-bank-exercise-manage/student-manage/records.html.twig',
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
            $data['reason'] = OperateReason::JOIN_BY_IMPORT;
            $data['reasonType'] = OperateReason::JOIN_BY_IMPORT_TYPE;
            $data['joinedChannel'] = OperateReason::JOIN_BY_IMPORT_TYPE;
            $data['source'] = 'outside';

            try {
                $this->getExerciseMemberService()->becomeStudent($exerciseId, $user['id'], $data);
            } catch (\Exception $e) {
                $this->setFlashMessage('danger', $e->getMessage());
            } finally {
                return $this->redirect(
                    $this->generateUrl(
                        'item_bank_exercise_manage_students',
                        ['exerciseId' => $exerciseId]
                    )
                );
            }
        }

        return $this->render(
            'item-bank-exercise-manage/student-manage/add-modal.html.twig',
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
        $member = $this->getExerciseMemberService()->getExerciseStudent($exerciseId, $userId);

        if (empty($member)) {
            $this->createNewException(ItemBankExerciseMemberException::NOTFOUND_MEMBER());
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getExerciseMemberService()->remarkStudent($exercise['id'], $user['id'], $data['remark']);

            return $this->createJsonResponse(['success' => 1]);
        }

        return $this->render(
            'item-bank-exercise-manage/student-manage/remark-modal.html.twig',
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
            if ($this->getExerciseMemberService()->isExerciseStudent($exerciseId, $user['id'])) {
                $response = $this->trans('item_bank_exercise.student_manage.student_exist');
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

    public function batchUpdateMemberDeadlinesAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        $ids = $request->query->get('ids');
        $all = $request->query->get('all');
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();
            if ($all) {
                if ('day' == $fields['updateType']) {
                    $this->getExerciseMemberService()->changeMembersDeadlineByExerciseId($exerciseId, $fields['day'], $fields['waveType']);

                    return $this->createJsonResponse(true);
                }
                $date = TimeMachine::isTimestamp($fields['deadline']) ? $fields['deadline'] : strtotime($fields['deadline'].' 23:59:59');
                $this->getExerciseMemberService()->updateMembers(['exerciseId' => $exerciseId, 'role' => 'student'], ['deadline' => $date]);

                return $this->createJsonResponse(true);
            }
            $this->getExerciseMemberService()->batchUpdateMemberDeadlines($exerciseId, $ids, $fields);

            return $this->createJsonResponse(true);
        }
        $users = $this->getUserService()->findUsersByIds($ids);

        return $this->render(
            'item-bank-exercise-manage/student-manage/set-deadline-modal.html.twig',
            [
                'exercise' => $exercise,
                'users' => $users,
                'ids' => implode(',', ArrayToolkit::column($users, 'id')),
                'all' => $all,
            ]
        );
    }

    public function checkUpdateDeadlineAction(Request $request, $exerciseId)
    {
        $fields = $request->query->all();
        $ids = $request->query->get('ids');
        $all = $request->query->get('all');
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if ($all && 'minus' == $fields['waveType']) {
            $exerciseMember = $this->getExerciseMemberService()->search(['exerciseId' => $exerciseId, 'deadlineGreaterThan' => '1', 'role' => 'student'], ['deadline' => 'ASC'], 0, 1);

            return $this->createJsonResponse($exerciseMember[0]['deadline'] - $fields['day'] * 24 * 60 * 60 > time());
        }
        if ($this->getExerciseMemberService()->checkUpdateDeadline($exerciseId, $ids, $fields)) {
            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    public function removeStudentAction(Request $request, $exerciseId, $userId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        $this->getExerciseMemberService()->removeStudent($exerciseId, $userId, ['reason' => OperateReason::EXIT_REMOVE_BY_MANUAL, 'reasonType' => OperateReason::EXIT_REMOVE_TYPE]);

        return $this->createJsonResponse(['success' => true]);
    }

    public function removeStudentsAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        $userIds = $request->request->get('studentIds', []);
        $userIds = is_array($userIds) ? $userIds : explode(',', $userIds);
        if (empty($this->getUserService()->findUsersByIds($userIds))) {
            return $this->createJsonResponse(['success' => false]);
        }
        $this->getExerciseMemberService()->removeStudents($exerciseId, $userIds, ['reason' => OperateReason::EXIT_REMOVE_BY_MANUAL, 'reasonType' => OperateReason::EXIT_REMOVE_TYPE]);

        return $this->createJsonResponse(['success' => true]);
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
