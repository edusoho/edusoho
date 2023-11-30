<template>
  <div class="duplicate-question-content">
    <div class="question-num">
      <button
        v-for="(item, index) in count"
        :key="index"
        class="numbering"
        :class="activeIndex == index ? 'numbering-active' : ''"
        :disabled="nextIndex == index"
        @click="changeQuestion(index)"
      >
        {{ item }}
      </button>
    </div>
    <div class="question-content mt8">
      <div class="question-head">
        <div
          class="question-head-item"
          v-for="(item, index) in headInfo"
          :key="index"
        >
          <div class="question-small-title">{{ item.title }}</div>
          <div class="question-small-content">
            {{ item.content }}
            <!-- {{ questionContent[item.content] ? questionContent[item.content] : '暂无' }} -->
          </div>
        </div>
      </div>
      <div class="question-body">
        <question-item :info="questionContent" />
      </div>
      <div class="question-foot">
        <a-button @click="onEdit" class="mr10">编辑</a-button>
        <a-button @click="confirm">删除</a-button>
      </div>
    </div>
  </div>
</template>
<script>
import QuestionItem from "./Item";
import { Repeat } from "common/vue/service";
export default {
  props: {
    questionContent: {
      type: Object,
      default: () => {},
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
          title: "分类",
          content: this.questionContent["category_name"] || "暂无",
        },
        {
          title: "类型",
          content: this.typeList[this.questionContent["type"]] || "暂无",
        },
        {
          title: "难度",
          content: this.difficultyList[this.questionContent["difficulty"]] || "暂无"
        },
        {
          title: "更新日期",
          content: this.$dateFormat(this.questionContent["updated_time"]) || "暂无",
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
  },
  components: {
    QuestionItem,
  },
  methods: {
    getDifficulty(str) {
      return this.difficultyList(str)
    },
    confirm() {
      const that = this;
      this.$confirm({
        title: "真的要删除该题目吗？",
        content: "删除题目，可能会影响课时的练习，请谨慎操作！",
        icon: "exclamation-circle",
        okText: "确认",
        cancelText: "取消",
        async onOk() {
          await Repeat.delQuestion(
            $("[name=questionBankId]").val(),
            that.questionContent.id
          )
            .then(async (res) => {
              if (res) {
                that.$message.success("删除成功");
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