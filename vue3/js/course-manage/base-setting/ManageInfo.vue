<script setup>
import BaseInfo from './BaseInfo.vue';
import {ref} from 'vue';
import BaseRule from './BaseRule.vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import MarketSetting from './MarketSetting.vue';

const props = defineProps({
  isUnMultiCourseSet: {required: true},
  course: {required: true},
  tags: {required: true},
  imageSrc: {required: true},
  imageSaveUrl: {required: true},
  imageUploadUrl: {required: true},
  enableOrg: {required: true},
  courseSet: {required: true},
  lessonWatchLimit: { required: true },
  uploadMode: { required: true },
  audioServiceStatus: { required: true },
  videoConvertCompletion: { required: true },
  courseSetManageFilesUrl: { required: true },
  freeTasks: { required: true },
  canFreeTasks: { required: true },
  taskName: { required: true },
  activityMetas: { required: true },
  canFreeActivityTypes: { required: true },
  freeTaskChangelog: { required: true },
  canModifyCoursePrice: { required: true },
  liveCapacityUrl: { required: true },
  serviceTags: { required: true },
  vipInstalled: { required: true },
  vipEnabled: { required: true },
  vipLevels: { required: true },
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
};

const baseRuleProps = {
  course: props.course,
  lessonWatchLimit: props.lessonWatchLimit,
  courseSet: props.courseSet,
  uploadMode: props.uploadMode,
  audioServiceStatus: props.audioServiceStatus,
  videoConvertCompletion: props.videoConvertCompletion,
  courseSetManageFilesUrl: props.courseSetManageFilesUrl,
  freeTasks: props.freeTasks,
  canFreeTasks: props.canFreeTasks,
  taskName: props.taskName,
  activityMetas: props.activityMetas,
  canFreeActivityTypes: props.canFreeActivityTypes,
  freeTaskChangelog: props.freeTaskChangelog,
}

const marketSettingProps = {
  course: props.course,
  courseSet: props.courseSet,
  canModifyCoursePrice: props.canModifyCoursePrice,
  liveCapacityUrl: props.liveCapacityUrl,
  serviceTags: props.serviceTags,
  vipInstalled: props.vipInstalled,
  vipEnabled: props.vipEnabled,
  vipLevels: props.vipLevels,
}

const baseInfoRef = ref(null);
const baseRuleRef = ref(null);
const marketSettingRef = ref(null);

const submitForm = async () => {
  if (baseInfoRef.value && baseRuleRef.value ) {
    const baseInfo = await baseInfoRef.value.validateForm();
    const baseRule = await baseRuleRef.value.validateForm()
    console.log(baseInfo);
    console.log(baseRule);
  }

};


</script>

<template>
  <ant-config-provider>
    <base-info
      ref="baseInfoRef"
      :params="baseInfoProps"
    />
    <base-rule
      ref="baseRuleRef"
      :params="baseRuleProps"
    />
    <market-setting
      ref="marketSettingRef"
      :params="marketSettingProps"
    />
    <a-button @click="submitForm">获取表单值</a-button>
  </ant-config-provider>
</template>

<style scoped lang="less">

</style>
