<template>
  <div :class="cardClass" id="item-bank-sdk-card">
    <div class="ibs-card-head ibs-clearfix ibs-text-left">
      {{ t("itemEngine.card") }}
      <span
        class="ibs-card-head__toggle"
        v-show="mode == 'do' && assessmentStatus !== 'preview'"
        @click="toggleCard"
        >{{ t("itemEngine.toggleCard") }}</span
      >
    </div>

    <div
      class="ibs-card-body ibs-card-body--scroll ibs-text-left"
      v-show="isShow"
    >
      <div
        v-for="(section, sectionsIndex) in sections"
        :key="'cardSection' + sectionsIndex"
      >
        <p class="ibs-card-body__type" v-show="getReviewTitle(sectionsIndex)">
          {{ section.name }}
        </p>
        <a-anchor :affix="false" class="ibs-card-anchor" @click="handleClick">
          <template v-for="(item, itemIndex) in section.items">
            <a-anchor-link
              v-for="(question, questionIndex) in item.questions"
              :key="`cardQuestion${question.id}`"
              :href="`#ibs-${question.seq}`"
              v-show="getModeType(sectionsIndex, itemIndex, questionIndex)"
              :title="question.seq"
              :class="statusClass(sectionsIndex, itemIndex, questionIndex)"
            />
          </template>
        </a-anchor>
      </div>
      <div class="ibs-card-body__explain" v-show="mode == 'report'">
        <span class="ibs-success-bg"></span>
        <small>{{ t("Right") }}</small>
        <span class="ibs-danger-bg"></span>
        <small>{{ t("Wrong") }}</small>
        <span class="ibs-warning-bg"></span>
        <small>{{ t("itemEngine.remainReview") }}</small>
        <span class="ibs-tip-bg"></span>
        <small>{{ t("itemEngine.undo") }}</small>
      </div>
    </div>
    <div class="ibs-card-footer">
      <div class="ibs-text-left" v-if="mode == 'report'">
        <a-checkbox
          @change="onChange"
          v-if="!setting"
          class="ibs-dark-major text-left"
          >{{ t("itemEngine.showError") }}</a-checkbox
        >

        <!-- 答题逻辑 -->
        <div
          v-if="
            answerRecord.status == 'finished' &&
              assessmentStatus !== 'finished' &&
              showDoAgainBtn
          "
          class="ibs-text-center ibs-mt16"
        >
          <a-button
            type="primary"
            shape="round"
            v-if="canDoAgain === '1'"
            @click="doAgain"
            >{{ t("itemEngine.doAagin") }}</a-button
          >
          <a-button disabled shape="round" v-else v-show="!setting">{{
            t("itemEngine.doCountUsed")
          }}</a-button>
        </div>
      </div>
      <slot name="returnBtn"></slot>

      <div class="ibs-text-center" v-if="mode == 'do'">
        <a-button
          v-if="showSaveProgressBtn"
          type="primary"
          shape="round"
          class="ibs-mr8"
          :disabled="disabled"
          @click="saveAnswer"
          >{{ t("itemEngine.saveProgress") }}</a-button
        >
        <a-button
          type="primary"
          shape="round"
          :disabled="disabled"
          @click="submitAnswer"
        >
          {{
            t(
              metaActivity.mediaType === "testpaper"
                ? "testpaper.submit"
                : "itemEngine.submit"
            )
          }}
        </a-button>
      </div>
    </div>
  </div>
</template>

<script>
import Emitter from "common/vue/mixins/emitter";
import Locale from "common/vue/mixins/locale";

export default {
  name: "card",
  mixins: [Emitter, Locale],
  data: function() {
    return {
      isShow: this.showCard(),
      cardTop: 0,
      affix: false
    };
  },
  props: {
    mode: {
      type: String,
      default: "report"
    },
    courseSetStatus: {
      type: String,
      default: "1"
    },
    sections: {
      type: Array,
      default: () => []
    },
    answerRecord: {
      type: Object,
      default: () => {}
    },
    exercise: {
      type: Object,
      default: () => {}
    },
    answerScene: {
      type: Object,
      default: () => {}
    },
    section_responses: {
      type: Array,
      default: () => []
    },
    section_reports: {
      type: Array,
      default: () => []
    },
    assessmentStatus: {
      type: String,
      default: ""
    },
    answerShow: {
      type: String,
      // 默认显示, 不显示值为 none
      default: "show"
    },
    doTimes: {
      type: Number,
      default: 0
    },
    cardIsShow: {
      type: Number,
      default: 0
    },
    showSaveProgressBtn: {
      type: Number,
      default: 1
    },
    showDoAgainBtn: {
      type: Number,
      default: 1
    },
    metaActivity: {
      type: Object,
      default() {
        return {};
      }
    },
    canDoAgain: {
      type: String,
      default: ""
    },
    assessmentResponses: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  watch: {
    section_responses: {
      handler: function() {},
      deep: true
    }
  },
  computed: {
    disabled() {
      return this.assessmentStatus == "preview";
    },
    cardClass() {
      let className = "ibs-card";
      if (this.isShow) {
        className += " ibs-card-inspection-show";
      }
      return className;
    },
    setting() {
      return this.answerShow === "none" ? true : false;
    }
  },
  mounted() {},
  methods: {
    onChange(e) {
      console.log(`checked = ${e.target.checked}`);
      this.$emit("showError", e.target.checked);
    },
    toggleCard() {
      this.isShow = !this.isShow;
    },
    getContainer() {
      return document.getElementById("item-bank-sdk-card");
    },
    saveAnswer() {
      this.$emit("saveAnswerData");
      // 考试：取消按钮是离开考试，确定按钮是及选题
      const self = this;
      let content = "";

      if (this.metaActivity.mediaType === "testpaper") {
        if (this.answerRecord.exam_mode == "0") {
          content = this.t("testpaper.exitTips");
        }

        if (this.answerRecord.exam_mode == "1") {
          content = this.t("testpaper.exitTips1");
        }
      }

      this.$confirm({
        title: this.t("testpaper.saveTips"),
        icon: () => <a-icon type="check-circle" style="font-size: 22px;" />,
        content,
        okText: this.t("itemEngine.goThenDo"),
        cancelText:
          this.metaActivity.mediaType === "testpaper"
            ? this.t("testpaper.exit")
            : this.t("itemEngine.exit"),
        class: "ibs-card-confirm-modal",
        // getContainer: this.getContainer,
        onOk() {
          self.forceRemoveModalDom();
        },
        onCancel() {
          self.$emit("exitAnswer");
          self.forceRemoveModalDom();
        }
      });
    },
    submitAnswer() {
      const self = this;

      this.$confirm({
        title: this.t("itemEngine.confirmSubmit_title"),
        okText: this.t("itemEngine.confirm"),
        cancelText: this.t("itemEngine.goThenDo"),
        class: "ibs-card-confirm-modal",
        // getContainer: this.getContainer,
        onOk() {
          self.$emit("answerData");
          self.forceRemoveModalDom();
        },
        onCancel() {
          self.forceRemoveModalDom();
        }
      });
    },
    showConfirm(flag) {
      const text = flag ? "" : this.t("itemEngine.confirmSave_tip");
      const self = this;

      this.$confirm({
        title: flag
          ? this.t("itemEngine.confirmSubmit_title")
          : this.t("itemEngine.confirmSave_title"),
        content: this.answerScene.limited_time > 0 ? text : "",
        okText: flag ? this.t("itemEngine.confirm") : this.t("itemEngine.exit"),
        cancelText: this.t("itemEngine.goThenDo"),
        class: "ibs-card-confirm-modal",
        // getContainer: this.getContainer,
        onOk() {
          if (flag) {
            self.$emit("answerData");
          } else {
            self.$emit("saveAnswerData");
          }
          self.forceRemoveModalDom();
        },
        onCancel() {
          self.forceRemoveModalDom();
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
    getModeType(s, i, q) {
      if (this.mode !== "review") return true;
      if (
        this.section_reports[s].item_reports[i].question_reports[q].status ===
        "reviewing"
      ) {
        return true;
      } else {
        return false;
      }
    },
    statusClass(s, i, q) {
      if (this.mode === "do") {
        if (Number(this.sections[s].items[i].questions[q].isDelete)) {
          return "ibs-card-body__tag";
        }

        const data = this.section_responses[s].item_responses[i]
          .question_responses[q].response;
        let doItem = data.some(item => {
          return item != "";
        });

        const isTag = this.section_responses[s].item_responses[i]
          .question_responses[q].isTag;
        if (doItem) {
          if (isTag) {
            // 答题且收藏
            return "ibs-card-body__tag ibs-card-body__tag--success ibs-card-body__tag--collectWhite";
          } else {
            // 答题未收藏
            return "ibs-card-body__tag ibs-card-body__tag--success";
          }
        } else {
          if (isTag) {
            // 收藏未答题
            return "ibs-card-body__tag ibs-card-body__tag--collect";
          } else {
            // 未收藏未答题
            return "ibs-card-body__tag";
          }
        }
      } else if (this.mode === "review") {
        return "ibs-card-body__tag";
      } else if (this.mode === "anaysis") {
        return "ibs-card-body__tag";
      } else {
        if (Number(this.sections[s].items[i].questions[q].isDelete)) {
          return "ibs-card-body__tag";
        }

        const status = {
          right: "ibs-card-body__tag--success",
          wrong: "ibs-card-body__tag--danger",
          part_right: "ibs-card-body__tag--danger",
          no_answer: "",
          reviewing: "ibs-card-body__tag--warning"
        };
        const data = this.section_reports[s].item_reports[i].question_reports[
          q
        ];

        if (
          this.assessmentResponses.section_responses[s].item_responses[i]
            .question_responses[q].isTag
        ) {
          return `ibs-card-body__tag ${
            status[data.status]
          } ibs-card-body__tag--collect`;
        }

        if (this.setting) {
          return `ibs-card-body__tag ${status[data.status]} ibs-none-pointer`;
        } else {
          return `ibs-card-body__tag ${status[data.status]}`;
        }
      }
    },
    handleClick(e, link) {
      console.log(link);
    },
    doAgain() {
      if (this.courseSetStatus == "0") {
        this.$message.error(this.t("courseClosed.learn"));
        return;
      }

      if (this.exercise?.status == "closed") {
        this.$message.error(this.t("exerciseClosed.learn"));
        return;
      }

      this.dispatch("item-report", "doAgain");
    },
    submitReview() {
      this.dispatch("item-review", "handleSubmit");
    },
    getReviewTitle(s) {
      if (this.mode !== "review") {
        return true;
      }
      const essayData = this.sections[s].items.map(item => {
        const status = item.questions.map(question => {
          return question.answer_mode === "rich_text";
        });
        const result = status.join(" ");
        return result;
      });

      return essayData.join(" ").indexOf(true) > -1 ? true : false;
    },
    showCard() {
      if (
        this.mode == "do" &&
        this.assessmentStatus !== "preview" &&
        this.assessmentStatus !== "finished"
      ) {
        return !this.cardIsShow;
      } else {
        return true;
      }
    }
  }
};
</script>
