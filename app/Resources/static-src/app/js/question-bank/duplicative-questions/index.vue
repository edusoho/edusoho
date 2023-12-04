<template>
  <div id="duplicate-check">
    <div class="duplicate-head flex items-center">
      <span class="duplicate-back flex items-center" @click="goBack">
        <span class="es-icon es-icon-fanhui mr4"></span>
        {{ 'importer.import_back_btn'|trans }}
      </span>
      <span class="duplicate-divider"></span>
      <span class="duplicate-title flex items-center"
        ><span class="bankName flex items-center"
          >【<span class="msg">{{ categoryName }}</span
          >】</span
        >{{ 'question.bank.check'|trans }}</span
      >
    </div>
    <div class="duplicate-body">
      <div class="duplicate-question">
        <div class="duplicate-question-head">
          {{ 'question.bank.common.repeated'|trans }}:<label class="duplicate-question-count">{{
            questionData.length
          }}</label
          >{{ 'subject.question_unit'|trans }}
          <div
            v-show="isShowGuide"
            class="duplicate-question-item duplicate-question-active"
          >
            <div class="duplicate-question-title">
              {{ questionData[0].displayMaterial | stripTags }}
            </div>
            <span class="duplicate-question-check-count"
              >{{ questionData[0].frequency }}{{ 'question.bank.unit'|trans }}</span
            >
          </div>
        </div>
        <duplicate-question-item
          v-for="(item, index) in questionData"
          :active="index == activeKey"
          :id="index"
          :key="index"
          :count="item.frequency"
          :title="item.displayMaterial"
          @changeOption="changeOption"
        />
      </div>
      <div class="duplicate-content">
        <div class="duplicate-content-title">{{ 'question.bank.topic.comparison'|trans }}</div>
        <div v-if="isHaveRepeat" class="mt16 flex flex-nowrap">
          <duplicate-question-content
            v-if="questionContentList[oneIndex]"
            @changeOption="changeOption"
            @getData="getData"
            @changeQuestion="changeQuestion"
            @startQuestion="startQuestion"
            @changeQuestionContent="changeQuestionContent"
            type="one"
            :activeIndex="oneIndex"
            :activeKey="activeKey"
            :nextIndex="twoIndex"
            :questionContent="questionContentList[oneIndex]"
            :count="questionContentList.length"
            :title="questionData[activeKey].material"
            class="mr16"
          />
          <duplicate-question-content
            v-if="questionContentList[twoIndex]"
            @changeOption="changeOption"
            @getData="getData"
            @changeQuestion="changeQuestion"
            @startQuestion="startQuestion"
            @changeQuestionContent="changeQuestionContent"
            type="two"
            :activeIndex="twoIndex"
            :activeKey="activeKey"
            :nextIndex="oneIndex"
            :questionContent="questionContentList[twoIndex]"
            :count="questionContentList.length"
            :title="questionData[activeKey].material"
          />
        </div>
        <div v-else class="no-data text-center">
          <img
            class="no-data-img"
            src="/static-dist/app/img/question-bank/noduplicative.png"
          />
          <div class="no-data-content">{{ 'question.bank.check.result.noData.title'|trans }}</div>
          <button class="return-btn">{{ 'question.bank.check.result.noData.btn'|trans }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import DuplicateQuestionItem from "./components/DuplicateQuestionItem.vue";
import DuplicateQuestionContent from "./components/DuplicateQuestionContent.vue";
import "store";
import { Repeat } from "common/vue/service";

export default {
  data() {
    return {
      activeKey: 0,
      introOption: {
        prevLabel: Translator.trans("course_set.manage.prev_label"),
        nextLabel: Translator.trans("question.bank.next_label"),
        skipLabel: Translator.trans("question.bank.skip_label"),
        doneLabel: Translator.trans("question.bank.finish_label"),
        showBullets: false,
        showStepNumbers: false,
        exitOnEsc: false,
        exitOnOverlayClick: false,
        tooltipClass: "duplicate-intro",
        steps: [],
      },
      oneIndex: 0,
      twoIndex: 1,
      isHaveRepeat: true,
      isShowGuide: false,
      questionData: [
        {
          material: "",
          frequency: "",
          displayMaterial: "",
        },
      ],
      questionContentList: [
        {
          analysis: "",
          category_name: "",
          type: "",
        },
      ],
    };
  },
  components: {
    DuplicateQuestionItem,
    DuplicateQuestionContent,
  },
  watch: {
    activeKey() {
      this.changeOption(this.activeKey)
    },
  },
  computed: {
    categoryName() {
      if ($("[name=categoryName]").val()) {
        return $("[name=categoryName]").val();
      }

      if ($("[name=categoryId]").val() === "") {
        return Translator.trans("question.bank.all_question");
      }

      return Translator.trans("question.bank.no_category");
    },
  },
  async mounted() {
    await this.getData();
    await this.changeOption();
    if (!store.get("QUESTION_DUPLICATE_INTRO")) {
      this.isShowGuide = true;

      this.$nextTick(() => {
        this.initGuide();
      });
    }

    if($("[name=page_type]").val() && store.get("DUPLICATE_MATERIAL")) {
      this.matchActive(store.get("DUPLICATE_MATERIAL"))
    }
  },
  methods: {
    initGuide() {
      let that = this;
      that.isShowGuide = true;
      that.introOption.steps = [
        {
          element: ".duplicate-question-head",
          intro: Translator.trans("question.bank.check.guide.one"),
          position: "bottom",
        },
        {
          element: ".question-num",
          intro: Translator.trans("question.bank.check.guide.two"),
          position: "bottom",
        },
      ]
      introJs()
        .setOptions(that.introOption)
        .start()
        .onchange(function () {
          that.isShowGuide = false;
          document.querySelectorAll(".introjs-skipbutton")[0].style.display =
            "inline-block";
          document.querySelectorAll(".introjs-prevbutton")[0].style.display =
            "none";
        })
        .oncomplete(function () {
          store.set("QUESTION_DUPLICATE_INTRO", true);
        });
    },
    changeQuestion(type, index) {
      this[`${type}Index`] = index;
    },
    async changeOption(activeKey = 0) {
      const that = this;
      that.activeKey = activeKey;
      let formData = new FormData();
      formData.append("material", that.questionData[activeKey].material);
      await Repeat.getRepeatQuestionInfo(
        $("[name=questionBankId]").val(),
        formData
      ).then(async (res) => {
        // if(res) {
        //     that.$error({
        //         title: Translator.trans("question.bank.error.tip.title"),
        //         content: Translator.trans("question.bank.error.tip.content"),
        //         okText: Translator.trans("site.btn.confirm"),
        //         async onOk() {
        //             await that.getData()
        //             await that.changeOption()
        //         }
        //     });
        // }

        that.questionContentList = res;
        that.questionData[activeKey].frequency = res.length.toString();
      }).catch((err) => {
        that.$message.error(err.message);
      });
    },
    goBack() {
      window.location.href = `/question_bank/${$("[name=questionBankId]").val()}/questions`;
    },
    async getData() {
      await Repeat.getRepeatQuestion($("[name=questionBankId]").val(), {
        categoryId: $("[name=categoryId]").val(),
      }).then((res) => {
        this.questionData = res;
      }).catch((err) => {
        this.$message.error(err.message);
      });
    },
    startQuestion() {
      this.activeKey = 0;
    },
    matchActive(title) {
      for(let i = 0; i < this.questionData.length; i++) {
        if(this.questionData[i].material == title) {
          this.activeKey = i;
          break;
        }
      }
    },
    changeQuestionContent(index, content) {
      this.questionContentList[index] = content;
    }
  },
};
</script>