<script setup>
import {reactive, ref} from 'vue';
import {
  QuestionCircleOutlined,
} from '@ant-design/icons-vue';

const props = defineProps({
  manage: {type: Object, default: {}}
});

const tryLookLengthOptions = Array.from({length: 11}, (v, i) => ({
  value: String(i),
  label: i === 0 ? '不支持试看' : `${i}分钟试看`
}));

const learnModeOptions = [
  {label: '自由式', value: 'freeMode'},
  {label: '解锁式', value: 'lockMode'},
];

const enableAudioOptions = [
  {label: '开启', value: '1'},
  {label: '关闭', value: '0'},
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
         style="width: calc(100% + 64px);">基础规则
    </div>
    <a-form
      ref="formRef"
      class="mt-66"
      :model="formState"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 16 }"
    >
      <a-form-item
      >
        <template #label>
          <div class="flex items-center">
            <div>学习模式</div>
            <a-popover>
              <template #content>
                <div class="text-14"><span class="font-medium">自由式：</span>学习过程自由安排</div>
                <div class="text-14"><span class="font-medium">解锁式：</span>根据既定顺序逐个解锁学习</div>
              </template>
              <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
            </a-popover>
          </div>
        </template>
        <div class="flex h-32 items-center">
          <a-radio-group v-model:value="formState.learnMode" :options="learnModeOptions"
                         :disabled="props.manage.course.status !== 'draft' || props.manage.course.platform !== 'self'"/>
        </div>
        <div class="text-[#adadad] text-12 mt-8">计划发布后学习模式无法修改</div>
      </a-form-item>
      <a-form-item
        v-if="props.manage.lessonWatchLimit"
        label="视频观看时长限制"
        name="watchLimit"
        :rules="[
          { pattern: /^[0-9]\d*$/, message: '请输入非负整数', trigger: blur },
          ]"
      >
        <div class="flex items-center space-x-8">
          <a-input v-model:value="formState.watchLimit" style="width: 150px"></a-input>
          <div class="text-[#a1a1a1] font-normal text-14 whitespace-nowrap">倍总时长</div>
          <a-popover>
            <template #content>
              <div class="text-14 font-normal w-300">例：课程视频总时长为100分钟，设置为5倍，则学员总共可观看500分钟，超出时长将提示不能学习。0表示无限制。
                对于手动上传的回放视频，可限制其观看时长。但对于直接录制、跳转到第三方直播平台回放的视频，无法限制。
              </div>
            </template>
            <QuestionCircleOutlined class="text-14 leading-14"/>
          </a-popover>
        </div>
      </a-form-item>
      <a-form-item
        label="任务完成规则"
        name="enableFinish"
      >
        <a-radio-group v-model:value="formState.enableFinish"
                       :disabled="props.manage.course.platform === 'supplier'">
          <a-radio value="1">无限制</a-radio>
          <a-radio value="0">由任务完成条件决定<span>
              <a-popover>
                <template #content>
                  <div class="text-14 font-normal">必须达到完成条件，任务才算完成</div>
                </template>
                <QuestionCircleOutlined class="text-14 leading-14 ml-4"/>
              </a-popover>
            </span></a-radio>
        </a-radio-group>
      </a-form-item>
      <div v-if="props.manage.courseSet !== 'live'">
        <a-form-item
          label="设置免费学习任务"
          name="freeTaskIds"
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
                    <a-tag v-if="formState.freeTaskIds.includes(task.id)" color="#f46300">免费</a-tag>
                  </div>
                </a-list-item>
              </a-list>
            </a-checkbox-group>
            <div class="text-[#a1a1a1] text-14 flex items-center">
              免费{{ props.manage.taskName }}仅支持{{ props.manage.canFreeActivityTypes }}
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
          name="tryLookLength"
        >
          <template #label>
            <div class="flex items-center">
              <div>视频试看</div>
              <a-popover>
                <template #content>
                  <div class="text-14">常用于收费视频内容的前几分钟免费试看</div>
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
        label="音频听课（试用）"
        name="enableAudio"
      >
        <a-radio-group class="base-rule-radio mt-6" v-model:value="formState.enableAudio"
                       :options="enableAudioOptions"
                       :disabled="props.manage.course.platform === 'supplier'"/>
        <div class="text-12 text-[#adadad] mt-8">1.开启后，学员在学习时，可按需切换为音频听课，提高完成率。</div>
        <div class="text-12 text-[#adadad] mt-8">2.当前转音频完成情况 ：{{ props.manage.videoConvertCompletion }}<a
          class="text-[#46c37b] text-14 ml-8 hover:text-[#34a263]" :href="props.manage.courseSetManageFilesUrl"
          target="__blank">查看详情</a></div>
        <div class="text-12 text-[#adadad] mt-8">3.视频含弹题时，在APP端不支持转音频播放</div>
      </a-form-item>
    </a-form>
  </div>
</template>

<style lang="less">

</style>
