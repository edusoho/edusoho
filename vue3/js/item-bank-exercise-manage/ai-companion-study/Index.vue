<script setup>
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {onMounted, reactive, ref} from 'vue';
import Api from '../../../api';
import {message} from 'ant-design-vue';

const props = defineProps({
  exerciseId: {required: true},
});

const spinning = ref(false);
const masking = ref(false);

const formState = reactive({
  isActive: false,
  domainId: null,
});
const rules = reactive({
  domainId: [
    {required: true, message: '请选择专业', trigger: 'blur'}
  ],
});
const formItemLayout = {
  labelCol: {span: 5},
  wrapperCol: {span: 18},
};
const formItemLayoutWithOutLabel = {
  wrapperCol: {
    span: 18,
    offset: 5,
  },
};

const agentConfig = ref();
const domainOptions = ref([]);

const filterOption = (input, option) => {
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

onMounted(async () => {
  try {
    spinning.value = true;
    const res = await Api.aiCompanionStudy.getAgentStatus();
    masking.value = res.enable !== true;
    agentConfig.value = await Api.itemBank.getItemBankExercises(props.exerciseId);
    formState.isActive = agentConfig.value?.isAgentActive === '1';
    formState.domainId = agentConfig.value?.agentDomainId === '' ? null : agentConfig.value.agentDomainId;
  } finally {
    spinning.value = false;
  }
  const options = await Api.aiCompanionStudy.getDomains();
  domainOptions.value = options.map(item => ({
    label: item.name,
    value: item.id,
  }));
  if (formState.domainId) {
    return
  }
  const domain = await Api.aiCompanionStudy.getDomainId({exerciseId: props.exerciseId});
  if (domain.id.trim()) {
    formState.domainId = domain.id;
  }
});

const save = async () => {
  try {
    spinning.value = true;
    const params = {
      isActive: formState.isActive === true ? '1' : '0',
      domainId: formState.domainId,
    };
    await Api.itemBank.setItemBankExerciseAgent(props.exerciseId, params);
  } finally {
    spinning.value = false;
  }
  agentConfig.value = await Api.itemBank.getItemBankExercises(props.exerciseId);
  message.success('保存成功');
};
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col w-full">
      <div class="py-24 pl-32 border border-x-0 border-t-0 border-[#F1F1F1] text-16 leading-16 font-medium text-[rgba(0,0,0,0.88)] border-solid">AI伴学服务</div>
      <a-spin :spinning="spinning" size="large" class="relative min-h-735" tip="加载中...">
        <div v-if="masking" class="flex justify-center items-center w-full min-h-735 absolute z-20 left-0 -top-93 bg-[rgba(0,0,0,0.70)]">
          <img class="w-924 h-530" src="../../../img/course-manage/ai-companion-study/poster.png" alt="海报">
        </div>
        <a-form
          class="mt-28"
          ref="formRef"
          :model="formState"
          :rules="rules"
          v-bind="formItemLayout"
          autocomplete="off"
          @finish="save"
        >
          <a-form-item
            label="AI 伴学服务"
            extra="开启后为题库学员提供学习计划制定、督学提醒、更精准的专业问答和题目答疑服务"
          >
            <a-switch v-model:checked="formState.isActive" checked-children="开" un-checked-children="关"/>
          </a-form-item>
          <div v-if="formState.isActive === true">
            <a-form-item
              label="选择AI智能体老师"
              name="domainId"
              extra="请选择AI教师的专业为学员提供精准的题目答疑和解答服务"
            >
              <a-select
                v-model:value="formState.domainId"
                style="width: 280px"
                placeholder="请选择专业"
                show-search
                :options="domainOptions"
                :filter-option="filterOption"
              ></a-select>
            </a-form-item>
          </div>
          <a-form-item
            v-bind="formItemLayoutWithOutLabel"
          >
            <a-button type="primary" html-type="submit">保存</a-button>
          </a-form-item>
        </a-form>
      </a-spin>
    </div>
  </AntConfigProvider>
</template>
