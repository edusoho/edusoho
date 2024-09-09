<script setup>
import BaseInfo from './BaseInfo.vue';
import {provide, ref} from 'vue';

const props = defineProps({
  isUnMultiCourseSet: { required: true },
  course: { required: true },
  tags: { required: true },
  imageSrc: { required: true },
  imageSaveUrl: { required: true },
  imageUploadUrl: { required: true },
  enableOrg: { required: true },
  courseSet: { required: true },
});

const baseInfoProps = {
  isUnMultiCourseSet: props.isUnMultiCourseSet,
  tags: props.tags,
  course: props.course,
  imageSrc: props.imageSrc,
  imageSaveUrl: props.imageSaveUrl,
  imageUploadUrl: props.imageUploadUrl,
  enableOrg: props.enableOrg,
  courseSet: props.courseSet,
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
