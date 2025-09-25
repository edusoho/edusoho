<script setup>
import {reactive, ref} from 'vue';
import {
  QuestionCircleOutlined,
} from '@ant-design/icons-vue';
import {t} from './vue-lang';

const props = defineProps({
  manage: {type: Object, default: {}}
});

const tryLookLengthOptions = Array.from({length: 11}, (v, i) => ({
  value: String(i),
  label: i === 0 ? t('label.notAvailableForPreview') : t('label.minuteTrialViewing', {min: i})
}));

const learnModeOptions = [
  {label: t('label.freestyle'), value: 'freeMode'},
  {label: t('label.unlockingType'), value: 'lockMode'},
];

const enableAudioOptions = [
  {label: t('label.open'), value: '1'},
  {label: t('label.close'), value: '0'},
];

const formRef = ref(null);
const formState = reactive({
  learnMode: props.manage.course.learnMode,
  watchLimit: props.manage.course.watchLimit,
  enableFinish: props.manage.course.enableFinish,
  tryLookLength: props.manage.course.tryLookLength,
  enableAudio: props.manage.course.enableAudio,
  freeTaskIds: Object.keys(props.manage.freeTasks),
});

const canFreeTasks = ref(props.manage.canFreeTasks.map(task => ({
  ...task,
  isSelected: false
})));

const validateForm = () => {
  return formRef.value.validate()
    .then(() => {
      return formState;
    })
    .catch((error) => {
    });
};

defineExpose({
  validateForm,
});
</script>

<template>
  <div class="flex flex-col w-full relative">
    <div class="absolute -left-32 w-full px-32 font-medium py-10 text-14 text-stone-900 bg-[#f5f5f5]"
         style="width: calc(100% + 64px);">{{ t('title.baseRule') }}
    </div>
    <a-form
      ref="formRef"
      class="mt-66"
      :model="formState"
      :label-col="{ span: 6 }"
      :wrapper-col="{ span: 16 }"
    >
      <a-form-item>
        <template #label>
          <div class="flex items-center">
            <div>{{ t('label.studyMode') }}</div>
            <a-popover>
              <template #content>
                <div class="text-14"><span class="font-medium">{{ t('label.freestyle') }}：</span>{{ t('tip.freestyle') }}</div>
                <div class="text-14"><span class="font-medium">{{ t('label.unlockingType') }}：</span>{{ t('tip.unlockingType') }}</div>
              </template>
              <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
            </a-popover>
          </div>
        </template>
        <div class="flex h-32 items-center">
          <a-radio-group v-model:value="formState.learnMode" :options="learnModeOptions"
                         :disabled="props.manage.course.status !== 'draft' || props.manage.course.platform !== 'self'"/>
        </div>
        <div class="text-[#a1a1a1] text-14 mt-8">{{ t('tip.studyMode') }}</div>
      </a-form-item>
      <a-form-item
        v-if="props.manage.lessonWatchLimit"
        :label="t('label.viewingDurationLimit')"
        name="watchLimit"
        :required="true"
        :validateTrigger="['blur']"
        :rules="[
          { pattern: /^(?:[0-9]|[1-5][0-9])$/, message: t('validate.nonNegativeInteger') },
          ]"
      >
        <div class="flex items-center space-x-8">
          <a-input v-model:value="formState.watchLimit" style="width: 150px"></a-input>
          <div class="text-[#a1a1a1] font-normal text-14 whitespace-nowrap">{{ t('label.totalDuration') }}</div>
          <a-popover>
            <template #content>
              <div class="text-14 font-normal w-300">{{ t('tip.viewingDurationLimit') }}</div>
            </template>
            <QuestionCircleOutlined class="text-14 leading-14"/>
          </a-popover>
        </div>
      </a-form-item>
      <a-form-item
        :label="t('label.taskCompletionRules')"
      >
        <a-radio-group v-model:value="formState.enableFinish"
                       :disabled="props.manage.course.platform === 'supplier'">
          <a-radio value="1">{{ t('label.unlimited') }}</a-radio>
          <a-radio value="0">{{ t('label.conditions') }}<span>
              <a-popover>
                <template #content>
                  <div class="text-14 font-normal">{{ t('tip.conditions') }}</div>
                </template>
                <QuestionCircleOutlined class="text-14 leading-14 ml-4"/>
              </a-popover>
            </span></a-radio>
        </a-radio-group>
      </a-form-item>
      <div v-if="props.manage.courseSet !== 'live'">
        <a-form-item
          :label="t('label.freeTasks')"
        >
          <div class="flex flex-col">
            <a-checkbox-group v-model:value="formState.freeTaskIds" style="width: 100%">
              <a-list size="small" bordered style="width: 100%"
                      v-if="canFreeTasks.length"
                      class="max-h-196 overflow-y-auto mb-8"
              >
                <a-list-item
                  v-for="task in canFreeTasks"
                  :key="task.id"
                >
                  <div class="flex justify-between w-full">
                    <a-checkbox v-model:checked="task.isSelected" :value="task.id" style="width: 100%">
                      <div>
                        <a-tooltip placement="top">
                          <template #title>
                            <div class="text-14 font-normal">
                              {{ props.manage.activityMetas[task.type].name }}{{ props.manage.taskName }}
                            </div>
                          </template>
                          <i class="text-[#999] mr-4" :class="props.manage.activityMetas[task.type].icon"></i>
                        </a-tooltip>
                        <span>{{ props.manage.taskName }} {{ task.number }} : {{ task.title }}</span>
                      </div>
                    </a-checkbox>
                    <a-tag v-if="formState.freeTaskIds.includes(task.id)" color="#f46300">{{ t('tag.free') }}</a-tag>
                  </div>
                </a-list-item>
              </a-list>
            </a-checkbox-group>
            <div class="text-[#a1a1a1] text-14 flex items-center">
              {{ t('tag.free') }}{{ props.manage.taskName }}{{ t('label.onlySupported') }}{{ props.manage.canFreeActivityTypes }}
              <a-popover placement="right">
                <template #content>
                  <div class="text-14">{{ props.manage.freeTaskChangelog }}</div>
                </template>
                <i v-if="props.manage.freeTaskChangelog" class="es-icon es-icon-tip admin-update__icon color-danger"
                   slot="reference"></i>
              </a-popover>
            </div>
          </div>
        </a-form-item>
        <a-form-item
          v-if="props.manage.uploadMode !== 'local'"
        >
          <template #label>
            <div class="flex items-center">
              <div>{{ t('label.videoPreview') }}</div>
              <a-popover>
                <template #content>
                  <div class="text-14">{{ t('tip.videoPreview') }}</div>
                </template>
                <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
              </a-popover>
            </div>
          </template>
          <a-select
            style="width: 250px"
            v-model:value="formState.tryLookLength"
          >
            <a-select-option
              v-for="option in tryLookLengthOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </a-select-option>
          </a-select>
        </a-form-item>
      </div>
      <a-form-item
        v-if="props.manage.audioServiceStatus !== 'needOpen' && props.manage.course.type === 'normal'"
        :label="t('label.audioLearning')"
      >
        <a-radio-group class="base-rule-radio mt-6" v-model:value="formState.enableAudio"
                       :options="enableAudioOptions"
                       :disabled="props.manage.course.platform === 'supplier'"/>
        <div class="text-14 text-[#a1a1a1] mt-8">1.{{ t('tip.afterActivation') }}</div>
        <div class="text-14 text-[#a1a1a1] mt-8">2.{{ t('tip.audioStatus') }} ：{{ props.manage.videoConvertCompletion }}<a
          class="text-[--primary-color] text-14 ml-8 font-medium" :href="props.manage.courseSetManageFilesUrl"
          target="__blank">{{ t('btn.viewDetails') }}</a></div>
        <div class="text-14 text-[#a1a1a1] mt-8">3.{{ t('tip.notAPP') }}</div>
      </a-form-item>
    </a-form>
  </div>
</template>

<style lang="less">

</style>
