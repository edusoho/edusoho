<template>
  <div
    class="ibs-background-color"
    id="item-bank-sdk-message"
    v-if="this.section_responses.length > 0"
  >
    <div>
      <img
        id="image"
        src="/static-dist/app/img/question-bank/testpaperAiIcon.png"
        alt=""
      />
    </div>

    <!--  -->
    <div
      v-show="!mobileShow"
      :class="{
        'ibs-item-preview-container':
          assessmentStatus === 'preview' || assessment.type === 'aiPersonality',
        'ibs-item-container':
          assessmentStatus !== 'preview' && assessment.type !== 'aiPersonality'
      }"
    >
      <div
        v-if="assessment.type === 'random' && assessmentStatus === 'preview'"
        class="ibs-item-header"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="14"
          height="14"
          viewBox="0 0 14 14"
          fill="none"
        >
          <path
            d="M7 0C3.13438 0 0 3.13438 0 7C0 10.8656 3.13438 14 7 14C10.8656 14 14 10.8656 14 7C14 3.13438 10.8656 0 7 0ZM7.5 10.375C7.5 10.4438 7.44375 10.5 7.375 10.5H6.625C6.55625 10.5 6.5 10.4438 6.5 10.375V6.125C6.5 6.05625 6.55625 6 6.625 6H7.375C7.44375 6 7.5 6.05625 7.5 6.125V10.375ZM7 5C6.80374 4.99599 6.61687 4.91522 6.47948 4.775C6.3421 4.63478 6.26515 4.4463 6.26515 4.25C6.26515 4.0537 6.3421 3.86522 6.47948 3.725C6.61687 3.58478 6.80374 3.50401 7 3.5C7.19626 3.50401 7.38313 3.58478 7.52052 3.725C7.6579 3.86522 7.73485 4.0537 7.73485 4.25C7.73485 4.4463 7.6579 4.63478 7.52052 4.775C7.38313 4.91522 7.19626 4.99599 7 5Z"
            fill="#46C37B"
          />
        </svg>
        <div class="ibs-item-header-text">
          <span>当前试卷为随机生成</span>
          <span class="ibs-item-header-number">{{
              randomAssessmentIds.length
            }}</span>
          <span>份试卷中的第</span>
          <span class="ibs-item-header-number">{{
              randomAssessmentIds.findIndex(id => id === assessment.id) + 1
            }}</span>
          <span>份，您可手动切换试卷</span>
        </div>
        <div class="ibs-item-header-select">
          <span>切换试卷： </span>
          <a-select
            placeholder="请选择试卷"
            :default-value="assessment.id"
            @change="changeAssessment"
            style="width: 110px"
          >
            <a-select-option
              v-for="(id, index) in randomAssessmentIds"
              :key="id"
              :value="id"
            >
              {{ `试卷 ${index + 1}` }}
            </a-select-option>
          </a-select>
        </div>
      </div>
      <a-alert
        v-if="assessment.type === 'aiPersonality'"
        message="个性卷练习 —— 提供量身定制的练习题库。根据您的掌握程度智能推荐，从基础巩固到高阶挑战，一步步见证学员的成长轨迹，让每一分努力都精准高效！"
        type="success"
        closable
        show-icon
      >
        <template slot="icon">
          <img
            src="/static-dist/app/img/question-bank/testpaperAiIcon.png"
            alt=""
          />
        </template>
      </a-alert>
      <div class="ibs-item-body">
        <div class="ibs-item-content">
          <div class="ibs-item-list ibs-assessment-info ibs-mb8">
            <div v-show="mode === 'do'" class="ibs-assessment-heading">
              <div class="ibs-assessment-title ibs-clearfix">
                <div
                  class="ibs-assessment-title__name ibs-left ibs-text-overflow"
                >
                  <a-tooltip placement="topLeft">
                    <template slot="title">
                      {{ assessment.name }}
                    </template>
                    {{ assessment.name }}
                  </a-tooltip>
                </div>
                <div class="ibs-right ibs-assessment-title__tag">
                  {{ t("itemEngine.status.do") }}
                </div>
              </div>
              <div
                class="ibs-assessment-description ibs-mt8 ibs-editor-text"
                v-html="assessment.description"
              ></div>
            </div>

            <div v-show="mode === 'analysis'" class="ibs-assessment-heading">
              <div class="ibs-assessment-title ibs-clearfix">
                <div
                  class="ibs-assessment-title__name ibs-left ibs-text-overflow"
                >
                  <a-tooltip placement="topLeft">
                    <template slot="title">
                      {{ assessment.name }}
                    </template>
                    {{ assessment.name }}
                  </a-tooltip>
                </div>
                <div class="ibs-right ibs-assessment-title__tag">
                  {{ t("itemEngine.status.analysis") }}
                </div>
              </div>
              <div
                class="ibs-assessment-description ibs-mt8 ibs-editor-text"
                v-html="assessment.description"
              ></div>
            </div>
            <result
              v-if="mode === 'report' || mode === 'review'"
              :answerReport="answerReport"
              :mode="mode"
              :needMarking="needMarking"
              :answerScene="answerScene"
              :needScore="needScore"
              :assessment="assessment"
              :answerRecord="answerRecord"
              :metaActivity="metaActivity"
              v-bind="$attrs"
              :media-type="mediaType"
              :finish-type="finishType"
              :submit-list="submitList"
              @view-historical-result="handleViewHistoricalResult"
            ></result>
          </div>

          <div
            v-for="(section, sectionsIndex) in sections"
            :key="'sections' + sectionsIndex"
            class="ibs-pb16 ibs-item-list"
            v-show="getTitle(sectionsIndex) && answerShow == 'show'"
          >
            <!-- 模块头部 -->
            <section-title
              :name="section.name"
              :count="section.question_count"
              :needScore="needScore"
              :score="section.total_score"
            ></section-title>
            <div
              v-for="(item, itemIndex) in section.items"
              :key="'item' + itemIndex"
              :class="getLastClass(itemIndex, section.items.length - 1)"
            >
              <!-- 材料题题干 -->
              <material-title
                v-show="
                  item.type === 'material' &&
                    getMaterial(sectionsIndex, itemIndex)
                "
                :material="item.material"
                :attachments="item.attachments"
              ></material-title>

              <!-- ------------题目区域------------ -->
              <div class="ibs-question-wrap">
                <div
                  v-for="(question, questionIndex) in item.questions"
                  :key="questionIndex"
                  :id="`ibs-${question.seq}`"
                  class="ibs-pl16 ibs-pr16 ibs-question-wrap__item"
                >
                  <judge-type
                    v-if="
                      question.answer_mode === 'true_false' &&
                        mode !== 'review' &&
                        getErrorData(sectionsIndex, itemIndex, questionIndex)
                    "
                    v-bind="$attrs"
                    :answerRecord="answerRecord"
                    :question="question"
                    :item="item"
                    :mode="mode"
                    :questionFavoritesItem="
                      getFavoritesQuestion(
                        sectionsIndex,
                        itemIndex,
                        questionIndex
                      )
                    "
                    :analysisQuestionInfo="
                      getAnalysisQuestion(
                        sectionsIndex,
                        itemIndex,
                        questionIndex
                      )
                    "
                    :keys="[sectionsIndex, itemIndex, questionIndex]"
                    :userAnwer="
                      getUserAnwer(sectionsIndex, itemIndex, questionIndex)
                    "
                    :reportAnswer="
                      getReportAnswer(sectionsIndex, itemIndex, questionIndex)
                    "
                    :needScore="needScore"
                    :doingLookAnalysis="doingLookAnalysis"
                    :assessmentStatus="assessmentStatus"
                    :recordStatus="recordStatus"
                    :section_responses="section_responses"
                    @changeAnswer="changeDetermine"
                    @changeTag="changeTag"
                    @changeCollect="changeCollect"
                    @setMaterialAnalysis="setMaterialAnalysis"
                    @prepareStudentAiAnalysis="prepareStudentAiAnalysis"
                    :isShowAiAnalysis="isShowAiAnalysis"
                  ></judge-type>
                  <single-choice
                    v-if="
                      question.answer_mode === 'single_choice' &&
                        mode !== 'review' &&
                        getErrorData(sectionsIndex, itemIndex, questionIndex)
                    "
                    v-bind="$attrs"
                    :answerRecord="answerRecord"
                    :question="question"
                    :item="item"
                    :mode="mode"
                    :questionFavoritesItem="
                      getFavoritesQuestion(
                        sectionsIndex,
                        itemIndex,
                        questionIndex
                      )
                    "
                    :analysisQuestionInfo="
                      getAnalysisQuestion(
                        sectionsIndex,
                        itemIndex,
                        questionIndex
                      )
                    "
                    :keys="[sectionsIndex, itemIndex, questionIndex]"
                    :userAnwer="
                      getUserAnwer(sectionsIndex, itemIndex, questionIndex)
                    "
                    :reportAnswer="
                      getReportAnswer(sectionsIndex, itemIndex, questionIndex)
                    "
                    :doingLookAnalysis="doingLookAnalysis"
                    :needScore="needScore"
                    :assessmentStatus="assessmentStatus"
                    :recordStatus="recordStatus"
                    :section_responses="section_responses"
                    @changeAnswer="changeDetermine"
                    @changeTag="changeTag"
                    @changeCollect="changeCollect"
                    @setMaterialAnalysis="setMaterialAnalysis"
                    @prepareStudentAiAnalysis="prepareStudentAiAnalysis"
                    :isShowAiAnalysis="isShowAiAnalysis"
                  ></single-choice>
                  <choice
                    v-if="
                      (question.answer_mode === 'choice' ||
                        question.answer_mode === 'uncertain_choice') &&
                        mode !== 'review' &&
                        getErrorData(sectionsIndex, itemIndex, questionIndex)
                    "
                    v-bind="$attrs"
                    :answerRecord="answerRecord"
                    :question="question"
                    :item="item"
                    :mode="mode"
                    :analysisQuestionInfo="
                      getAnalysisQuestion(
                        sectionsIndex,
                        itemIndex,
                        questionIndex
                      )
                    "
                    :questionFavoritesItem="
                      getFavoritesQuestion(
                        sectionsIndex,
                        itemIndex,
                        questionIndex
                      )
                    "
                    :keys="[sectionsIndex, itemIndex, questionIndex]"
                    :userAnwer="
                      getUserAnwer(sectionsIndex, itemIndex, questionIndex)
                    "
                    :reportAnswer="
                      getReportAnswer(sectionsIndex, itemIndex, questionIndex)
                    "
                    :needScore="needScore"
                    :doingLookAnalysis="doingLookAnalysis"
                    :assessmentStatus="assessmentStatus"
                    :recordStatus="recordStatus"
                    :section_responses="section_responses"
                    @changeAnswer="changeChoice"
                    @changeTag="changeTag"
                    @changeCollect="changeCollect"
                    @setMaterialAnalysis="setMaterialAnalysis"
                    @prepareStudentAiAnalysis="prepareStudentAiAnalysis"
                    :isShowAiAnalysis="isShowAiAnalysis"
                  ></choice>
                  <essay
                    v-if="
                      getEssayItem(
                        question,
                        sectionsIndex,
                        itemIndex,
                        questionIndex
                      )
                    "
                    v-bind="$attrs"
                    :answerRecord="answerRecord"
                    :question="question"
                    :item="item"
                    :mode="mode"
                    :userAttachments="
                      getUserAttachment(sectionsIndex, itemIndex, questionIndex)
                    "
                    :questionFavoritesItem="
                      getFavoritesQuestion(
                        sectionsIndex,
                        itemIndex,
                        questionIndex
                      )
                    "
                    :analysisQuestionInfo="
                      getAnalysisQuestion(
                        sectionsIndex,
                        itemIndex,
                        questionIndex
                      )
                    "
                    :keys="[sectionsIndex, itemIndex, questionIndex]"
                    :userAnwer="
                      getUserAnwer(sectionsIndex, itemIndex, questionIndex)
                    "
                    :reportAnswer="
                      getReportAnswer(sectionsIndex, itemIndex, questionIndex)
                    "
                    :needMarking="needMarking"
                    :needScore="needScore"
                    :doingLookAnalysis="doingLookAnalysis"
                    :assessmentStatus="assessmentStatus"
                    :recordStatus="recordStatus"
                    :uploadSDKInitData="uploadSDKInitData"
                    :section_responses="section_responses"
                    @changeAnswer="changeEssay"
                    @changeTag="changeTag"
                    @changeCollect="changeCollect"
                    @getEssayAttachment="getEssayAttachment"
                    @deleteEssayAttachment="deleteEssayAttachment"
                    @setMaterialAnalysis="setMaterialAnalysis"
                    @prepareStudentAiAnalysis="prepareStudentAiAnalysis"
                    :isShowAiAnalysis="isShowAiAnalysis"
                  >
                    <template v-slot:review>
                      <review
                        v-bind="$attrs"
                        :keys="[sectionsIndex, itemIndex, questionIndex]"
                        :id="
                          getReportAnswerId(
                            sectionsIndex,
                            itemIndex,
                            questionIndex
                          )
                        "
                        :needScore="needScore"
                        :question="question"
                        :review-type="reviewType"
                      ></review>
                    </template>
                  </essay>
                  <fill
                    v-if="
                      question.answer_mode === 'text' &&
                        mode !== 'review' &&
                        getErrorData(sectionsIndex, itemIndex, questionIndex)
                    "
                    v-bind="$attrs"
                    :analysisQuestionInfo="
                      getAnalysisQuestion(
                        sectionsIndex,
                        itemIndex,
                        questionIndex
                      )
                    "
                    :answerRecord="answerRecord"
                    :question="question"
                    :item="item"
                    :mode="mode"
                    :questionFavoritesItem="
                      getFavoritesQuestion(
                        sectionsIndex,
                        itemIndex,
                        questionIndex
                      )
                    "
                    :keys="[sectionsIndex, itemIndex, questionIndex]"
                    :userAnwer="
                      getUserAnwer(sectionsIndex, itemIndex, questionIndex)
                    "
                    :reportAnswer="
                      getReportAnswer(sectionsIndex, itemIndex, questionIndex)
                    "
                    :needScore="needScore"
                    :doingLookAnalysis="doingLookAnalysis"
                    :assessmentStatus="assessmentStatus"
                    :recordStatus="recordStatus"
                    :showErrorCorrection="isErrorCorrection"
                    :section_responses="section_responses"
                    @changeAnswer="changeFill"
                    @changeTag="changeTag"
                    @changeCollect="changeCollect"
                    @setMaterialAnalysis="setMaterialAnalysis"
                    @error-correction="errorCorrection"
                    @prepareStudentAiAnalysis="prepareStudentAiAnalysis"
                    :isShowAiAnalysis="isShowAiAnalysis"
                  ></fill>
                  <answer-model
                    v-if="
                      Number(question.isDelete) &&
                        mode !== 'review' &&
                        getErrorData(sectionsIndex, itemIndex, questionIndex)
                    "
                    :question="question"
                    :mode="mode"
                    :needScore="needScore"
                    :assessmentStatus="assessmentStatus"
                    :recordStatus="recordStatus"
                    :itemType="item.type"
                  ></answer-model>
                </div>
              </div>
              <!-- ------------题目区域------------ -->

              <!-- 材料题解析，后续考虑和材料题题干放在一起 -->
              <material-analysis
                v-show="showMaterialAnalysis(item, sectionsIndex, itemIndex)"
                :courseSetStatus="courseSetStatus"
                :attachments="item.attachments"
                :analysis="`${item.analysis || t('itemReport.no_analysis')}`"
              ></material-analysis>
            </div>
          </div>
          <slot name="review-footer" />
        </div>
        <slot name="pendant">
          <div class="ibs-item-pendant">
            <a-affix :offset-top="0">
              <count-down
                :mode="mode"
                :limitedTime="Number(answerScene.limited_time)"
                :beginTime="Number(answerRecord.begin_time)"
                :endTime="Number(answerScene.end_time)"
                :assessmentStatus="assessmentStatus"
                :getCurrentTime="getCurrentTime"
                :validPeriodMode="answerScene.valid_period_mode"
                v-if="
                  answerRecord.exam_mode == '0'
                  && (Number(answerScene.limited_time) || answerScene.valid_period_mode == '3')
                  && assessmentStatus !== 'preview'
                  && mode === 'do'
                "
                @reachTimeSubmitAnswerData="reachTimeSubmitAnswerData"
              ></count-down>
              <div
                v-if="
                  answerRecord.exam_mode == '1' &&
                    assessmentStatus !== 'preview' &&
                    mode === 'do'
                "
                class="ibs-assessment-timer"
              >
                <img class="clock-icon" :src="clockIcon" />
                <span>{{ used_time }}</span>
              </div>
              <card
                :mode="mode"
                :assessmentStatus="assessmentStatus"
                :sections="sections"
                :metaActivity="metaActivity"
                :section_responses="section_responses"
                :answerRecord="answerRecord"
                :answerScene="answerScene"
                :canDoAgain="canDoAgain"
                :section_reports="section_reports"
                :answerShow="answerShow"
                :doTimes="doTimes"
                :showSaveProgressBtn="showSaveProgressBtn"
                :showDoAgainBtn="showDoAgainBtn"
                :assessmentResponses="assessmentResponses"
                @showError="getVal"
                @answerData="answerData"
                @saveAnswerData="saveAnswerData"
                @exitAnswer="exitAnswer"
                :cardIsShow="enableFacein"
                :exercise="exercise"
                :courseSetStatus="courseSetStatus"
              >
                <template #returnBtn>
                  <slot name="returnBtn"></slot>
                </template>
              </card>
              <slot name="inspection"></slot>
            </a-affix>
          </div>
        </slot>
      </div>
      <a-modal
        :closable="closable"
        :visible="visible"
        :title="t('itemEngine.testpaperIntro')"
        :footer="null"
        :getContainer="getContainer"
      >
        <div class="ibs-tip-content">
          {{ t("itemEngine.unstartedTip") }}
          {{ Number(answerScene.start_time) | date }}
          {{ t("itemEngine.unstartedNextTip") }}
        </div>
      </a-modal>
    </div>

    <a-modal
      :closable="closable"
      :maskClosable="false"
      :visible="mobileShow"
      :getContainer="getContainer"
    >
      <div>
        {{ t("itemEngine.InspectionMobileTip") }}
      </div>
      <template slot="footer">
        <a-button type="primary" @click="goBack">
          {{ t("Back") }}
        </a-button>
      </template>
    </a-modal>
  </div>
</template>

<script>
import result from "./item-engine-components/result";
import card from "./item-engine-components/card";
import answerModel from "./item-engine-components/answer-model";
import judgeType from "./item-engine-components/judge";
import singleChoice from "./item-engine-components/single-choice";
import choice from "./item-engine-components/choice";
import essay from "./item-engine-components/essay";
import fill from "./item-engine-components/fill";
import sectionTitle from "./item-engine-components/section-title";
import materialTitle from "./item-engine-components/material-title";
import materialAnalysis from "./item-engine-components/material-analysis";
import review from "./item-engine-components/review";
import countDown from "./item-engine-components/count-down";
import Locale from "common/vue/mixins/locale";
import {
  timeStampFormatTime,
  isMobileDevice,
  getCountDown
} from 'common/date-toolkit';

import Viewer from 'viewerjs';

let orderNum = 1;
const baseCKEditorData = {
  publicPath: `${process.env.BASE_URL}/es-ckeditor/ckeditor.js`,
  fileSingleSizeLimit: 10,
  filebrowserImageUploadUrl: "",
  filebrowserImageDownloadUrl: "",
  language: "zh-cn",
  jqueryPath: "https://cdn.bootcss.com/jquery/3.4.1/jquery.js"
};

const clockIcon = `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGgAAABoCAMAAAAqwkWTAAAAP1BMVEX///9mZmafn5/s7OxwcHB5eXmMjIz19fXGxsaysrK8vLyDg4PZ2dnj4+OoqKizs7OpqamWlpbPz8/Y2Nji4uLjiUH7AAAE6klEQVRo3u1Z2baiMBCULJ2FEBb9/2+dKyKVINkcnTMPt54EIUV3qjud9OUX/wjqatgPzKIu3wT3onuC8cvXYEUXQNjLl2C6A8zlK7DdC75iE9/81rMf9Jv3vjFPbB1auo1Wrpf+8nGoB4/ar1cm8XmVLytR4Cq33lgN/Lzk+vBOD+F9fIrY651fov+NiDtt7+nbG6PpW0SnmNVLwJmZDH87XK3sEiAdjTqINbCHt2iu1GUxLyHPCvEGk+67IqRG5t2YllYaeRiS2B0zyRMqHt7ULTQDdUDvD9Ox+D6k4huPEK2rlZowDNnTRM014RmxTc8g2lZg3u8jjCrz2BR70cGFdTZpAZqGidRYF+uWK3NKwxdt/D0zWHcLnx53qUcrsKvPBMQRTobieossyPjzBRYulLqWR1hkFnkaQJ4fqz4WBK6r5JG8lBtQskICbA9cWclDahsBNFkqNT+vJb6zQgfseSk6QMiZ/YB6cVazTnFA1ZWkI6Jpg2Bhahg0k0FaiNRX5kFinBBND9DJzC4MJX/g9TohyDAeTGmXwtnBfVSbUw1KUvAQz3iAIiaFD82Ch9W7qdsJWSRRuH6ocpwFT1mliCATiIkK6fEpbKivV/WJXgfTpCsM4nABeLJQDyYRvCnLBo3gBE8dUw+fZ02S+BSD+aljkpgmJUB6CuR2qK8E7j3f1IY3TCFkZxjEKtfiQTwGh9pgEuVjaMJPquHBVpYedsAklZUCh0G8hgdEPDAp5RB8EeEVVseDmWSwgzIeUZDCVGXQcFx3OIRnM767YnQJg2p4dtCuJpXR3bSL/7Y+tTTyxGcCffrAg/a/bC6FXC3z1kU8gNh9N6UnSexmZHaNi9xS+gsP5EC7hEVaC8NOqVPuBWIeDA9h8KQWMEVDM088fFINep+Ya8pqneeJ/S9TbjG7e20q98qt6pplshDF8ITccEI0gzLlXKZ+5pOlHDPvsmPrr5T/WfwLgOoV0rNNVNM+ebKCP3KPTJvN6Wdw/30i3IeL/PtEZdcJhYW77DqfEgOLZRHDVYiBdgnMKTFYZI+sdjs5s/Q+q18tBWU2YJdUwC5dBJ1KmA4RlU5ByCOlFDQd/0X2yqYgDF9OquBJJtVbeokVcfZg2bMLuaR3vwT/FxY+g4de4axn1uVKXYuFL7OUv31KD7UMhaXcwasSn9MEtiuXp741LvoMhNECjnJS50pVOhSa7L2jZJ4vIOOij5pNQn2LnzrbIzKYr6ZZwteVS11Ceqb2Jp4N/C3zRwAOJmFbVQsuYIUulbo9TMLBRiWPPO5/ZXGzbEDaulmWFZtlfIr42+0/OAsmUVgu9ryOB+JhZYNitY2tRzQj1Fc85hzCzeLYdug0xuorYArVNtYeo4FHQX1ldyMpjJUHgxh6huPqAo/BLRvVkmthCQ2HVAU61GYw0Rsk07ew0bJWXbE0TX33A/6acAmIntZGlRThzTE+MB8bW5SzwjykAako6lrXMbQMylTkXloGQHsThPvzJsioTpogzUyxrAd7bOuMLoymVh5IAOoDmbOPRpVeeNROFoimVliBzmcKoEE0tYPLoB1VpkE0tWNMtcTBYgnPTKqdAkYBvV9u0Z86avhSvknQ3sKe2R0nLWzgbaoietD8DZY5yYDc0I5CSxxAE/2jUM6TiDkEeQeWD7Npc88MzFjt+OW/xB9y8yiXTVs2xQAAAABJRU5ErkJggg==`;

export default {
  name: "item-engine",
  mixins: [Locale],
  inheritAttrs: false,
  components: {
    judgeType,
    card,
    singleChoice,
    choice,
    essay,
    fill,
    sectionTitle,
    materialTitle,
    materialAnalysis,
    review,
    result,
    countDown,
    answerModel
  },
  data() {
    return {
      randomAssessmentIds: [],
      sections: this.assessment.sections,
      responses: this.assessmentResponse.section_responses || [],
      analysis_reports: this.answerSceneReport.question_reports || [],
      section_responses: [],
      section_reports: this.answerReport.section_reports,
      isError: false,
      enableFacein: Number(this.answerScene.enable_facein),
      count: 0,
      intervalId: null,
      saveDataIntervalId: null,
      visible: false,
      closable: false,
      needMarking: Number(this.answerScene.manual_marking),
      needScore: Number(this.answerScene.need_score),
      doTimes: Number(this.answerScene.do_times),
      canDoAgain: this.answerScene.canDoAgain,
      recordStatus: this.answerRecord.status,
      collectList: [],
      mobileShow: false,
      reviewType: this.answerScene.reviewType,
      used_time: "00:00:00",
      clockIcon
    };
  },
  props: {
    getCurrentTime: {},
    // 模式 preview:预览模式 report:答题结果模式 do:做题模式 review:批阅模式
    mode: {
      type: String,
      default: "do"
    },
    courseId: {
      type: String,
      default: ""
    },
    exerciseId: {
      type: String,
      default: ""
    },
    type: {
      type: String,
      default: ""
    },
    showCKEditorData: {
      type: Object,
      default() {
        return {};
      }
    },
    answerReport: {
      type: Object,
      default() {
        return {};
      }
    },
    exercise: {
      type: Object,
      default() {
        return {};
      }
    },
    answerSceneReport: {
      type: Object,
      default() {
        return {};
      }
    },
    //暂存数据
    assessmentResponse: {
      type: Object,
      default() {
        return {};
      }
    },
    //试卷
    assessment: {
      type: Object,
      default() {
        return {};
      }
    },
    //任务信息
    metaActivity: {
      type: Object,
      default() {
        return {};
      }
    },
    //答题记录
    answerRecord: {
      type: Object,
      default() {
        return {};
      }
    },
    //courseSet状态
    courseSetStatus: {
      type: String,
      default() {
        return "1";
      }
    },
    //答题场次
    answerScene: {
      type: Object,
      default() {
        return {};
      }
    },
    //是否显示答案
    answerShow: {
      type: String,
      default() {
        return "show";
      }
    },
    //试卷预览
    assessmentStatus: {
      type: String,
      default() {
        return "";
      }
    },
    showAttachment: {
      type: String,
      default() {
        return "0";
      }
    },
    uploadSDKInitData: {
      type: Object,
      default() {
        return {};
      }
    },
    cdnHost: {
      type: String,
      default() {
        return "service-cdn.qiqiuyun.net";
      }
    },
    questionFavorites: {
      type: Array,
      default() {
        return [];
      }
    },
    deleteAttachmentCallback: {
      type: Function,
      default() {
        return new Promise(resolve => {
          resolve();
        });
      }
    },
    previewAttachmentCallback: {
      type: Function,
      default() {
        return new Promise(resolve => {
          resolve();
        });
      }
    },
    downloadAttachmentCallback: {
      type: Function,
      default() {
        return new Promise(resolve => {
          resolve();
        });
      }
    },
    inpectConstrolCallback: {
      type: Function,
      default() {
        return new Promise(resolve => {
          resolve();
        });
      }
    },
    showSaveProgressBtn: {
      type: Number,
      default: 1
    },
    showDoAgainBtn: {
      type: Number,
      default: 1
    },
    isErrorCorrection: {
      type: String,
      default: "0"
    },
    mediaType: {
      type: String,
      default: ""
    },

    finishType: {
      type: String,
      default: ""
    },

    submitList: {
      type: Array,
      default() {
        return [];
      }
    },

    isDownload: {
      type: Boolean,
      default: false
    },
    assessmentResponses: {
      type: Object,
      default() {
        return {};
      }
    },
    isShowAiAnalysis: {
      type: Boolean,
      default: true
    },
  },
  computed: {
    CKEditorData: function() {
      if (Object.keys(this.showCKEditorData).length) {
        return Object.assign(baseCKEditorData, this.showCKEditorData);
      }
      return baseCKEditorData;
    },
    widthCol: function() {
      return this.mode !== "analysis" ? 14 : "";
    },
    offsetCol: function() {
      return this.mode !== "analysis" ? 2 : 0;
    },
    //是否允许做题时查看解析
    doingLookAnalysis: function() {
      return (
        !!Number(this.answerScene.doing_look_analysis) && this.mode === "do"
      );
    },
    localUsedTime() {
      return `${this.answerRecord.id}-usedTime`;
    }
  },
  watch: {
    section_responses: {
      handler: function(val) {
        console.log(val);
      },
      deep: true
    }
  },
  filters: {
    date: function(timeStamp) {
      return timeStampFormatTime(timeStamp);
    }
  },
  provide() {
    return {
      showCKEditorData: this.CKEditorData,
      showAttachment: Number(this.showAttachment),
      cdnHost: this.cdnHost,
      deleteAttachmentCallback: this.deleteAttachmentCallback,
      previewAttachmentCallback: this.previewAttachmentCallback,
      downloadAttachmentCallback: this.downloadAttachmentCallback,
      isDownload: this.isDownload
    };
  },
  mounted() {
    // const viewer = new Viewer(document.getElementById('image'), {
    //   inline: true,
    //   viewed() {
    //     viewer.zoomTo(1);
    //   },
    // });

    //如果有暂存数据
    if (this.questionFavorites.length > 0) {
      this.collectList = this.questionFavorites;
    }

    if (this.responses.length > 0) {
      this.section_responses = this.responses;
    } else {
      this.formateSections();
    }

    if (
      this.mode === "do" &&
      this.assessmentStatus !== "preview" &&
      !this.visible
    ) {
      this.countTime();
      this.trigger();
    }

    if (
      this.enableFacein &&
      isMobileDevice() &&
      this.mode === "do" &&
      this.assessmentStatus !== "preview"
    ) {
      this.mobileShow = true;
    }

    // 是否提示考试时间未到
    this.getStartTime();
  },
  created() {
    this.$on("previewFile", this.previewAttachment);
    this.$on("downloadFile", this.downloadAttachment);
    this.$on("getDeleteFile", this.deleteAttachment);
    const ids = document.getElementById("randomAssessmentIds");
    if (ids) {
      this.randomAssessmentIds = JSON.parse(ids.value);
    }
  },
  beforeDestroy() {
    if (this.intervalId) {
      clearInterval(this.intervalId);
    }
    if (this.saveDataIntervalId) {
      clearInterval(this.saveDataIntervalId);
    }
  },
  methods: {
    //遍历获取答案体结构
    formateSections() {
      this.sections.forEach(item => {
        this.section_responses.push({
          section_id: item.id,
          item_responses: this.formateItems(item.items)
        });
      });
    },

    formateItems(items) {
      let item_responses = [];
      items.forEach(item => {
        item_responses.push({
          item_id: item.id,
          question_responses: this.formateQuestions(item.questions)
        });
      });
      return item_responses;
    },
    formateQuestions(questions) {
      let question_responses = [];
      questions.forEach(item => {
        item.orderNum = orderNum++;
        if (Number(item.isDelete) == 1) {
          item.response_points = [];
        }
        const length = item.response_points.length
          ? item.response_points.length
          : 0;
        question_responses.push({
          question_id: item.id,
          response: this.initResponse(item.answer_mode, length),
          attachments: [],
          isTag: false
        });
      });
      return question_responses;
    },

    //初始化答案数据
    initResponse(mode, lengths) {
      let response = [];
      if (mode === "text") {
        response = Array(lengths).fill("");
      } else if (
        mode === "single_choice" ||
        mode === "rich_text" ||
        mode === "true_false"
      ) {
        response = [""];
      }
      return response;
    },
    getVal(msg) {
      this.isError = msg;
    },
    exitAnswer() {
      this.$emit("exitAnswer");
    },
    answerData() {
      const finalData = this.getResponse();
      finalData.used_time = localStorage.getItem(this.localUsedTime) || 0;
      this.$emit("getAnswerData", finalData);
      // 立即提交的时候清空计时器;
      clearInterval(this.saveDataIntervalId);
      this.saveDataIntervalId = null;
      clearInterval(this.intervalId); //清除计时器
      this.intervalId = null; //设置为null
    },
    reachTimeSubmitAnswerData() {
      const finalData = this.getResponse();
      finalData.used_time = localStorage.getItem(this.localUsedTime) || 0;
      this.$emit("reachTimeSubmitAnswerData", finalData);
    },
    saveAnswerData(okCallback) {
      const finalData = this.getResponse();
      finalData.used_time = localStorage.getItem(this.localUsedTime) || 0;
      this.$emit("saveAnswerData", finalData, okCallback);
    },
    countTime() {
      if (this.intervalId != null) {
        return;
      }

      const usedTime = Number(this.answerRecord.used_time);
      const localUsedTime = localStorage.getItem(this.localUsedTime) || 0;
      let time = Math.max(usedTime, localUsedTime);

      if (usedTime > localUsedTime) {
        localStorage.setItem(this.localUsedTime, usedTime);
      }

      const self = this;
      this.intervalId = setInterval(() => {
        localStorage.setItem(this.localUsedTime, ++time);

        const { hours, minutes, seconds } = getCountDown(time * 1000, 0);
        this.used_time = `${hours}:${minutes}:${seconds}`;

        if (
          this.answerRecord.exam_mode == "1" &&
          this.answerScene.limited_time > 0 &&
          time / 60 == this.answerScene.limited_time
        ) {
          const timeReach = this.t("itemEngine.timeReach")(
            Math.floor(time / 60)
          );
          this.$confirm({
            content: () => <strong>{timeReach}</strong>,
            icon: "",
            okText: this.t("itemEngine.goThenDo"),
            cancelText: this.t("testpaper.submit"),
            class: "ibs-card-confirm-modal",
            onOk() {
              self.forceRemoveModalDom();
            },
            onCancel() {
              self.answerData();
              self.forceRemoveModalDom();
            }
          });
        }
      }, 1000);
    },
    forceRemoveModalDom() {
      const modal = document.querySelector(".ant-modal-root");

      if (modal) {
        modal.remove();
      }

      document.body.style = "";
    },
    trigger() {
      const data = this.getResponse();
      const self = this;
      this.saveDataIntervalId = setInterval(() => {
        data.used_time = localStorage.getItem(this.localUsedTime) || 0;
        self.$emit("timeSaveAnswerData", data);
      }, 30 * 1000);
    },
    getStartTime() {
      if (
        this.mode === "do" &&
        this.assessmentStatus !== "preview" &&
        Number(this.answerScene.start_time)
      ) {
        const now = this.getCurrentTime
          ? this.getCurrentTime()
          : Date.parse(new Date());
        const startTime = Number(this.answerScene.start_time) * 1000;
        const timeSpace = now < startTime;
        if (timeSpace) {
          this.visible = true;
        } else {
          this.visible = false;
        }
      }
    },
    getResponse() {
      const finalData = {};
      finalData.assessment_id = this.assessment.id;
      finalData.answer_record_id = this.answerRecord.id;
      finalData.section_responses = this.section_responses;
      finalData.type = this.type;
      finalData.courseId = this.courseId;
      finalData.exerciseId = this.exerciseId;
      return finalData;
    },
    changeDetermine(value, keys) {
      this.section_responses[keys[0]].item_responses[
        keys[1]
      ].question_responses[keys[2]].response = [value];
    },
    changeChoice(value, keys) {
      this.section_responses[keys[0]].item_responses[
        keys[1]
      ].question_responses[keys[2]].response = value;
    },
    changeEssay(value, keys) {
      this.section_responses[keys[0]].item_responses[
        keys[1]
      ].question_responses[keys[2]].response = [value];
    },
    changeFill(value, keys) {
      this.section_responses[keys[0]].item_responses[
        keys[1]
      ].question_responses[keys[2]].response = value;
    },
    changeTag(value, keys) {
      if (!Array.isArray(keys) || keys.length !== 3) return;

      this.$set(
        this.section_responses[keys[0]].item_responses[keys[1]]
          .question_responses[keys[2]],
        "isTag",
        value
      );
    },
    changeCollect(value, collectStatus) {
      const collectItem = Object.assign(value, {
        target_id: this.assessment.id
      });
      if (collectStatus) {
        this.collectList.push(collectItem);
        this.$emit("collectUpdateEvent", collectItem);
      } else {
        this.collectList = this.collectList.filter(collectItem => {
          return collectItem.question_id !== value.question_id;
        });
        this.$emit("cancelCollectEvent", collectItem);
      }
      console.log(this.collectList);
    },
    getUserAnwer(s, i, q) {
      return this.section_responses[s].item_responses[i].question_responses[q]
        .response;
    },
    getUserAttachment(s, i, q) {
      return this.section_responses[s].item_responses[i].question_responses[q]
        .attachments;
    },
    getReportAnswer(s, i, q) {
      if (this.mode == "report" || this.mode == "review") {
        try {
          return this.section_reports[s].item_reports[i].question_reports[q];
        } catch (e) {
          console.log(e);
        }
      } else {
        return {};
      }
    },
    getReportAnswerId(s, i, q) {
      if (this.mode == "report" || this.mode == "review") {
        return this.section_reports[s].item_reports[i].question_reports[q].id;
      } else {
        return "";
      }
    },
    getErrorData(s, i, q) {
      if (this.mode !== "report") {
        return true;
      } else {
        if (!this.isError) {
          return true;
        } else {
          const status = this.getReportAnswer(s, i, q).status;
          if (status === "wrong" || status === "part_right") {
            return true;
          } else {
            return false;
          }
        }
      }
    },
    getReviewMaterial(s, i) {
      const essayData = this.section_reports[s].item_reports[
        i
      ].question_reports.map(item => {
        return item.status === "reviewing";
      });
      return essayData.indexOf(true) > -1 ? true : false;
    },
    getMaterial(s, i) {
      if (this.mode == "report") {
        return this.getErrorMaterial(s, i);
      } else if (this.mode == "review") {
        return this.getReviewMaterial(s, i);
      } else {
        return true;
      }
    },
    getErrorMaterial(s, i) {
      if (!this.isError) {
        return true;
      }
      const errorData = this.section_reports[s].item_reports[
        i
      ].question_reports.map(question => {
        return question.status === "wrong" || question.status === "part_right";
      });
      return errorData.indexOf(true) > -1 ? true : false;
    },
    getTitle(s) {
      if (this.mode == "report") {
        return this.getErrorTitle(s);
      } else if (this.mode == "review") {
        return this.getReviewTitle(s);
      } else {
        return true;
      }
    },
    getErrorTitle(s) {
      if (!this.isError) {
        return true;
      }
      const errorData = this.section_reports[s].item_reports.map(item => {
        const status = item.question_reports.map(question => {
          return (
            question.status === "wrong" || question.status === "part_right"
          );
        });
        const result = status.join(" ");
        return result;
      });
      return errorData.join(" ").indexOf(true) > -1 ? true : false;
    },
    getReviewTitle(s) {
      const essayData = this.section_reports[s].item_reports.map(item => {
        const status = item.question_reports.map(question => {
          return question.status === "reviewing";
        });
        const result = status.join(" ");
        return result;
      });

      return essayData.join(" ").indexOf(true) > -1 ? true : false;
    },
    getContainer() {
      return document.getElementById("item-bank-sdk-message");
    },
    getEssayAttachment(file, keys) {
      this.section_responses[keys[0]].item_responses[
        keys[1]
      ].question_responses[keys[2]].attachments.push(file);
    },
    deleteEssayAttachment(fileId, keys) {
      const attachments = this.section_responses[keys[0]].item_responses[
        keys[1]
      ].question_responses[keys[2]].attachments;

      this.section_responses[keys[0]].item_responses[
        keys[1]
      ].question_responses[keys[2]].attachments = attachments.filter(
        item => item.id !== fileId
      );
    },
    previewAttachment(fileId) {
      this.$emit("previewAttachment", fileId);
    },
    downloadAttachment(fileId) {
      this.$emit("downloadAttachment", fileId);
    },
    deleteAttachment({ fileId }) {
      this.$emit("deleteAttachment", fileId);
    },
    getFavoritesQuestion(s, i, q) {
      const [item] = this.questionFavorites.filter(question => {
        return (
          question.question_id == this.sections[s].items[i].questions[q].id
        );
      });
      return item;
    },
    getAnalysisQuestion(s, i, q) {
      if (this.mode !== "analysis") return {};
      const [item] = this.analysis_reports.filter(question => {
        return (
          question.question_id == this.sections[s].items[i].questions[q].id
        );
      });
      return item;
    },
    getLastClass(q, length) {
      if (q === length) {
        return "ibs-item-last";
      } else {
        return "ibs-item-not-last";
      }
    },
    getEssayItem(question, s, i, q) {
      if (this.mode !== "review") {
        return (
          question.answer_mode === "rich_text" && this.getErrorData(s, i, q)
        );
      } else {
        return (
          this.section_reports[s].item_reports[i].question_reports[q].status ===
          "reviewing"
        );
      }
    },
    goBack() {
      window.history.go(-1);
    },
    setMaterialAnalysis(canShowAnalysis, keys) {
      this.$set(
        this.assessment.sections[keys[0]].items[keys[1]],
        "showMaterialAnalysis",
        canShowAnalysis
      );
    },
    showMaterialAnalysis(item, sectionsIndex, itemIndex) {
      if (item.type !== "material" || !item.analysis) {
        return;
      }

      const analysis = item.showMaterialAnalysis;

      if (this.doingLookAnalysis) {
        return analysis;
      }

      return (
        this.mode !== "do" &&
        this.mode !== "review" &&
        this.getMaterial(sectionsIndex, itemIndex)
      );
    },

    errorCorrection(params) {
      this.$emit("error-correction", params);
    },

    handleViewHistoricalResult(params) {
      this.$emit("view-historical-result", params);
    },
    prepareStudentAiAnalysis(gen) {
      gen({
        answerRecordId: this.answerRecord.id,
      });
    },
    changeAssessment(value) {
      if (parent !== window) {
        window.location.href = `${location.origin}${location.pathname}?preview=1&assessmentId=${value}`;
      } else {
        window.location.href = window.location.href.replace(
          `/testpaper/${this.assessment.id}`,
          `/testpaper/${value}`
        );
      }
    },
  }
};
</script>
