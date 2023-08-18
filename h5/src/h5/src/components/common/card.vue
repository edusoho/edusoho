<template>
  <!-- 答题卡 -->
  <van-popup v-model="cardShow" position="bottom">
    <div class="ibs-card">
      <div class="ibs-card-title">
        <div>
          <span
            v-for="(item, index) in statusItem"
            :key="index"
            :class="[item.className]"
            >{{ item.name }}</span
          >
        </div>
        <i class="wap-icon wap-icon-no" @click="closeCard" />
      </div>
      <div class="ibs-card-list">
        <div class="ibs-card-item">
          <div v-for="(section, s) in sections" :key="'section' + s">
            <template v-if="getSectionShow(section)">
              <div class="ibs-card-item-title">
                {{ section.name }}
              </div>
              <div class="ibs-card-item-list">
                <template v-for="(item, i) in section.items">
                  <template v-for="(question, q) in item.questions">
                    <div
                      v-if="getShow(s, i, q, item, question)"
                      :key="question.id"
                      :class="[
                        'ibs-list-cicle',
                        formatStatus(s, i, q, question)
                      ]"
                      @click="slideToNumber(s, item.seq, q, item.id, question)"
                    >
                      {{ question.seq }}
                    </div>
                  </template>
                </template>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </van-popup>
</template>
<script>
import { do_card, report_card, review_card } from "@/src/utils/cradConfig.js";
export default {
  name: "ibs-card",
  data() {
    return {};
  },
  props: {
    mode: {
      type: String,
      default: "do"
    },
    sections: {
      type: Array,
      default: () => []
    },
    section_responses: {
      type: Array,
      default: () => []
    },
    answer_report: {
      type: Object,
      default: () => {}
    },
    wrongMode: {
      type: Boolean,
      default: false
    },
    question_reports: {
      type: Array,
      default: () => []
    },
    needScore: {
      type: Boolean,
      default: false
    },
    value: Boolean
  },
  model: {
    prop: "value",
    event: "update"
  },
  computed: {
    cardShow: {
      get: function() {
        return this.value;
      },
      set: function(v) {
        this.$emit("update", v);
      }
    },
    statusItem() {
      const mode = this.mode;
      switch (mode) {
        case "do":
          return do_card;
        case "report":
          return report_card;
        case "review":
          return review_card;
        default:
          return "";
      }
    }
  },
  methods: {
    // 答题卡定位
    slideToNumber(s, i, q, itemId, question) {
      const keys = {
        sectionsIndex: s,
        itemIndex: Number(i),
        questionIndex: q,
        itemId
      };
      if (this.wrongMode || this.mode === "review") {
        keys.questionId = question.id;
      }
      this.$emit("slideTo", keys);
      this.closeCard();
    },
    closeCard() {
      // 关闭弹出层
      this.cardShow = false;
    },
    // 答题卡状态判断
    formatStatus(s, i, q) {
      const mode = this.mode;
      switch (mode) {
        case "do":
          return this.getDoStatus(s, i, q);
        case "report":
          return this.getReportStatus(s, i, q);
        case "review":
          return this.getReviewStatus(s, i, q);
      }
    },
    //做题模式下状态
    getDoStatus(s, i, q) {
      const userAnwer = this.section_responses[s].item_responses[i]
        .question_responses[q].response;
      const doItem = userAnwer.some(item => {
        return item != "";
      });
      if (doItem) {
        return "ibs-cicle-active";
      }
      return "";
    },
    //答题报告模式下状态
    getReportStatus(s, i, q) {
      const status = this.answer_report.section_reports[s].item_reports[i]
        .question_reports[q].status;
      switch (status) {
        case "right":
          return "ibs-cicle-right";
        case "reviewing":
          return "ibs-cicle-none";
        case "wrong":
        case "part_right":
          return "ibs-cicle-wrong";
        case "no_answer":
          return "";
      }
    },
    getReviewStatus(s, i, q) {
      const richTextIndex = this.sections[s].items[i].questions[q]
        .richTextIndex;
      const data = this.question_reports[richTextIndex];
      if (!data) {
        return;
      }
      let status;
      if (this.needScore) {
        status = data.score || data.score === 0;
      } else {
        status = data.status;
      }
      return status ? "ibs-cicle-none" : "";
    },
    getShow(s, i, q, item, question) {
      if (this.mode === "report" && this.wrongMode) {
        const status = this.answer_report.section_reports[s].item_reports[i]
          .question_reports[q].status;
        return status !== "right";
      }
      if (this.mode === "review") {
        return question.reviewShow;
      }
      return true;
    },
    getSectionShow(section) {
      if (this.mode === "review") {
        return section.richTextNum && section.richTextNum > 0;
      }
      return true;
    }
  }
};
</script>
