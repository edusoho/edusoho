<script setup>
import BaseInfo from './BaseInfo.vue';
import {ref} from 'vue';
import BaseRule from './BaseRule.vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import MarketSetting from './MarketSetting.vue';
import Api from '../../../api';

const manageProps = defineProps({
  isUnMultiCourseSet: {type: [String, Number]},
  course: Object,
  courseManageUrl: String,
  tags: Object,
  imageSrc: String,
  imageSaveUrl: String,
  imageUploadUrl: String,
  enableOrg: Number,
  courseSet: Object,
  lessonWatchLimit: Number,
  uploadMode: String,
  audioServiceStatus: String,
  videoConvertCompletion: Number,
  courseSetManageFilesUrl: String,
  freeTasks: Object,
  canFreeTasks: Object,
  taskName: String,
  activityMetas: Object,
  canFreeActivityTypes: String,
  freeTaskChangelog: String,
  canModifyCoursePrice: Number,
  liveCapacityUrl: String,
  serviceTags: Object,
  vipInstalled: String,
  vipEnabled: String,
  vipLevels: Object,
});

const baseInfoRef = ref(null);
const baseRuleRef = ref(null);
const marketSettingRef = ref(null);

const submitForm = async () => {
  const baseInfo = await baseInfoRef.value.validateForm();
  const baseRule = await baseRuleRef.value.validateForm();
  const marketSetting = await marketSettingRef.value.validateForm();
  if (!baseInfo && baseRule && marketSetting) {
    return;
  }
  const result = {};
  baseInfo.cover.forEach((item, index) => {
    result[`covers[${index}][type]`] = item.type;
    result[`covers[${index}][id]`] = item.id;
    result[`covers[${index}][url]`] = item.url;
    result[`covers[${index}][uri]`] = item.uri;
  });
  let params = {
    _csrf_token: $('meta[name=csrf-token]').attr('content'),
    ...result,
  }
  delete baseInfo.cover;
  Object.assign(
    params,
    baseInfo,
    baseRule,
    marketSetting,
  );
  await Api.courseSets.updateMultiCourseSet(manageProps.courseSet.id, manageProps.course.id, params);
};
</script>

<template>
  <ant-config-provider>
    <base-info
      ref="baseInfoRef"
      :manage="manageProps"
    />
    <base-rule
      ref="baseRuleRef"
      :manage="manageProps"
    />
    <market-setting
      ref="marketSettingRef"
      :manage="manageProps"
    />
    <a-button type="primary" @click="submitForm" class="ml-200">保存</a-button>
  </ant-config-provider>
</template>

<style scoped lang="less">

</style>
