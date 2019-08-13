<template>
  <div>
    <head-top></head-top>
    <fill-type :filldata="paperdata.items.fill[0]"></fill-type>
    <essay-type :essaydata="paperdata.items.essay[0]"></essay-type>
  </div>
</template>

<script>
import fillType from "./component/fill";
import essayType from "./component/essay";
import headTop from "./component/head";
export default {
  name: "testPaper",
  data() {
    return {
      paperdata: {
        testpaperResult: {
          id: "5",
          paperName: "测试试卷",
          testId: "1",
          userId: "2",
          courseId: "1",
          lessonId: "1",
          score: "0.0",
          objectiveScore: "0.0",
          subjectiveScore: "0.0",
          teacherSay: "",
          rightItemCount: "0",
          passedStatus: "none",
          limitedTime: "0",
          beginTime: "1533556877",
          endTime: "0",
          updateTime: "0",
          metas: [],
          active: "0",
          status: "doing",
          target: "",
          checkTeacherId: "0",
          checkedTime: "0",
          usedTime: "0",
          type: "testpaper",
          courseSetId: "1",
          migrateResultId: "0"
        },
        testpaper: {
          id: "1",
          name: "测试试卷",
          description: "<p>乏味而</p>\r\n",
          courseId: "0",
          lessonId: "0",
          limitedTime: "0",
          pattern: "questionType",
          target: "",
          status: "open",
          score: "14.0",
          passedCondition: ["0"],
          itemCount: "7",
          createdUserId: "2",
          createdTime: "1533111506",
          updatedUserId: "2",
          updatedTime: "1533111529",
          metas: {
            mode: "rand",
            ranges: {
              courseId: "0",
              lessonId: "0"
            },
            counts: {
              single_choice: 1,
              choice: 1,
              essay: 1,
              uncertain_choice: 1,
              determine: 1,
              fill: 1,
              material: 1
            },
            scores: {
              single_choice: "2",
              choice: "2",
              essay: "2",
              uncertain_choice: "2",
              determine: "2",
              fill: "2",
              material: "2"
            },
            missScores: {
              choice: "0",
              uncertain_choice: "0"
            },
            percentages: {
              simple: "",
              normal: "",
              difficulty: ""
            },
            question_type_seq: [
              "single_choice",
              "choice",
              "essay",
              "uncertain_choice",
              "determine",
              "fill",
              "material"
            ]
          },
          copyId: "0",
          type: "testpaper",
          courseSetId: "1",
          migrateTestId: "0"
        },
        items: {
          single_choice: [
            {
              id: "1",
              isDeleted: true,
              stem: "此题已删除",
              score: "2.0",
              type: "single_choice",
              seq: "1",
              missScore: "0.0"
            }
          ],
          choice: [
            {
              id: "2",
              type: "choice",
              stem:
                '<p>乏味<img alt="" src="http://develop.edusoho-dev.com/files/course/2018/08-01/161250231d0a214155.png" /></p>\r\n',
              score: "2.0",
              metas: {
                choices: [
                  "<p>奋斗为</p>\n",
                  "<p>的驱蚊器</p>\n",
                  "<p>我的娃</p>\n",
                  "<p>e呃呃呃去</p>\n"
                ]
              },
              categoryId: "0",
              difficulty: "normal",
              target: "course-1",
              courseId: "0",
              lessonId: "0",
              parentId: "0",
              subCount: "0",
              finishedTimes: "0",
              passedTimes: "0",
              createdUserId: "2",
              updatedUserId: "2",
              courseSetId: "1",
              seq: "2",
              missScore: "0.0"
            }
          ],
          essay: [
            {
              id: "3",
              type: "essay",
              stem:
                '<p>问答提<img alt="" src="http://develop.edusoho-dev.com/files/course/2018/08-01/1613371e5406809517.png" /></p>\r\n',
              score: "2.0",
              metas: [],
              categoryId: "0",
              difficulty: "normal",
              target: "course-1",
              courseId: "0",
              lessonId: "0",
              parentId: "0",
              subCount: "0",
              finishedTimes: "0",
              passedTimes: "0",
              createdUserId: "2",
              updatedUserId: "2",
              courseSetId: "1",
              seq: "3",
              missScore: "0.0",
              answers:''
            }
          ],
          uncertain_choice: [
            {
              id: "4",
              type: "uncertain_choice",
              stem:
                '<p>带我去俄方<img alt="" src="http://develop.edusoho-dev.com/files/course/2018/08-01/161421da6b94018498.png" /></p>\r\n',
              score: "2.0",
              metas: {
                choices: [
                  "<p>出现阿萨德 v&nbsp;</p>\r\n",
                  "<p>擦粉</p>\r\n",
                  "<p>的我</p>\r\n",
                  '<p>打完<img alt="" src="http://develop.edusoho-dev.com/files/course/2018/08-01/16144117b2ec825153.png" /></p>\r\n'
                ]
              },
              categoryId: "0",
              difficulty: "normal",
              target: "course-1",
              courseId: "0",
              lessonId: "0",
              parentId: "0",
              subCount: "0",
              finishedTimes: "0",
              passedTimes: "0",
              createdUserId: "2",
              updatedUserId: "2",
              courseSetId: "1",
              seq: "4",
              missScore: "0.0"
            }
          ],
          determine: [
            {
              id: "5",
              type: "determine",
              stem: "<p>奋斗哇恶补 v 呃</p>\r\n",
              score: "2.0",
              metas: [],
              categoryId: "0",
              difficulty: "normal",
              target: "course-1",
              courseId: "0",
              lessonId: "0",
              parentId: "0",
              subCount: "0",
              finishedTimes: "0",
              passedTimes: "0",
              createdUserId: "2",
              updatedUserId: "2",
              courseSetId: "1",
              seq: "5",
              missScore: "0.0"
            }
          ],
          fill: [
            {
              id: "6",
              type: "fill",
              stem:
                "<p>但是我奋斗的是[[我其实对方认为|v 奋斗色个人]]dwqfdwqer[[dewqad2w|dfwqe ]]</p>\r\n",
              score: "2.0",
              metas: [],
              categoryId: "0",
              difficulty: "normal",
              target: "course-1",
              courseId: "0",
              lessonId: "0",
              parentId: "0",
              subCount: "0",
              finishedTimes: "0",
              passedTimes: "0",
              createdUserId: "2",
              updatedUserId: "2",
              courseSetId: "1",
              seq: "6",
              missScore: "0.0",
              answers:['','']
            }
          ],
          material: [
            {
              id: "7",
              type: "material",
              stem:
                '<p>往日的完全不<img alt="" src="http://develop.edusoho-dev.com/files/course/2018/08-01/161701d2dc92623881.jpg" /></p>\r\n',
              score: "0.0",
              metas: [],
              categoryId: "0",
              difficulty: "normal",
              target: "course-1",
              courseId: "0",
              lessonId: "0",
              parentId: "0",
              subCount: "1",
              finishedTimes: "0",
              passedTimes: "0",
              createdUserId: "2",
              updatedUserId: "2",
              courseSetId: "1",
              seq: "7",
              missScore: "0.0",
              subs: [
                {
                  id: "8",
                  type: "single_choice",
                  stem: "<p>大额度</p>\r\n",
                  score: "2.0",
                  answer: ["0"],
                  analysis:
                    '<p>儿儿无关<img alt="" src="http://develop.edusoho-dev.com/files/course/2018/08-01/1617382743f1321043.jpg" /></p>\n',
                  metas: {
                    choices: [
                      "<p>个人更热</p>\r\n",
                      "<p>fewer</p>\r\n",
                      "<p>fe wfew发</p>\r\n",
                      "<p>氛围发</p>\r\n"
                    ]
                  },
                  categoryId: "0",
                  difficulty: "normal",
                  target: "course-1",
                  courseId: "0",
                  lessonId: "0",
                  parentId: "7",
                  subCount: "0",
                  finishedTimes: "0",
                  passedTimes: "0",
                  createdUserId: "2",
                  updatedUserId: "2",
                  courseSetId: "1",
                  seq: "7",
                  missScore: "0.0"
                }
              ]
            }
          ]
        }
      }
    };
  },
  components: {
    fillType,
    essayType,
    headTop
  }
};
</script>

<style>
</style>