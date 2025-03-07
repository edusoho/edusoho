<template>
  <answer-model
    :question="question"
    :mode="mode"
    :needScore="needScore"
    v-bind="$attrs"
    v-on="$listeners"
    :assessmentStatus="assessmentStatus"
    :questionFavoritesItem="questionFavoritesItem"
    :section_responses="section_responses"
    :keys="keys"
    @changeTag="changeTag"
    @prepareTeacherAiAnalysis="prepareTeacherAiAnalysis"
  >
    <template v-slot:response_points>
      <div class="ibs-mb16 ibs-mt16" v-if="mode === 'do'">
        <a-textarea
          :rows="0"
          :id="`essay-do-answer${question.id}`"
          :data-image-download-url="
            showCKEditorData.filebrowserImageDownloadUrl
          "
        />
        <div
          class="ibs-mt16"
          v-if="showAttachment && assessmentStatus !== 'preview'"
        >
          <attachment-upload
            :mode="uploadMode"
            module="answer"
            :uploaderData="uploadSDKInitData"
            :cdnHost="cdnHost"
            :fileShowData="getAttachmentTypeData('answer')"
            @getFileInfo="getFileInfo"
            @deleteFile="deleteFile"
          ></attachment-upload>
        </div>
      </div>

      <div
        v-if="mode === 'preview' || mode === 'import'"
        class="ibs-answer-part"
      >
        <!-- word导入解析错误 -->
        <div
          class="ibs-danger-color"
          v-if="question.errors && question.errors.answer"
        >
          {{ t("itemEngine.answerErrorMessage") }}
        </div>

        <template v-else>
          <span class="ibs-label">{{ t("itemEngine.standard_answer") }}</span>
          <div
            class="ibs-content ibs-editor-text"
            v-html="question.answer[0]"
          ></div>
        </template>
      </div>

      <div v-if="mode == 'analysis'">
        <span class="ibs-label">{{ t("itemEngine.standard_answer") }}</span>
        <div
          class="ibs-content ibs-editor-text"
          v-html="question.answer[0]"
        ></div>
        <div class="ibs-content" v-if="needScore">
          <div class="ibs-position-relative">
            {{ t("Get") }} {{ question.score }} {{ t("itemEngine.score")
            }}<span class="ibs-analysis-status"
              >{{ Number(analysisData.right_num)
              }}{{ t("itemEngine.manCount") }}</span
            >
          </div>
          <div class="ibs-position-relative">
            {{ t("Get") }} 0 - {{ question.score }} {{ t("itemEngine.score")
            }}<span class="ibs-analysis-status"
              >{{ Number(analysisData.part_right_num)
              }}{{ t("itemEngine.manCount") }}</span
            >
          </div>
          <div class="ibs-position-relative">
            {{ t("Get") }} 0 {{ t("itemEngine.score")
            }}<span class="ibs-analysis-status"
              >{{
                Number(analysisData.wrong_num) +
                  Number(analysisData.no_answer_num)
              }}{{ t("itemEngine.manCount") }}</span
            >
          </div>
        </div>
      </div>

      <div
        v-if="mode === 'report' || mode === 'review'"
        class="ibs-answer-part"
      >
        <span class="ibs-label">{{ t("itemEngine.answerResult") }}</span>
        <div class="ibs-content">
          <div class="ibs-mb4">
            <span class="ibs-label">{{ t("itemEngine.standard_answer") }}</span>
            <div
              class="ibs-content ibs-editor-text"
              v-html="question.answer[0]"
            ></div>
          </div>
          <div class="ibs-mb4">
            <span class="ibs-label"> {{ answerText }}</span>
            <div
              v-if="reportAnswer.response[0] !== '' && reportAnswer.response[0]"
              class="ibs-content ibs-editor-text"
              v-html="reportAnswer.response[0]"
            ></div>
            <div v-else class="ibs-content">
              {{ t("itemEngine.answerStatus.no_answer") }}
            </div>
            <div
              v-if="getAttachmentTypeData('answer').length > 0"
              class="ibs-mt8"
              style="padding: 8px 8px 0;background-color: #f5f5f5;border: 1px solid #F2F3F5;border-radius: 6px;"
            >
              <attachment-preview
                v-for="fileData in getAttachmentTypeData('answer')"
                :key="fileData.id"
                :cdnHost="cdnHost"
                :fileData="fileData"
              ></attachment-preview>
            </div>
          </div>
          <template v-if="mode == 'report'">
            <div v-if="needMarking">
              <div v-if="answerRecord.status !== 'reviewing'">
                <div class="ibs-mb4" v-show="needScore">
                  <span class="ibs-label">{{ t("itemEngine.getScore") }}</span>
                  <div class="ibs-content ibs-success-color">
                    {{ reportAnswer.score }}{{ t("itemEngine.score") }}
                  </div>
                </div>

                <div class="ibs-mb4">
                  <span class="ibs-label">{{ t("itemEngine.comment") }}</span>
                  <div
                    class="ibs-content ibs-editor-text"
                    v-html="
                      `${reportAnswer.comment || t('itemReport.no_comment')}`
                    "
                  ></div>
                </div>
              </div>
              <!-- 正在批阅 -->
              <div class="ibs-mark-status" v-else>
                <div>{{ t("itemEngine.teacher_comment") }}</div>
              </div>
            </div>
            <div v-else class="ibs-mark-status">
              {{ t("itemEngine.noNeed_comment") }}
            </div>
          </template>
        </div>
        <!-- 教师批阅组件 -->
        <slot name="review" v-if="mode == 'review'"></slot>
      </div>
    </template>
    <template v-slot:analysis_response_points>
      <div>
        <span class="ibs-label">{{ t("itemEngine.standard_answer") }}</span>
        <div
          class="ibs-content ibs-editor-text"
          v-html="question.answer[0]"
        ></div>
      </div>
    </template>
  </answer-model>
</template>

<script>
// 引入ckeditor
import loadScript from "load-script";
import answerModel from "./answer-model";
import attachmentUpload from "../attachment-upload";
import attachmentPreview from "../attachment-preview";
import { debounce } from "common/debounce";
import Locale from "common/vue/mixins/locale";

export default {
  name: "essay-type",
  inheritAttrs: false,
  mixins: [Locale],
  data() {
    return {
      formLayout: "horizontal",
      form: this.$form.createForm(this, { name: "essay-comment-form" }),
      answer: this.userAnwer[0]
    };
  },
  computed: {
    uploadMode: function() {
      return this.getAttachmentTypeData("answer") ? "edit" : "create";
    },
    analysisData() {
      const defaultData = {
        right_num: "0",
        wrong_num: "0",
        no_answer_num: "0",
        part_right_num: "0"
      };
      return Object.assign(defaultData, this.analysisQuestionInfo);
    },
    answerText() {
      return this.role === "student"
        ? this.t("itemEngine.memberAnswer")
        : this.t("itemEngine.studentAnswer");
    }
  },
  components: { answerModel, attachmentUpload, attachmentPreview },
  props: {
    mode: {
      type: String,
      default: "do"
    },
    keys: {
      type: Array,
      default() {
        return [];
      }
    },
    question: {
      type: Object,
      default() {
        return {};
      }
    },
    userAnwer: {
      type: Array,
      default() {
        return [""];
      }
    },
    reportAnswer: {
      type: Object,
      default() {
        return {};
      }
    },
    userAttachments: {
      type: Array,
      default() {
        return [];
      }
    },
    scene: {
      type: String,
      default() {
        return "exam";
      }
    },
    answerRecord: {
      type: Object,
      default() {
        return {};
      }
    },
    needScore: {
      type: Number,
      default: 1
    },
    needMarking: {
      type: Number,
      default: 1
    },
    uploadSDKInitData: {
      type: Object,
      default() {
        return {};
      }
    },
    questionFavoritesItem: {
      type: Object,
      default() {
        return {};
      }
    },
    assessmentStatus: {
      type: String,
      default: ""
    },
    analysisQuestionInfo: {
      type: Object,
      default() {
        return {};
      }
    },
    role: {
      type: String,
      default: ""
    },
    section_responses: {
      type: Array,
      default() {
        return [];
      }
    },
    item: {
      type: Object,
      default() {
        return {};
      }
    },
  },
  inject: ["showCKEditorData", "showAttachment", "cdnHost"],
  mounted() {
    this.$nextTick(() => {
      loadScript(this.showCKEditorData.jqueryPath, err => {
        if (err) {
          console.log(err);
        }
        loadScript(this.showCKEditorData.publicPath, err => {
          if (err) {
            console.log(err);
          }
          // 答题时的答案输入组件
          if (this.mode === "do") {
            this.initEssayAnswer();
          }
        });
      });
    });
  },
  methods: {
    initEssayAnswer() {
      this.answerEditor = window.CKEDITOR.replace(
        `essay-do-answer${this.question.id}`,
        {
          toolbar: "Detail",
          fileSingleSizeLimit: this.showCKEditorData.fileSingleSizeLimit,
          filebrowserImageUploadUrl: this.showCKEditorData
            .filebrowserImageUploadUrl,
          filebrowserImageDownloadUrl: this.showCKEditorData
            .filebrowserImageDownloadUrl,
          language: this.showCKEditorData.language
        }
      );
      this.answerEditor.setData(this.answer);
      this.answerEditor.on("change", () => {
        const data = this.answerEditor.getData();
        const that = this;
        debounce(
          function() {
            that.changeAnswer(data);
          },
          500,
          true
        )();
      });
      this.answerEditor.on("blur", () => {
        const data = this.answerEditor.getData();
        const that = this;
        debounce(
          function() {
            that.changeAnswer(data);
          },
          500,
          true
        )();
      });
    },
    changeAnswer(data) {
      this.$emit("changeAnswer", data, this.keys);
    },
    changeTag(data) {
      this.$emit("changeTag", data, this.keys);
    },
    getFileInfo(file) {
      this.$emit("getEssayAttachment", file, this.keys);
    },
    deleteFile(fileId) {
      this.$emit("deleteEssayAttachment", fileId, this.keys);
    },
    getAttachmentTypeData(type) {
      const data =
        this.mode === "do"
          ? this.userAttachments
          : this.reportAnswer.attachments;

      const result = data.filter(item => {
        return item.module == type;
      });

      return result;
    },
    prepareTeacherAiAnalysis(gen) {
      const data = {};
      let question = JSON.parse(JSON.stringify(this.question));
      data.stem = question.stem;
      data.answer = [].concat(question.answer).join();
      if (this.item.type === "material") {
        data.type = "material-essay";
        data.material = this.item.material;
      } else {
        data.type = "essay";
      }
      gen(data);
    }
  }
};
</script>
