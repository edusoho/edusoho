<script setup>
import {message} from 'ant-design-vue';
import {open} from '../../../common';

const props = defineProps({
  chapter: {type: Object, default: {}},
  previewAs: {type: String, default: null},
  member: {type: Object, default: {}},
  record: {type: Object, default: {}},
  exercise: {type: Object, default: {}},
  moduleId: {type: Number, default: null},
});

const $modal = $('#modal');
const emit = defineEmits(['selectChapter']);
async function exerciseChapter() {
  emit('selectChapter', props.chapter.id);
  //不是该题库练习成员
  if (Object.keys(props.member).length === 0) {
    return;
  }
  //题库练习已关闭并且状态为未开始或继续做题
  if (props.exercise.status === 'closed' && !['reviewing', 'finished'].includes(props.record?.status)) {
    message.error('题库已关闭，无法继续学习');
    return;
  }
  //有效期已过
  if (props.member.canLearn === '0') {
    message.error('学习有效期已过期，无法继续学习');
    return;
  }

  //开始答题
  if (Object.keys(props.record).length === 0) {
    const response = await fetch(`/item_bank_exercise/${props.exercise.id}/module/${props.moduleId}/category/${props.chapter.id}/info_modal`);
    const html = await response.text();
    $modal.html(html).modal('show');
    return;
  }
  //pc不支持一题一答
  if (['doing', 'paused'].includes(props.record.status) && props.record.exercise_mode === '1') {
    const response = await fetch(`/item_bank/exercise/submit_single_mode_not_support_modal`);
    const html = await response.text();
    $modal.html(html).modal('show');
    return;
  }
  //继续答题或查看报告
  open(`/item_bank_exercise/${props.exercise.id}/module/${props.moduleId}/category/${props.chapter.id}/answer`);
}

function showButtonStatus(status) {
  switch (status) {
  case 'doing':
  case 'paused':
    return {
      text: '继续做题',
    };
  case 'reviewing':
  case 'finished':
    return {
      text: '查看报告',
    };
  default:
    return {
      text: '开始做题',
    };
  }
}
</script>

<template>
  <div class="flex items-center w-full">
    <div v-if="props.member && props.previewAs === 'member'" class="flex items-center w-full">
      <div v-if="props.chapter.question_num !== '0'" class="flex items-center justify-between text-14 leading-22 text-[#87898F] w-full">
        <div class="flex">
          <div class="w-180 text-right">
            <span v-if="Object.keys(props.record).length === 0">{{ `0/${props.chapter.question_num}题` }}</span>
            <span v-else>{{ `${props.record.doneQuestionNum}/${props.chapter.question_num}题` }}</span>
          </div>
          <div class="ml-12">
            <span v-if="Object.keys(props.record).length === 0">正确率：0.0%</span>
            <span v-else>{{ `正确率：${props.record.rightRate}%` }}</span>
          </div>
        </div>
        <div>
          <a-button v-if="['doing', 'paused'].includes(props.record.status)" :disabled="props.member.locked === '1'" type="primary" @click.stop="exerciseChapter()">{{ showButtonStatus(props.record.status).text }}</a-button>
          <a-button v-else-if="['reviewing', 'finished'].includes(props.record.status)" style="color: #FF7D00; border-color: #FF7D00; background-color: #FFFFFF" :disabled="props.member.locked === '1'" type="primary" ghost @click.stop="exerciseChapter()">{{ showButtonStatus(props.record.status).text }}</a-button>
          <a-button v-else style="background-color: #FFFFFF" type="primary" :disabled="props.member.locked === '1'" ghost @click.stop="exerciseChapter()">{{ showButtonStatus(props.record.status).text }}</a-button>
        </div>
      </div>
    </div>
    <div v-else class="text-14 leading-22 text-[#87898F] w-full text-right">{{ props.chapter.question_num }}题</div>
  </div>
</template>
