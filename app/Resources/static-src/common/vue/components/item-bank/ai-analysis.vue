<template>
  <div class="ibs-ai-explain ibs-mt16">
    <div class="ibs-ai-left">
      <p v-if="questionId" class="ai-left-tittle">{{ t("itemEngine.aiAssistant") }}</p>
      <p v-else class="ai-left-tittle">{{ t("itemEngine.aiProblemAssistant") }}</p>
      <button
        class="ai-left-btn"
        v-show="status === 'ungenerated'"
        @click="gen"
      >
        <img src="/static-dist/app/img/question-bank/ai.png" alt="" class="ai-left-img"/>
        <span class="ai-left-text">{{ t("itemEngine.analysis") }}</span>
      </button>
      <button
        class="ai-left-stopbtn"
        v-show="status === 'generating'"
        @click="stopGen"
      >
        <img src="/static-dist/app/img/question-bank/ai-stop-gen.png" alt="" class="ai-left-img"/>
        <span class="ai-left-text">{{ t("itemEngine.stopGeneration") }}</span>
      </button>
      <button
        class="ai-left-stopbtn"
        v-show="status === 'generated'"
        @click="gen"
      >
        <img src="/static-dist/app/img/question-bank/ai-re-gen.png" alt="" class="ai-left-img"/>
        <span class="ai-left-text">{{ t("itemEngine.reGenerate") }}</span>
      </button>
      <span class="ai-left-content">{{ t("itemEngine.aiAnalysisRefer") }}</span>
      <p class="ai-left-tips" v-show="unableGenerateTipVisible">{{ t("itemEngine.aiUnableGenerate") }}</p>
    </div>
    <div class="ibs-ai-right">
      <img src="/static-dist/app/img/kk.png" alt="" class="ai-right-img"/>
    </div>
  </div>
</template>

<script>
import Locale from "common/vue/mixins/locale";

export default {
  name: "ai-analysis",
  mixins: [Locale],
  props: {
    questionId: {
      type: String,
      default: undefined,
    },
  },
  data() {
    return {
      status: 'ungenerated',
      unableGenerateTipVisible: false,
      parseDone: false,
      answers: [],
    };
  },
  methods: {
    gen() {
      if (this.questionId) {
        this.$emit('prepareStudentAiAnalysis', this.askAi);
      } else {
        this.$emit('prepareTeacherAiAnalysis', this.askAi);
      }
    },
    async askAi(data) {
      if (this.questionId) {
        data.questionId = this.questionId;
        data.role = 'student';
      } else {
        if (/<img .*>/.test(JSON.stringify(data))) {
          this.unableGenerateTipVisible = true;
          return;
        }
        data.role = 'teacher';
      }
      this.status = 'generating';
      const response = await fetch('/api/ai/question_analysis/generate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8',
          Accept: 'application/vnd.edusoho.v2+json',
        },
        body: JSON.stringify(data),
      });
      this.startOutput();
      await this.parse(response.body.getReader());
    },
    startOutput() {
      this.$emit('clearAnalysis');
      this.answers = [];
      const outputTimer = setInterval(() => {
        if (this.answers.length === 0) {
          return;
        }
        if (this.status === 'generated') {
          clearInterval(outputTimer);
          return;
        }
        this.$emit('appendAnalysis', this.answers.shift());
        if (this.answers.length === 0 && this.parseDone) {
          this.stopGen();
          clearInterval(outputTimer);
        }
      }, 50);
    },
    async parse(reader) {
      const decoder = new TextDecoder();
      let lastMessage = '';
      while (true) {
        const {done, value} = await reader.read();
        const messages = (lastMessage + decoder.decode(value)).split('\n\n');
        messages.forEach((message, index) => {
          if (index === messages.length - 1) {
            lastMessage = message;
          } else {
            const parseMessage = JSON.parse(message.slice(5));
            if (parseMessage.event === 'message') {
              this.answers.push(parseMessage.answer);
            }
          }
        });
        if (done) {
          this.parseDone = true;
          break;
        }
      }
    },
    stopGen() {
      this.status = 'generated';
    },
  },
}
</script>

<style scoped>

</style>
