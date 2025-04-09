<template>
  <div class="ibs-engine">
    <a-popover placement="bottomRight" overlayClassName="ai-agent-custom-popover" :trigger="showTeacherQuestion() ? 'hover' : 'manual'">
      <template slot="content">
        <div class="ai-agent-popover-content">
          <button class="ai-agent-button" @click="showAgentSdk"><span>请教小知老师解题思路～</span></button>
          <img src="/static-dist/app/img/ai-agent/icon.png" alt="头像">
        </div>
      </template>
      <div class="ibs-engine-item">
        <a-row>
          <!-- 序号和分数 -->
          <a-col :sm="2" :xs="3" class="ibs-text-center" v-show="showScoreAndSeq">
            <div class="ibs-order">{{ question.seq }}</div>
            <span
              v-if="assessmentStatus === 'preview' && Number(!question.isDelete)"
              class="ibs-score-tag"
              style="display: inline-block;"
              v-html="getScoringRules"
            >
          </span>
            <span
              class="ibs-score-tag"
              v-else-if="needScore && Number(!question.isDelete)"
            >
            {{ question.score }}{{ t("itemEngine.score") }}
          </span>

            <a-button
              v-show="showTag"
              class="ibs-mt8 ibs-toggle-btn"
              size="small"
              type="primary"
              :ghost="this.isTag ? false : true"
              @click="changeTag"
            ><i :class="tagClass"></i>{{ tagText }}</a-button
            >
            <a-button
              v-show="showCollect"
              class="ibs-mt8 ibs-collect-btn"
              size="small"
              type="primary"
              :ghost="this.isCollect ? false : true"
              @click="changeCollect"
            ><i :class="collectClass"></i>{{ collectText }}</a-button
            >
          </a-col>
          <a-col
            :xs="showScoreAndSeq ? 21 : 24"
            :sm="showScoreAndSeq ? 22 : 24"
            v-if="Number(!question.isDelete)"
          >
            <!-- word导入解析错误 -->
            <div
              class="ibs-stem ibs-danger-color"
              v-if="
              (question.errors &&
                question.errors.stem &&
                question.errors.stem.code == 100001) ||
                !question.stem
            "
            >
              {{ t("itemEngine.stemErrorMessage") }}
            </div>
            <div
              v-else
              class="ibs-stem ibs-editor-text"
              v-html="replaceHtmlSpace(question.stem)"
            ></div>

            <div
              class="ibs-stem ibs-danger-color"
              v-if="
              question.errors &&
                question.errors.stem &&
                question.errors.stem.code == 100006
            "
            >
              {{ t("itemEngine.stemAnsweringArea") }}
            </div>

            <div
              v-if="getAttachmentTypeData('stem').length > 0"
              style="padding: 8px 8px 0;background-color: #f5f5f5;border: 1px solid #F2F3F5;border-radius: 6px;"
            >
              <attachment-preview
                v-for="(item, index) in getAttachmentTypeData('stem')"
                :index="index"
                :key="item.no"
                :cdnHost="cdnHost"
                :fileData="item"
                mode="do"
              ></attachment-preview>
            </div>

            <slot name="response_points"></slot>
            <div class="ibs-clearfix" v-if="showCollectBtn">
              <a-button
                class="ibs-right ibs-collect-toggle-btn"
                type="primary"
                ghost
                @click="lookAnalysis"
              >
                {{
                  canShowAnalysis
                    ? t("itemEngine.closeExplain")
                    : t("itemEngine.openExplain")
                }}
              </a-button>
            </div>

            <div v-show="showAnalysis">
              <!--对于章节练习题需要和解析一起展示答案-->
              <slot
                v-if="doingLookAnalysis"
                name="analysis_response_points"
              ></slot>
              <div class="ibs-explain ibs-mt16"
                   v-if="mode !== 'import' || !question.aiAnalysisEnable"
                   v-html="
                `<span class='ibs-label'>${t(
                  'itemEngine.Explain'
                )}</span><div class='ibs-content ibs-editor-text ibs-mr8'>${question.analysis ||
                  t('itemReport.no_analysis')}</div>`
              "
              ></div>
              <ai-analysis
                v-if="isShowAiAnalysis && question.aiAnalysisEnable && ['report', 'import'].includes(mode)"
                :questionId="question.id"
                @prepareStudentAiAnalysis="prepareStudentAiAnalysis"
                @prepareTeacherAiAnalysis="prepareTeacherAiAnalysis"
                @clearAnalysis="clearAnalysis"
                @appendAnalysis="appendAnalysis"
              ></ai-analysis>
              <a-textarea
                v-if="mode === 'import' && question.aiAnalysisEnable"
                :rows="4"
                :data-image-download-url="showCKEditorData.filebrowserImageDownloadUrl"
                :name="`analysis${seq}`"
              />
              <div
                v-if="getAttachmentTypeData('analysis').length > 0"
                class="ibs-mt16"
                style="padding: 8px 8px 0;background-color: #f5f5f5;border: 1px solid #F2F3F5;border-radius: 6px;"
              >
                <attachment-preview
                  v-for="item in getAttachmentTypeData('analysis')"
                  :key="item.no"
                  :fileData="item"
                  :cdnHost="cdnHost"
                  mode="do"
                ></attachment-preview>
              </div>
            </div>
          </a-col>
          <a-col
            :xs="showScoreAndSeq ? 21 : 24"
            :sm="showScoreAndSeq ? 22 : 24"
            class="ibs-text-center"
            v-else
          >{{ deleteTip }}</a-col
          >
        </a-row>
      </div>
    </a-popover>
  </div>
</template>

<script>
import attachmentPreview from "../attachment-preview/src/attachment.vue";
import AiAnalysis from "../ai-analysis";
import Emitter from "common/vue/mixins/emitter";
import Locale from "common/vue/mixins/locale";
import loadScript from "load-script";

export default {
  name: "answer-model",
  inheritAttrs: false,
  data() {
    return {
      aiAgentEnabled: null,
      isTag: false,
      isCollect: false,
      canShowAnalysis: false,
      analysisEditor: undefined,
      analysisEditBySelf: false,
    };
  },
  components: {
    AiAnalysis,
    attachmentPreview
  },
  mixins: [Emitter, Locale],
  computed: {
    tagText() {
      return this.isTag ? this.t("cancel") : this.t("itemEngine.remark");
    },
    tagClass() {
      return this.isTag
        ? "ib-icon ib-icon-bookmark"
        : "ib-icon ib-icon-bookmark-outline";
    },
    collectText() {
      return this.isCollect ? this.t("cancel") : this.t("itemEngine.collect");
    },
    collectClass() {
      return this.isCollect
        ? "ib-icon ib-icon-favorite"
        : "ib-icon ib-icon-favoriteoutline";
    },
    deleteTip() {
      return this.itemType === "material"
        ? this.t("itemEngine.questionIsDeleted")
        : this.t("itemEngine.itemIsDeleted");
    },
    showAnalysis() {
      if (this.doingLookAnalysis) {
        return this.canShowAnalysis;
      } else {
        return !['do', 'review'].includes(this.mode);
      }
    },
    showCollectBtn() {
      return this.doingLookAnalysis;
    },
    showCollect() {
      if (!this.Collect) {
        return false;
      }
      return (
        this.mode == "report" &&
        this.recordStatus == "finished" &&
        this.assessmentStatus !== "finished" &&
        Number(!this.question.isDelete)
      );
    },
    showTag() {
      return (
        this.mode == "do" &&
        this.assessmentStatus !== "preview" &&
        Number(!this.question.isDelete)
      );
    },
    getScoringRules() {
      const { score_rule, answer_mode, score } = this.question;

      if (answer_mode === "text") {
        return score_rule.scoreType === "question"
          ? `${this.t("itemEngine.perQuestion")}
          ${score_rule.score} ${this.t("itemEngine.score")}`
          : `${this.t("itemEngine.perEmpty")}
          ${score_rule.otherScore} ${this.t("itemEngine.score")}`;
      }
      if (["uncertain_choice", "choice"].includes(answer_mode)) {
        let content =
          score_rule.scoreType === "question"
            ? `${this.t("itemEngine.missedSelection")}
              ${score_rule.otherScore} ${this.t("itemEngine.score")}`
            : `${this.t("itemEngine.each")}
            ${score_rule.otherScore} ${this.t("itemEngine.score")}`;

        return `
          <span>${score}${this.t("itemEngine.score")}</span>
          <span style="display: inline-block; width: 100%;">${content}</span>
        `;
      }
      return score + this.t("itemEngine.score");
    }
  },
  inject: ["cdnHost", "previewAttachmentCallback", "showCKEditorData"],
  props: {
    answerRecord: {
      type: Object,
      default() {
        return {};
      }
    },
    question: {
      type: Object,
      default() {
        return {};
      }
    },
    needScore: {
      type: Number,
      default() {
        return 1;
      }
    },
    assessmentStatus: {
      type: String,
      default() {
        return "";
      }
    },
    recordStatus: {
      type: String,
      default() {
        return "";
      }
    },
    // 模式 preview:预览模式 report:答题结果模式 do:做题模式
    mode: {
      type: String,
      default: "preview"
    },
    showScoreAndSeq: {
      type: Boolean,
      default: true
    },
    questionFavoritesItem: {
      type: Object,
      default() {
        return {};
      }
    },
    itemType: {
      type: String,
      default() {
        return "";
      }
    },
    doingLookAnalysis: {
      type: Boolean,
      default: false
    },
    Collect: {
      type: Boolean,
      default: true
    },
    keys: {
      type: Array,
      default() {
        return [];
      }
    },
    seq: {
      type: String,
    },
    section_responses: {
      type: Array,
      default() {
        return [];
      }
    },
    isShowAiAnalysis: {
      type: Boolean,
      default: true,
    },
  },
  mounted() {
    this.aiAgentEnabled = window.parent.document.getElementById('aiAgentToken')?.value
    // 判断是否被收藏
    this.isTag = this.section_responses[0]?.item_responses[
      Number(this.question.seq) - 1
    ]?.question_responses[0].isTag;
    if (this.questionFavoritesItem.question_id) {
      this.isCollect = true;
    }
    if (this.mode === "import" && this.question.aiAnalysisEnable) {
      this.$nextTick(() => {
        loadScript(this.showCKEditorData.jqueryPath, err => {
          if (err) {
            console.log(err);
          }
          loadScript(this.showCKEditorData.publicPath, err => {
            if (err) {
              console.log(err);
            }
            this.initBaseAnalysis();
          });
        });
      });
    }
  },
  watch: {
    question: {
      handler(question) {
        if (!this.analysisEditBySelf) {
          this.analysisEditor && this.analysisEditor.setData(question.analysis);
        }
        this.analysisEditBySelf = false;
      },
      deep: true
    }
  },
  methods: {
    async showAgentSdk() {
      $.ajax({
        url: `/api/answer_record/${this.answerRecord.id}/question_text/${this.question.id}`,
        type: 'GET',
        headers:{
          'Accept':'application/vnd.edusoho.v2+json'
        },
        contentType: 'application/json;charset=utf-8',
      }).done((res) => {
        if (!window.parent.agentSdk) return;
        const workflow = {
          workflow: this.mode === 'do' ? "teacher.question.idea" : this.mode === 'report' ? 'teacher.question.analysis' : null,
          inputs: {
            domainId: JSON.parse(window.parent.document.getElementById('chatMetaData')?.value).domainId,
            question: res.question,
          }
        }
        window.parent.agentSdk.chat(res.content, workflow);
      }).fail((err) => {
        console.log(err)
      })
    },
    showTeacherQuestion() {
      if (this.mode === 'report') {
        return true;
      }
      return this.mode === 'do' && !Number(this.answerRecord.limited_time);
    },
    replaceHtmlSpace(htmlStr) {
      return htmlStr ? htmlStr.replace(/ /g, " ") : "";
    },
    initBaseAnalysis() {
      this.analysisEditor = window.CKEDITOR.replace(`analysis${this.seq}`, {
        toolbar: "Minimal",
        fileSingleSizeLimit: this.showCKEditorData.fileSingleSizeLimit,
        filebrowserImageUploadUrl: this.showCKEditorData
          .filebrowserImageUploadUrl,
        filebrowserImageDownloadUrl: this.showCKEditorData
          .filebrowserImageDownloadUrl,
        language: this.showCKEditorData.language
      });
      this.analysisEditor.setData(this.question.analysis);
      this.analysisEditor.on("change", () => {
        this.analysisEditBySelf = true;
        this.$emit("changeAnalysis", this.seq, this.analysisEditor.getData());
      });
    },
    changeTag() {
      this.isTag = !this.isTag;
      this.$emit("changeTag", this.isTag);
    },
    changeCollect() {
      this.isCollect = !this.isCollect;
      let data = {
        question_id: this.question.id,
        target_type: "assessment"
      };

      this.$emit("changeCollect", data, this.isCollect);
    },
    getAttachmentTypeData(type) {
      const result = this.question.attachments.filter(item => {
        return item.module == type;
      });

      return result;
    },
    lookAnalysis() {
      this.canShowAnalysis = !this.canShowAnalysis;
    },
    prepareStudentAiAnalysis(gen) {
      this.$emit('prepareStudentAiAnalysis', gen);
    },
    prepareTeacherAiAnalysis(gen) {
      this.$emit('prepareTeacherAiAnalysis', gen);
    },
    clearAnalysis() {
      if (this.analysisEditor) {
        this.analysisEditor.setData('');
      } else {
        this.question.analysis = '';
      }
    },
    appendAnalysis(analysis) {
      if (this.analysisEditor) {
        this.analysisEditor.insertHtml(analysis);
      } else {
        this.question.analysis += analysis;
      }
    },
  }
};
</script>
