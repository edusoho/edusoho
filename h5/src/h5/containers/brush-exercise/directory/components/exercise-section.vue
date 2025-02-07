<template>
  <div class="flex flex-col">
    <div class="flex justify-between items-center" :class="{ 'bg-[#FAFAFA]': level === 0 }" style="padding: 8px 12px; border-radius: 6px;">
      <div class="flex items-center w-full" @click="isUnfold = !isUnfold">
        <van-icon v-if="!isUnfold" name="arrow-down" color="#5E6166" class="mr-12" :class="{ 'opacity-0': section.children.length === 0 }"/>
        <van-icon v-if="isUnfold" name="arrow-up" color="#5E6166" class="mr-12" :class="{ 'opacity-0': section.children.length === 0 }"/>
        <div class="w-full mr-12 text-14 text-[#37393D] font-normal truncate" :class="{ 'font-medium': level === 0, 'ml-16': level === 2 }" style="line-height: 22px;">{{ section.name }}</div>
      </div>
      <div v-if="allNum !== '0'" class="flex items-center">
        <div class="mr-12 text-14 font-normal text-[#87898F] h-fit" style="line-height: 22px; white-space: nowrap;">{{ learnNum }}{{ allNum }}题</div>
        <button class="text-12 font-normal border-[#408ffb] bg-white whitespace-nowrap p-4 px-8" :class="[btnText.class]" style="line-height: 20px; border-radius: 6px;" @click="clickBtn()">{{ btnText.text }}</button>
      </div>
    </div>
    <div v-if="level === 2 && !isLast" class="border-b border-[#F2F3F5] my-8 ml-44"></div>
    <div v-else class="mb-8"></div>
    <div v-show="isUnfold">
      <div v-for="(item, index) in section.children" :key="item.id" :ref="'exercise_' + item.id">
        <exercise-section
          :exercise-id="exerciseId"
          :module-id="moduleId"
          :is-last="index + 1 === section.children.length"
          :level="level + 1"
          :section="item"
        ></exercise-section>
      </div>
    </div>
  </div>
</template>

<script>
import { getBtnText } from '@/utils/itemBank-status.js';
import { mapState } from 'vuex';
import { closedToast } from '@/utils/on-status.js';
import exerciseSection from './exercise-section.vue';

export default {
  name: 'exercise-section',
  components: {exerciseSection},
  data() {
    return {
      isUnfold: true,
    };
  },
  props: {
    section: {},
    moduleId: {
      type: String,
      default: '',
    },
    exerciseId: {
      type: Number,
      default: -1,
    },
    level: {
      type: Number,
      default: 0,
    },
    isLast: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    ...mapState('ItemBank', {
      isMember: state => state.ItemBankExercise.isMember,
      ItemBankExercise: state => state.ItemBankExercise,
    }),
    btnText() {
      return getBtnText(this.section.latestAnswerRecord?.status || '');
    },
    hasQuestion() {
      return this.section.question_num > 0;
    },
    learnNum() {
      if (!this.isMember) {
        return '';
      }
      if (this.section.latestAnswerRecord) {
        return `${this.section.latestAnswerRecord.doneQuestionNum}/`;
      }
      return '0/';
    },
    allNum() {
      if (this.section.latestAnswerRecord) {
        return `${this.section.latestAnswerRecord.questionNum}`;
      }
      return `${this.section.question_num}`;
    },
  },
  watch: {},
  mounted() {
    if (this.$route.query.categoryId && this.section.children.length > 0) {
      this.$nextTick(() => {
        this.scrollToCategory(this.$route.query.categoryId);
      });
    }
  },
  methods: {
    clickBtn() {
      const status = this.section.latestAnswerRecord?.status;
      switch (status) {
        case 'doing':
        case 'paused':
          this.continueDo(this.section);
          break;
        case 'reviewing':
        case 'finished':
          this.goResult(this.section);
          break;
        default:
          this.startDo(this.section);
          break;
      }
    },
    startDo(item) {
      if (this.ItemBankExercise?.status == 'closed' || this.ItemBankExercise?.canLearn == '0') {
        closedToast('exercise')
        return
      }

      const query = {
        moduleId: this.moduleId,
        categoryId: item.id,
        exerciseId: this.exerciseId,
        backUrl: `/item_bank_exercise/${this.exerciseId}?categoryId=${item.id}`,
      };
      this.$router.push({ path: '/brushIntro', query });
    },
    continueDo(item) {
      if (this.ItemBankExercise?.status == 'closed' || this.ItemBankExercise?.canLearn == '0') {
        closedToast('exercise')
        return
      }

      const query = {
        moduleId: this.moduleId,
        categoryId: item.id,
        exerciseId: this.exerciseId,
        answer_record_id: item.latestAnswerRecord.answerRecordId,
        backUrl: `/item_bank_exercise/${this.exerciseId}?categoryId=${item.id}`
      };
      this.$router.push({ path: '/brushIntro', query });
    },
    goResult(item) {
      const query = {
        type: 'chapter',
        title: item.name,
        exerciseId: this.exerciseId,
        categoryId: item.id,
        moduleId: this.moduleId,
        backUrl: `/item_bank_exercise/${this.exerciseId}?categoryId=${item.id}`
      };
      const answerRecordId = item.latestAnswerRecord.answerRecordId;
      this.$router.push({
        path: `/brushResult/${answerRecordId}`,
        query,
      });
    },
    scrollToCategory() {
      const targetElement = this.$refs['exercise_' + this.$route.query.categoryId];
      if (targetElement) {
        const offsetTop = targetElement[0].offsetTop || targetElement.offsetTop;
        window.scrollTo({
          top: offsetTop + 222,
          behavior: 'smooth',
        });
      }
    }
  },
};
</script>
