<template>
  <div>
    <a-form :form="reviewForm">
      <item-engine
        mode="review"
        :answerReport="answerReport"
        :assessment="assessment"
        :metaActivity="metaActivity"
        :form="reviewForm"
        :role="role"
        :isDownload="isDownload"
        :answerScene="answerScene"
        :answerRecord="answerRecord"
        :showCKEditorData="showCKEditorData"
        @previewAttachment="previewAttachment"
        :previewAttachmentCallback="previewAttachmentCallback"
        :downloadAttachmentCallback="downloadAttachmentCallback"
        @downloadAttachment="downloadAttachment"
        :cdnHost="cdnHost"
        :media-type="mediaType"
        :finish-type="finishType"
        :submit-list="submitList"
        @view-historical-result="handleViewHistoricalResult"
      >
        <template #review-footer>
          <div
            class="ibs-item-list"
            v-if="['testpaper', 'homework'].includes(mediaType)"
          >
            <div class="ibs-pl16 ibs-pr16 ibs-comment">
              <a-row>
                <a-col :span="3" class="ibs-comment__title ibs-pl16">
                  {{ t("itemReview.job_comments") }}
                </a-col>
                <a-col :span="21" class="ibs-comment__content">
                  <a-form :form="form">
                    <a-form-item>
                      <a-textarea
                        :placeholder="t('itemReview.InputComment_tip')"
                        :rows="5"
                        v-decorator="[`comment`, { initialValue: '' }]"
                      ></a-textarea>
                    </a-form-item>

                    <a-form-item>
                      <a-select
                        :defaultValue="comments[0]"
                        @change="handleChange"
                      >
                        <a-select-option
                          v-for="(comment, index) in comments"
                          :key="index"
                          :value="comment"
                        >
                          {{ comment }}
                        </a-select-option>
                      </a-select>
                    </a-form-item>
                  </a-form>
                </a-col>
              </a-row>
            </div>
          </div>

          <div
            class="ibs-item-list ibs-footer ibs-clearfix"
            :class="{
              'ibs-footer--right':
                mediaType === 'homework' && finishType === 'submit'
            }"
          >
            <span
              class="ibs-mr24"
              v-if="
                mediaType === 'homework' &&
                  finishType === 'submit' &&
                  role !== 'student'
              "
            >
              {{ t("itemReview.rightQuestionCount") }}
              {{ answerReport.right_question_count }}
            </span>
            <template v-else-if="role !== 'student'">
              <div class="ibs-mr24 ibs-left">
                {{ t("itemReview.subjective_question") }}
                {{ getSubjectiveScore }}
                {{ t("itemEngine.score") }}
              </div>
              <div class="ibs-mr24 ibs-left">
                {{ t("itemReview.objective_question") }}
                {{ getObjectiveScore }}
                {{ t("itemEngine.score") }}
              </div>
              <div class="ibs-mr24 ibs-left">
                {{ t("itemReview.Student_score") }}：
                {{ getCommentScore }}
              </div>
              <div class="ibs-mr24 ibs-left">
                {{ t("itemReview.Pass_score") }}{{ answerScene.pass_score }}
              </div>
              <div class="ibs-mr24 ibs-left">
                {{ t("itemReview.result") }}
                <span style="font-weight: 500; color: green;" v-if="isPassed">
                  {{ t("itemReview.pass") }}
                </span>
                <span style="font-weight: 500; color: red;" v-else>
                  {{ t("itemReview.fail") }}
                </span>
              </div>
            </template>

            <a-button class="ibs-mr24" @click="handleCancel">
              {{ t("itemReview.Cancel") }}
            </a-button>
            <a-button
              type="primary"
              class="ibs-mr24"
              @click="showConfirm('', $event)"
            >
              {{ t("itemReview.FinishReview") }}
            </a-button>
            <a-button @click="showConfirm('again', $event)">
              {{ t("itemReview.FinishThenGo") }}
            </a-button>
          </div>
        </template>
      </item-engine>
    </a-form>
  </div>
</template>

<script>
import Locale from 'common/vue/mixins/locale';
import itemEngine from './item-engine';

export default {
  name: "item-review",
  components: {
    itemEngine
  },
  mixins: [Locale],
  data() {
    return {
      formLayout: "horizontal",
      reviewForm: this.$form.createForm(this, {
        name: "essay-comment-form",
        onValuesChange: this.onValuesChange
      }),
      commentScore: 0,
      objectiveScore: 0, // 客观题得分
      subjectiveScore: 0, // 主观题得分
      form: this.$form.createForm(this, { name: "comments-form" }),
      visible: false,
      comments: [
        this.t("itemReview.comments_0"),
        this.t("itemReview.comments_1"),
        this.t("itemReview.comments_2"),
        this.t("itemReview.comments_3"),
        this.t("itemReview.comments_4"),
        this.t("itemReview.comments_5")
      ],
      comment: "",
      grades: ["excellent", "good", "passed", "unpassed"],
      gradeObj: {
        excellent: this.t("itemReview.rank_0"),
        good: this.t("itemReview.rank_1"),
        passed: this.t("itemReview.rank_2"),
        unpassed: this.t("itemReview.rank_3")
      },
      commentData: {},
      data: {}
    };
  },
  computed: {
    gradeDefault() {
      return Number(this.answerScene.need_score) ? "" : "passed";
    },

    getObjectiveScore() {
      const objective_score = this.answerReport.objective_score || 0;
      return this.objectiveScore + Number(objective_score);
    },

    getSubjectiveScore() {
      const subjective_score = this.answerReport.subjective_score || 0;
      return (this.subjectiveScore + Number(subjective_score)).toFixed(2);
    },

    getCommentScore() {
      const score = this.answerReport.score || 0;
      return (this.commentScore + Number(score)).toFixed(2);
    },

    isPassed() {
      return this.getCommentScore >= this.answerScene.pass_score;
    }
  },
  props: {
    activity: {
      type: Object,
      default() {
        return {};
      }
    },

    metaActivity: {
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
    showCKEditorData: {
      type: Object,
      default() {
        return {};
      }
    },
    assessment: {
      type: Object,
      default() {
        return {};
      }
    },
    answerScene: {
      type: Object,
      default() {
        return {};
      }
    },
    answerRecord: {
      type: Object,
      default() {
        return {};
      }
    },
    cdnHost: {
      type: String,
      default: "service-cdn.qiqiuyun.net"
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
    //角色 默认teacher:教师 student:学生
    role: {
      type: String,
      default: ""
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
    }
  },
  created() {
    this.$on("handleSubmit", this.handleSubmit);
  },

  mounted() {
    this.mediaType === "testpaper" && this.initComment();
  },

  methods: {
    handleSubmit() {
      this.reviewForm.validateFields((err, values) => {
        if (!err) {
          let idList = [];
          for (let i in values) {
            if (i.indexOf("score") > -1) {
              idList.push(i.slice(5));
            } else if (i.indexOf("status") > -1) {
              idList.push(i.slice(6));
            }
          }
          let reviewList = [];
          if (this.commentScore) {
            this.commentScore = 0;
          }
          for (let i in idList) {
            let tempObj = {};
            tempObj.id = idList[i];
            tempObj.score = Number(values[`score${idList[i]}`]);
            tempObj.status = values[`status${idList[i]}`];
            this.commentScore += Number(values[`score${idList[i]}`]);
            tempObj.comment = values[`comment${idList[i]}`];
            reviewList.push(tempObj);
          }
          this.data.report_id = this.answerReport.id;
          this.data.question_reports = reviewList;
          this.handleValidateSuccess();
        } else {
          this.$message.error(this.t("itemEngine.reviewErrorTips"));
        }
        console.log(err);
      });
    },

    onValuesChange(props, value) {
      let tempKey = "";
      for (let i in value) {
        if (i.indexOf("score") > -1) {
          tempKey = i;
        } else {
          return;
        }
      }

      this.reviewForm.validateFields((err, values) => {
        values[tempKey] = value[tempKey];

        let idList = [];
        for (let i in values) {
          if (i.indexOf("score") > -1) {
            idList.push(i.slice(5));
          }
        }
        if (this.commentScore) {
          this.commentScore = 0;
        }

        if (this.subjectiveScore) {
          this.subjectiveScore = 0;
        }

        if (this.objectiveScore) {
          this.objectiveScore = 0;
        }

        for (let i in idList) {
          const id = idList[i];
          const score = Number(values[`score${id}`]);
          const mode = values[`mode${id}`];
          if (mode === "rich_text") {
            this.subjectiveScore += score;
          } else {
            this.objectiveScore += score;
          }
          this.commentScore += score;
        }

        this.mediaType === "testpaper" && this.initComment();
      });
    },

    initComment() {
      const { customComments } = this.activity;
      const totalScore = this.getCommentScore;

      for (let i = 0; i < customComments.length; i++) {
        const customComment = customComments[i];
        const { start, end, comment } = customComment;

        if (totalScore >= start && totalScore <= end) {
          this.form.setFieldsValue({ [`comment`]: comment });
          break;
        }
      }
    },

    handleValidateSuccess() {
      if (this.role === "student") {
        this.$emit("getReviewData", this.data);
        return;
      }
      this.visible = true;
    },
    handleCancel() {
      // this.visible = false;
      // window.history.back();
      this.$emit("cancel");
    },
    handleChange(value) {
      this.comment = value;
      this.form.setFieldsValue({ [`comment`]: this.comment });
    },
    showConfirm(value, e) {
      const that = this;
      this.$confirm({
        title: that.t("itemReview.ConfirmationPrompt"),
        okText: that.t("itemReview.Confirm"),
        cancelText: that.t("itemReview.Cancel"),
        onOk() {
          value === "again"
            ? that.handleFormSubmitAgain(e)
            : that.handleFormSubmit(e);
          that.forceRemoveModalDom();
        },
        onCancel() {
          that.forceRemoveModalDom();
        }
      });
    },
    forceRemoveModalDom() {
      const modal = document.querySelector(".ant-modal-root");

      if (modal) {
        modal.remove();
      }

      document.body.style = "";
    },
    handleFormSubmit(e) {
      this.getFormData(e);
      this.$emit("getReviewData", this.data);
    },
    handleFormSubmitAgain(e) {
      this.getFormData(e);
      this.$emit("getReviewDataAagin", this.data);
    },
    getFormData(e) {
      this.handleSubmit();
      e.preventDefault();
      this.form.validateFields((err, values) => {
        this.commentData = values;
      });
      this.visible = false;
      this.data = Object.assign(this.data, this.commentData);
    },
    getContainer() {
      return document.getElementById("item-bank-sdk-message");
    },
    getPopupContainer() {
      return document.getElementById("ibs-review-modal");
    },
    previewAttachment(fileId) {
      this.$emit("previewAttachment", fileId);
    },
    downloadAttachment(fileId) {
      this.$emit("downloadAttachment", fileId);
    },
    handleViewHistoricalResult(params) {
      this.$emit("view-historical-result", params);
    }
  }
};
</script>
