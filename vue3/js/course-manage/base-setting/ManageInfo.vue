<script setup>
import BaseInfo from './BaseInfo.vue';
import {provide, ref} from 'vue';
import Api from '../../../api';

const props = defineProps({
  isUnMultiCourseSet: { required: true },
  course: { required: true },
  tags: { required: true },
});

const baseInfoProps = {
  isUnMultiCourseSet: props.isUnMultiCourseSet,
  tags: props.tags,
  course: props.course,
}

const baseInfoRef = ref(null);

const needValidatorForm = ref(false);
provide('needValidatorForm', needValidatorForm);
const submitForm = async () => {
  needValidatorForm.value = true;
}
</script>

<template>
  <base-info
    ref="baseInfoRef"
    :params="baseInfoProps"
  />
  <a-button @click="submitForm">获取表单值</a-button>
</template>

<style scoped lang="less">

</style>
