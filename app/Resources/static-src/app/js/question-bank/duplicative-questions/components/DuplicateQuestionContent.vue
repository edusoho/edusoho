<template>
  <div class="duplicate-question-content">
    <div class="question-num">
      <button
        v-for="(item, index) in count"
        :key="index"
        class="numbering relative"
        :class="activeIndex == index ? 'numbering-active' : ''"
        :disabled="nextIndex == index"
        @click="changeQuestion(index)"
      >
      <img v-if="activeIndex == index" class="numbering-active-img" src="/static-dist/app/img/duplicate-active.png" />
      {{ item }}
      </button>
    </div>
    <div ref="container" @scroll="onScroll" class="question-content mt8">
      <div class="question-head">
        <div
          class="question-head-item"
          v-for="(item, index) in headInfo"
          :key="index"
        >
          <div class="question-small-title">{{ item.title }}</div>
          <div class="question-small-content">
            {{ item.content }}
          </div>
        </div>
      </div>
      <div class="question-body relative">
        <div class="question-index">{{ activeIndex+1 }}.</div>
        <question-item :info="questionContent" />
      </div>
      <div :class="isSticky ? 'sticky-button': ''"  ref="stickyButton" class="question-foot">
        <a-button @click="onEdit" class="mr10">{{ 'site.btn.edit'|trans }}</a-button>
        <a-button @click="confirm">{{ 'site.delete'|trans }}</a-button>
      </div>
    </div>
  </div>
</template>
<script>
import QuestionItem from "./Item";
import { Repeat } from "common/vue/service";
import "store";

export default {
  props: {
    questionContent: {
      type: Object,
      default: () => {},
    },
    title: {
      type: String,
      default: "",
    },
    count: {
      type: Number,
      default: 0,
    },
    type: {
      type: String,
      default: "",
    },
    activeIndex: {
      type: Number,
      default: 0,
    },
    nextIndex: {
      type: Number,
      default: 0,
    },
    activeKey: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {
      isSticky: false,
      difficultyList: {
        default: Translator.trans("question.bank.difficulty.default"),
        simple: Translator.trans("question.bank.difficulty.simple"),
        normal: Translator.trans("question.bank.difficulty.normal"),
        difficulty: Translator.trans("question.bank.difficulty"),
      },
      typeList: {
        default: Translator.trans("course.question.by.question.type"),
        single_choice: Translator.trans("course.question.type.single_choice"),
        choice: Translator.trans("course.question.type.choice"),
        uncertain_choice: Translator.trans("course.question.type.uncertain_choices"),
        essay: Translator.trans("course.question.type.essay"),
        determine: Translator.trans("course.question.type.determine"),
        fill: Translator.trans("course.question.type.fill"),
        material: Translator.trans("course.question.type.material"),
        },
    };
  },
  computed: {
    headInfo() {
      return [
        {
          title: Translator.trans("category"),
          content: this.questionContent["category_name"] || Translator.trans("question.bank.none"),
        },
        {
          title: Translator.trans("question.bank.type"),
          content: this.typeList[this.questionContent["type"]] || Translator.trans("question.bank.none"),
        },
        {
          title: Translator.trans("question.bank.difficulty.default"),
          content: this.difficultyList[this.questionContent["difficulty"]] || Translator.trans("question.bank.none")
        },
        {
          title: Translator.trans("question.bank.update_time"),
          content: this.$dateFormat(this.questionContent["updated_time"]) || Translator.trans("question.bank.none"),
        },
      ];
    }
  },
  watch: {
    async count() {
      if (this.count > 1) {
        return;
      }

      await this.$emit("startQuestion");
      await this.$emit("getData");
      await this.$emit("changeOption");
    },
    async activeIndex() {
      await Repeat.getQuestionInfo(this.questionContent.id).then((res) => {
        this.$emit("changeQuestionContent", `${this.type}Index`, res);
      })
    }
  },
  components: {
    QuestionItem,
  },
  methods: {
    onScroll(e) {
      const container = this.$refs.container;
      const stickyButton = this.$refs.stickyButton;

      if (!this.isSticky && container.scrollTop > container.clientHeight - stickyButton.offsetHeight - 20) {
        this.isSticky = true;
      } else if (this.isSticky && container.scrollTop <= container.clientHeight - stickyButton.offsetHeight - 20) {
        this.isSticky = false;
      }
    },
    getDifficulty(str) {
      return this.difficultyList(str)
    },
    confirm() {
      const that = this;
      this.$confirm({
        title: Translator.trans("question.bank.delete.tip.title"),
        content: Translator.trans("question.bank.delete.tip.content"),
        icon: "exclamation-circle",
        okText: Translator.trans("site.confirm"),
        cancelText: Translator.trans("site.cancel"),
        async onOk() {
          await Repeat.delQuestion(
            $("[name=questionBankId]").val(),
            that.questionContent.id
          )
            .then(async (res) => {
              if (res) {
                that.$message.success(Translator.trans("site.delete_success_hint"));
                await that.$emit("changeOption", that.activeKey);
              }
            })
            .catch((err) => {
              that.$message.error(err.message);
            });
        },
        onCancel() {},
      });
    },
    onEdit() {
      store.set("DUPLICATE_MATERIAL", this.title);
      window.location.href = `/question_bank/${$(
        "[name=questionBankId]"
      ).val()}/duplicative_question/${this.questionContent.id}/update`;
    },
    changeQuestion(index) {
      this.$emit("changeQuestion", this.type, index);
      
    },
  },
};
</script>