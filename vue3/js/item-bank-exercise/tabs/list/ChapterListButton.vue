<script setup>
const props = defineProps({
  questionNum: {type: String, default: '0'},
  previewAs: {type: String, default: null},
  member: {type: Object, default: {}},
  record: {type: Array, default: []},
  exercise: {type: Object, default: {}},
});
</script>

<template>
  <div class="flex items-center">
    <div v-if="props.member && props.previewAs === 'member'" class="flex items-center">
      <div v-if="props.record.length > 0" class="flex items-center">
        111111
      </div>
      <div v-else class="flex items-center text-14 leading-22 text-[#87898F]">
        <div>0/{{ props.questionNum }}题</div>
        <div class="ml-12">正确率：0.0%</div>
        <a-button class="ml-100" type="primary">开始做题</a-button>
      </div>
    </div>
    <div v-else class="text-14 leading-22 text-[#87898F]">{{ props.questionNum }}题</div>
  </div>
</template>
