<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Favorite\Service\FavoriteService;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\AssessmentExerciseRecordService;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ChapterExerciseRecordService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Review\Service\ReviewService;
use Biz\System\Service\CacheService;
use Biz\User\Service\TokenService;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        if (!empty($exercise)) {
            return $this->redirect($this->generateUrl('item_bank_exercise_manage_base', ['exerciseId' => $exercise['id']]));
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
            $seq_exercise = $this->getExerciseService()->search([], ['seq' => 'DESC'], 0, 1);
            $maxSeqExercise = empty($seq_exercise) ? [] : $seq_exercise[0];
            $seq = empty($maxSeqExercise) ? 1 : $maxSeqExercise['seq'] + 1;
            $data = [
                'title' => $questionBank['name'],
                'questionBankId' => $questionBank['id'],
                'categoryId' => $questionBank['categoryId'],
                'seq' => $seq,
            ];
            $exercise = $this->getExerciseService()->create($data);

            return $this->redirect($this->generateUrl('item_bank_exercise_manage_base', ['exerciseId' => $exercise['id']]));
        }

        return $this->render(
            'question-bank/question/create-modal.html.twig',
            [
                'questionBank' => $questionBank,
            ]
        );
    }

    public function showAction(Request $request, $id, $tab, $moduleId)
    {
        $user = $this->getCurrentUser();
        $exercise = $this->getExerciseService()->get($id);
        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        $member = $user['id'] ? $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $user['id']) : null;
        $previewAs = $request->query->get('previewAs', '');
        if (empty($previewAs) && $user->isLogin() && $this->canExerciseShowRedirect($request)) {
            if (!empty($member)) {
                if ('date' != $exercise['expiryMode'] || $exercise['expiryStartDate'] < time()) {
                    return $this->redirect(($this->generateUrl('my_item_bank_exercise_show', ['id' => $id])));
                }
            }
        }

        $isExerciseTeacher = $this->getExerciseService()->isExerciseTeacher($id, $user['id']);
        $tabs = $this->getTabs($exercise);
        if (!empty($tabs) && $tab == '') {
            $tab = $tabs[0]['type'];
            $moduleId = $tabs[0]['id'];
        }

        return $this->render(
            'item-bank-exercise/exercise-show.html.twig',
            [
                'tab' => $tab == '' ? 'reviews' : $tab,
                'tabs' => $tabs,
                'moduleId' => $moduleId,
                'exercise' => $exercise,
                'isExerciseTeacher' => $isExerciseTeacher,
                'member' => $member,
                'previewAs' => $previewAs,
            ]
        );
    }

    protected function getTabs($exercise)
    {
        $condition['exerciseId'] = $exercise['id'];
        if ($exercise['chapterEnable']) {
            $condition['types'][] = 'chapter';
        }
        if ($exercise['assessmentEnable']) {
            $condition['types'][] = 'assessment';
        }
        return $this->getExerciseModuleService()->search(
            $condition,
            [],
            0,
            6
        );
    }

    public function headerAction(Request $request, $exercise, $tab, $moduleId)
    {
        $user = $this->getCurrentUser();

        $member = $user->isLogin() ? $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $user['id']) : [];

        $isUserFavorite = $user->isLogin() ? !empty($this->getFavoriteService()->getUserFavorite(
            $user['id'],
            'item_bank_exercise',
            $exercise['id']
        )) : false;

        return $this->render(
            'item-bank-exercise/header/header-for-guest.html.twig',
            [
                'isUserFavorite' => $isUserFavorite,
                'member' => $member,
                'exercise' => $exercise,
                'tab' => $tab,
                'moduleId' => $moduleId,
            ]
        );
    }

    public function qrcodeAction(Request $request, $id, $tab = 'scan')
    {
        list($url, $userId) = $this->getQrcodeUrl($id);
        $token = $this->getTokenService()->makeToken(
            'qrcode',
            [
                'userId' => $userId,
                'data' => [
                    'url' => $url,
                ],
                'times' => 1,
                'duration' => 3600,
            ]
        );
        $url = $this->generateUrl('common_parse_qrcode', ['token' => $token['token']], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($tab != 'scan') {
            return $this->qrcodeDownload($url);
        }

        $response = [
            'img' => $this->generateUrl('common_qrcode', ['text' => $url], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        return $this->createJsonResponse($response);
    }

    protected function qrcodeDownload($url)
    {
        $qrCode = new QrCode();
        $qrCode->setText($url);
        $qrCode->setSize(150);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');

        $headers = ['Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="image.png"',];

        return new Response($img, 200, $headers);
    }

    public function reviewsAction(Request $request, $id, $member = [])
    {
        $exercise = $this->getExerciseService()->get($id);

        $conditions = [
            'parentId' => 0,
            'targetType' => 'item_bank_exercise',
            'targetId' => $id,
        ];

        $paginator = new Paginator(
            $request,
            $this->getReviewService()->countReviews($conditions),
            20
        );

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userReview = [];
        $user = $this->getCurrentUser();
        if (empty($member) && $user->isLogin()) {
            $member = $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $user['id']);
        }
        if (!empty($member)) {
            $userReview = $this->getReviewService()->getByUserIdAndTargetTypeAndTargetId($member['userId'], 'item_bank_exercise', $exercise['id']);
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));

        return $this->render(
            'item-bank-exercise/tabs/reviews.html.twig',
            [
                'paginator' => $paginator,
                'exercise' => $exercise,
                'reviews' => $reviews,
                'userReview' => $userReview,
                'users' => $users,
                'member' => $member,
            ]
        );
    }

    public function moduleAction(Request $request, $previewAs, $exerciseId, $moduleId)
    {
        $module = $this->getExerciseModuleService()->get($moduleId);

        return $this->render(
            'item-bank-exercise/tabs/module.html.twig',
            [
                'moduleType' => $module['type'],
                'moduleId' => $moduleId,
                'exerciseId' => $exerciseId,
                'previewAs' => $previewAs,
            ]
        );
    }

    public function renderChapterListAction(Request $request, $previewAs, $exerciseId, $moduleId)
    {
        $exercise = $this->getExerciseService()->get($exerciseId);
        $user = $this->getCurrentUser();
        $member = $user['id'] ? $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $user['id']) : null;
        $questionBank = $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']);
        $categoryTree = [];
        if ($exercise['chapterEnable']) {
            $categoryTree = $this->getItemCategoryService()->getItemCategoryTreeList($questionBank['itemBankId']);
        }
        $records = [];
        if ($member) {
            $records = $this->getChapterExerciseRecordService()->search(
                ['moduleId' => $moduleId, 'exerciseId' => $exerciseId, 'userId' => $user['id']],
                ['createdTime' => 'ASC'],
                0,
                PHP_INT_MAX
            );
            $records = ArrayToolkit::index($records, 'itemCategoryId');
        }

        return $this->render('item-bank-exercise/tabs/list/chapter-list.html.twig', [
            'exercise' => $exercise,
            'moduleId' => $moduleId,
            'member' => $member,
            'records' => $records,
            'categoryTree' => $categoryTree,
            'previewAs' => $previewAs,
        ]);
    }

    public function renderAssessmentListAction(Request $request, $previewAs, $exerciseId, $moduleId)
    {
        $exercise = $this->getExerciseService()->get($exerciseId);
        $user = $this->getCurrentUser();
        $member = $user['id'] ? $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $user['id']) : null;

        $paginator = new Paginator(
            $this->get('request'),
            $this->getAssessmentExerciseService()->count(['moduleId' => $moduleId]),
            20
        );

        $assessments = [];
        $assessmentExercises = [];
        if ($exercise['assessmentEnable']) {
            $assessmentExercises = $this->getAssessmentExerciseService()->search(
                ['moduleId' => $moduleId],
                ['createdTime' => 'DESC'],
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
            $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($assessmentExercises, 'assessmentId'));
        }

        $records = [];
        if ($member) {
            $records = $this->getAssessmentExerciseRecordService()->search(
                ['moduleId' => $moduleId, 'exerciseId' => $exerciseId, 'userId' => $user['id']],
                ['createdTime' => 'ASC'],
                0,
                PHP_INT_MAX
            );
            $records = ArrayToolkit::index($records, 'assessmentId');
        }

        return $this->render('item-bank-exercise/tabs/list/assessment-list.html.twig', [
            'exercise' => $exercise,
            'moduleId' => $moduleId,
            'member' => $member,
            'records' => $records,
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']),
            'assessments' => $assessments,
            'paginator' => $paginator,
            'assessmentExercises' => ArrayToolkit::index($assessmentExercises, 'assessmentId'),
            'previewAs' => $previewAs,
        ]);
    }

    public function advancedUsersAction(Request $request, $exerciseId)
    {
        $records = $this->getCacheService()->get("item_bank_exercise({$exerciseId})");
        $records = json_decode($records, true);
        if (empty($records)) {
            $records = $this->getChapterExerciseRecordService()->findWeekRankRecords($exerciseId);
            $expiryTime = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7, date('Y'));
            $this->getCacheService()->set("item_bank_exercise({$exerciseId})", json_encode($records), $expiryTime);
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($records, 'userId'));

        return $this->render(
            'item-bank-exercise/tabs/advanced.html.twig',
            [
                'users' => $users,
                'members' => ArrayToolkit::index($records, 'userId'),
            ]
        );
    }

    public function memberExpiredAction($id)
    {
        list($exercise, $member) = $this->getExerciseService()->tryTakeExercise($id);

        if ($this->getExerciseMemberService()->isMemberNonExpired($exercise, $member)) {
            return $this->createJsonResponse(true);
        }

        return $this->render(
            'item-bank-exercise/expired.html.twig',
            [
                'exercise' => $exercise,
                'member' => $member,
            ]
        );
    }

    public function deadlineReachAction($id)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $this->getExerciseMemberService()->quitExerciseByDeadlineReach($user['id'], $id);

        return $this->redirect($this->generateUrl('item_bank_exercise_show', ['id' => $id]));
    }

    protected function getQrcodeUrl($id)
    {
        $user = $this->getCurrentUser();
        $params = ['id' => $id];

        $url = $this->generateUrl('item_bank_exercise_show', $params, UrlGeneratorInterface::ABSOLUTE_URL);
        if ($user->isLogin()) {
            $exerciseMember = $this->getExerciseMemberService()->getExerciseMember($id, $user['id']);
            if ($exerciseMember) {
                $url = $this->generateUrl('my_item_bank_exercise_show', $params, UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }

        return [$url, $user['id']];
    }

    private function canExerciseShowRedirect($request)
    {
        $host = $request->getHost();
        $referer = $request->headers->get('referer');
        if (empty($referer)) {
            return false;
        }

        $biz = $this->getBiz();
        $matchExpreList = $biz['item_bank_exercise.show_redirect'];

        foreach ($matchExpreList as $matchExpre) {
            $matchExpre = "/{$host}" . $matchExpre;
            if (preg_match($matchExpre, $referer)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return FavoriteService
     */
    protected function getFavoriteService()
    {
        return $this->createService('Favorite:FavoriteService');
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

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->createService('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Review:ReviewService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->createService('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return AssessmentExerciseService
     */
    protected function getAssessmentExerciseService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return ChapterExerciseRecordService
     */
    protected function getChapterExerciseRecordService()
    {
        return $this->createService('ItemBankExercise:ChapterExerciseRecordService');
    }

    /**
     * @return AssessmentExerciseRecordService
     */
    protected function getAssessmentExerciseRecordService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseRecordService');
    }
}
