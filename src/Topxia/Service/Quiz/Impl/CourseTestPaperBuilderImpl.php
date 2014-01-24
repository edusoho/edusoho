<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\TestPaperBuilder;
use Topxia\Common\ArrayToolkit;

class CourseTestPaperBuilderImpl extends BaseService  implements TestPaperBuilder
{
	private $options;
	private $testPaper;
	private $message = array();
	private $questions = array();
	private $typeQuestions = array();
	private $questionsGroup = array();

	public function prepare($testPaper,$options)
	{
		if(empty($options['isDifficulty'])){

			$options['isDifficulty'] = 0;
		}

		$this->testPaper = $testPaper;

		$this->options = $options;

		$lessons = $this->getCourseService()->getCourseLessons($options['courseId']);

		$questionsCourse = $this->getQuestionService()->findQuestionsByTypeAndTypeIds('course', array($options['courseId']));

		$questionsLesson = $this->getQuestionService()->findQuestionsByTypeAndTypeIds('lesson', ArrayToolkit::column($lessons, 'id'));

		$questions = array_merge($questionsCourse, $questionsLesson);
		
		$questionsGroup = array();

		if ($options['isDifficulty'] == 0) {

			foreach ($questions as $question) {

				if($question['parentId'] != 0)
					continue;

                $questionsGroup[$question['type']][] = $question;
            }
		} else {

			foreach ($questions as $question) {
				
				if($question['parentId'] != 0)
					continue;

                $questionsGroup[$question['type']][$question['difficulty']][] = $question;
            }
		}

		$this->questionsGroup = $questionsGroup;
 	}

	public function build()
	{
        
		// $seqTypes = explode(',', $this->testPaper['metas']['question_type_seq']);

		$name = $this->options['isDifficulty'] == 0 ? 'buildQuestions' : 'buildDifficultyQuestions';

		foreach ($this->testPaper['metas']['question_type_seq'] as $type) {
			
            $this->$name($type, $this->options['itemCounts'][$type]);
		}

	}

	public function validate()
	{
		$name = $this->options['isDifficulty'] == 0 ? 'validateMessage' : 'validateDifficultyMessage';

		foreach ($this->options['itemCounts'] as $type => $count) {
			
            $this->$name($type, $count);
		}

	}

	public function getQuestions()
	{
		return $this->questions;
	}


	public function getMessage()
	{
		if (empty($this->message)) {

            $this->message = false;

        } else {

            $this->message = array_merge(array('课程题库题目不足,无法生成试卷') , $this->message);

            $this->message = implode(',', $this->message);
        }

		return $this->message;
	}

	private function buildQuestions($type, $count)
	{
		$this->typeQuestions = array();

		$this->generateRandomQuestions($type, $count);

		$this->buildMaterialQuestions($type);
	}

	private function buildDifficultyQuestions($type, $count)
	{
		$this->typeQuestions = array();

        $needCountGroup = $this->getDifficultyCounts($count);

		foreach ($needCountGroup as $difficulty => $needCount) {

            if ($difficulty == 'otherCount') {

            	$questions = $this->generateAllDifficultyRandomQuestions($type, $difficulty, $needCount);

            } else {

            	$questions = $this->generateDifficultyRandomQuestions($type, $difficulty, $needCount);
            }
            
        }

		$this->buildMaterialQuestions($type);
	}

	private function buildMaterialQuestions($type)
	{
		if($type=="material"){

        	$questions = $this->getQuestionService()->findQuestionsByParentIds(ArrayToolkit::column($this->typeQuestions, 'id'));
        	
        	$this->questions = array_merge($this->questions, $questions);
        }
	}

	private function validateMessage($type, $count)
	{
		$message = array();

		if (empty($this->questionsGroup[$type])) {

            $this->questionsGroup[$type] = array();
        }

		if (count($this->questionsGroup[$type]) < $count) {

            $needCount = abs(count($this->questionsGroup[$type]) - $count);

            $message[] = $this->options['questionType'][$type]."缺少".$needCount."题 ";
        }
  
     	$this->message = array_merge($this->message , $message);
	}

	private function validateDifficultyMessage($type, $count)
	{
		$message = array();

        $needCountGroup = $this->getDifficultyCounts($count);

        $groupSum = 0;

		foreach ($needCountGroup as $difficulty => $needCount) {

            if ($difficulty == 'otherCount') {

                if ($groupSum < $needCount ) {

                    $needCount = $groupSum - $needCount;

                    $message[] = $this->options['questionType'][$type]."缺少".$needCount."题 ";
                }

                continue;
            }

            if (empty($this->questionsGroup[$type][$difficulty])) {

                $this->questionsGroup[$type][$difficulty] = array();
            }

            if (count($this->questionsGroup[$type][$difficulty]) < $needCount) {

                $needCount = abs(count($this->questionsGroup[$type][$difficulty]) - $needCount);

                $message[] = $this->options['questionType'][$type].$this->options['difficulty'][$difficulty]." 缺少".$needCount."题 ";
            }

            $groupSum = $groupSum + count($this->questionsGroup[$type][$difficulty]);

        }

     	$this->message = array_merge($this->message , $message);
	}

	private function generateRandomQuestions($type, $count)
	{
		if(empty($this->questionsGroup[$type])){

			$this->questionsGroup[$type] = array();
		}

		if(count($this->questionsGroup[$type]) < $count) {

			$this->createNotFoundException();
		}

		shuffle($this->questionsGroup[$type]);

        $questions = array_slice($this->questionsGroup[$type], 0, $count);

 		$this->questions = array_merge($this->questions, $questions);

 		$this->typeQuestions = array_merge($this->typeQuestions, $questions);
	}

	private function generateDifficultyRandomQuestions($type, $difficulty, $needCount)
	{
		if(empty($this->questionsGroup[$type][$difficulty])){

			$this->questionsGroup[$type][$difficulty] = array();
		}

		if (count($this->questionsGroup[$type][$difficulty]) < $needCount) {

			$this->createNotFoundException();
		}

		shuffle($this->questionsGroup[$type][$difficulty]);

		$questions = array();

		for ($i=0; $i < $needCount; $i++) { 

			$questions[] = array_merge($questions, array_shift($this->questionsGroup[$type][$difficulty]));
		}

		$this->questions = array_merge($this->questions, $questions);

		$this->typeQuestions = array_merge($this->typeQuestions, $questions);
	}

	private function generateAllDifficultyRandomQuestions($type, $difficulty, $needCount)
	{
		$questions = array();

		for ($i = 0; $i < $needCount; $i++) {

			$randDifficulty = array_rand($this->questionsGroup[$type]);

			$randId = array_rand($this->questionsGroup[$type][$randDifficulty]);

			if(!empty($this->questionsGroup[$type][$randDifficulty][$randId])){

				$questions[] = $this->questionsGroup[$type][$randDifficulty][$randId];
			}else{

				$i --;
			}

            unset($this->questionsGroup[$type][$randDifficulty][$randId]);

            if(count($this->questionsGroup[$type][$randDifficulty]) ==0){

                unset($this->questionsGroup[$type][$randDifficulty]);
            }
        } 

        $this->questions = array_merge($this->questions, $questions);

        $this->typeQuestions = array_merge($this->typeQuestions, $questions);
	}

	private function getDifficultyCounts($num)
    {
    	$perventage = array();

        $perventage['2'] = 100 - $this->options['perventage']['1'];

        $perventage['1'] = $this->options['perventage']['1'] - $this->options['perventage']['0'];

        $perventage['0'] = $this->options['perventage']['0'];

        if (($perventage['0'] + $perventage['1'] +$perventage['2']) != 100) {

            throw $this->createNotFoundException('perventage 参数错误');
        }

        $counts = array();

        $counts['simple']     = (int) ($num * $perventage['0'] /100); 
        $counts['normal']   = (int) ($num * $perventage['1'] /100); 
        $counts['difficulty'] = (int) ($num * $perventage['2'] /100); 

        $counts['otherCount'] = $num - ($counts['simple'] + $counts['normal'] + $counts['difficulty']);

        return $counts;
    }

    private function getQuestionService()
    {
        return $this->createService('Quiz.QuestionService');
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

}