<template>
  <div class="ibs-result">
    <div class="ibs-mb16 ibs-clearfix">
      <div :class="`ibs-left ibs-result-header ${extralClass}`">
        <div class="ibs-result-header__title ibs-text-overflow">
          <a-tooltip placement="topLeft">
            <template slot="title">
              {{ assessment.name }}
            </template>
            {{ assessment.name }}
          </a-tooltip>
          <div v-if="needMarking">
            <span
              class="ibs-result-header__tag"
              v-if="mode === 'review' || answerRecord.status == 'reviewing'"
              >{{ t("itemEngine.answerStatus.reviewing") }}</span
            >
            <span
              class="ibs-result-header__tag ibs-result-header__tag--success"
              v-else
              >{{ t("itemEngine.answerStatus.finished") }}</span
            >
          </div>
        </div>
        <div class="ibs-result-header__info">
          {{ t("itemEngine.answerUser") }}{{ username }}
          <span
            class="ibs-ml24"
            v-if="
              metaActivity.mediaType === 'testpaper' &&
                !metaActivity.isOnlyStudent
            "
          >
            {{ t("itemEngine.testpaperMode") }}
            {{
              answerRecord.exam_mode === "1"
                ? t("testpaper.mode1")
                : t("testpaper.mode0")
            }}
          </span>
          <span
            class="ibs-ml24"
            v-if="
              metaActivity.mediaType === 'testpaper' &&
                answerScene.limited_time > 0
            "
          >
            {{ t("testpaper.timeTips") }}
            {{ answerScene.limited_time }}
            {{ t("testpaper.minutesUnit") }}
          </span>
          <span class="ibs-ml24"
            >{{ t("itemEngine.submitTime")
            }}{{ answerRecord.end_time | date }}</span
          >
          <span class="ibs-ml24"
            >{{ t("itemEngine.usedTime")
            }}{{ getTime(Number(answerRecord.used_time)) }}
          </span>
          <template v-if="mediaType === 'homework'">
            <span class="ibs-ml24">
              {{ t("itemEngine.numbeOfOperations") }}
              {{ t("itemEngine.theFirst") }}
              {{ submitList.length + 1 }}
              {{ t("itemEngine.order") }}
            </span>
            <a-dropdown class="ibs-ml24" v-if="submitList.length">
              <a-button type="link">{{ t("itemEngine.jobHistory") }}</a-button>
              <a-menu slot="overlay" @click="handleMenuClick">
                <a-menu-item v-for="(item, index) in submitList" :key="index">
                  {{ t("itemEngine.theFirst") }}
                  {{ index + 1 }}
                  {{ t("itemEngine.order") }}
                  <template v-if="finishType === 'submit'">
                    {{ t("itemEngine.right") }}
                    {{ item.right_question_count }}
                    {{ t("itemEngine.question") }}
                  </template>
                  <template v-else>
                    {{ item.score }} {{ t("Minute") }}
                    <span v-if="item.grade === 'passed'" style="color: green;">
                      {{ t("itemReview.pass") }}
                    </span>
                    <span v-else style="color: red;">
                      {{ t("itemReview.fail") }}
                    </span>
                  </template>
                </a-menu-item>
              </a-menu>
            </a-dropdown>
          </template>
        </div>
      </div>
      <div class="ibs-right" v-show="needScore">
        <div class="ibs-result-header__score">
          <p class="ibs-mb4" v-if="answerRecord.status == 'reviewing'">?</p>
          <p class="ibs-mb4" v-else>{{ answerReport.score }}</p>
          <p class="ibs-mb4">
            {{ t("itemEngine.totalScore")
            }}<span>{{ answerReport.total_score }}</span>
          </p>
        </div>
      </div>
    </div>
    <a-table
      :columns="columns"
      :dataSource="answerReport.section_reports"
      :pagination="false"
    >
      <template slot="section_name" slot-scope="text, record">
        <span>{{ t(text) }}</span>
      </template>
      <template slot="score" slot-scope="text, record" v-show="needScore">
        <span
          class="ibs-warning-color ibs-text-folder"
          v-if="Number(record.reviewing_question_num)"
        >
          ？</span
        >
        <span v-else class="ibs-warning-color ibs-text-folder">{{ text }}</span>
      </template>

      <template slot="question_count" slot-scope="text, record">
        <span v-if="record.section_name === t('material')"
          >{{ text }}{{ t("subCountNumber") }}</span
        >
        <span v-else>{{ text }}{{ t("countNumber") }}</span>
      </template>

      <template slot="right_question_num" slot-scope="text, record">
        <span
          class="ibs-success-color"
          v-if="Number(record.reviewing_question_num)"
        >
          ？</span
        >
        <span v-else class="ibs-success-color"
          >{{ text }}{{ t("countNumber") }}</span
        >
      </template>

      <template slot="wrong_question_num" slot-scope="text, record">
        <span
          class="ibs-danger-color"
          v-if="Number(record.reviewing_question_num)"
        >
          ？</span
        >
        <span v-else class="ibs-danger-color"
          >{{ text + Number(record.part_right_question_num)
          }}{{ t("countNumber") }}</span
        >
      </template>

      <template slot="no_answer_question_num" slot-scope="text, record">
        <span v-if="Number(record.reviewing_question_num)"> ？</span>
        <span v-else>{{ text }}{{ t("countNumber") }}</span>
      </template>
    </a-table>

    <div class="ibs-result-tip" v-show="mode === 'review'">
      <i class="ib-icon ib-icon-info ibs-mr8"></i>{{ reviewText }}
    </div>
    <div
      class="ibs-result-tip"
      v-if="mode === 'report' && answerRecord.status === 'reviewing'"
    >
      <i class="ib-icon ib-icon-info ibs-mr8"></i
      >{{ t("itemEngine.waitingReview_tip") }}
    </div>
  </div>
</template>

<script>
import Locale from "common/vue/mixins/locale";
import { sec2Time, timeStampFormatTime } from "common/date-toolkit";

export default {
  name: "result",
  mixins: [Locale],
  data() {
    return {
      scoreColumns: [
        {
          title: this.t("itemEngine.itemType"),
          dataIndex: "section_name",
          key: "section_name",
          scopedSlots: { customRender: "section_name" },
          align: "center"
        },
        {
          title: this.t("itemEngine.itemScore"),
          dataIndex: "score",
          key: "score",
          scopedSlots: { customRender: "score" },
          align: "center"
        },
        {
          title: this.t("itemEngine.itemTotal"),
          dataIndex: "question_count",
          key: "question_count",
          scopedSlots: { customRender: "question_count" },
          align: "center"
        },
        {
          title: this.t("itemEngine.itemRight"),
          dataIndex: "right_question_num",
          key: "right_question_num",
          scopedSlots: { customRender: "right_question_num" },
          align: "center"
        },
        {
          title: this.t("itemEngine.itemWrong"),
          dataIndex: "wrong_question_num",
          key: "wrong_question_num",
          scopedSlots: { customRender: "wrong_question_num" },
          align: "center"
        },
        {
          title: this.t("itemEngine.itemUndo"),
          dataIndex: "no_answer_question_num",
          key: "no_answer_question_num",
          scopedSlots: { customRender: "no_answer_question_num" },
          align: "center"
        }
      ],
      noScoreColumns: [
        {
          title: this.t("itemEngine.itemType"),
          dataIndex: "section_name",
          key: "section_name",
          align: "center"
        },
        {
          title: this.t("itemEngine.itemTotal"),
          dataIndex: "question_count",
          key: "question_count",
          scopedSlots: { customRender: "question_count" },
          align: "center"
        },
        {
          title: this.t("itemEngine.itemRight"),
          dataIndex: "right_question_num",
          key: "right_question_num",
          scopedSlots: { customRender: "right_question_num" },
          align: "center"
        },
        {
          title: this.t("itemEngine.itemWrong"),
          dataIndex: "wrong_question_num",
          key: "wrong_question_num",
          scopedSlots: { customRender: "wrong_question_num" },
          align: "center"
        },
        {
          title: this.t("itemEngine.itemUndo"),
          dataIndex: "no_answer_question_num",
          key: "no_answer_question_num",
          scopedSlots: { customRender: "no_answer_question_num" },
          align: "center"
        }
      ]
    };
  },
  computed: {
    extralClass: function() {
      return this.needScore ? "" : "ibs-result-header--width";
    },
    columns: function() {
      return this.needScore ? this.scoreColumns : this.noScoreColumns;
    },
    username: function() {
      return this.answerRecord.username || "-";
    },
    reviewText() {
      if (this.role === "student") {
        return this.t("itemEngine.student_FinishReview_tip");
      }
      return this.t("itemEngine.FinishReview_tip");
    }
  },
  props: {
    mode: {
      type: String,
      default: "report"
    },
    answerReport: {
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
    needScore: {
      type: Number,
      default() {
        return 1;
      }
    },
    needMarking: {
      type: Number,
      default() {
        return 1;
      }
    },
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

    metaActivity: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  filters: {
    date: function(timeStamp) {
      return timeStampFormatTime(timeStamp);
    }
  },
  methods: {
    getTime: function(second) {
      const timeLocal = {
        hour: this.t("Hour"),
        minute: this.t("Minute"),
        second: this.t("Second")
      };
      return sec2Time(second, timeLocal);
    },

    handleMenuClick(e) {
      this.$emit("view-historical-result", this.submitList[e.key]);
    }
  }
};
</script>
