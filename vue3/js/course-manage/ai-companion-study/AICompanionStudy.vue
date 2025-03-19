<script setup>
import {onMounted, reactive, ref} from 'vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {DeleteOutlined, PlusOutlined} from '@ant-design/icons-vue';
import {Modal} from 'ant-design-vue';
import {ExclamationCircleOutlined} from '@ant-design/icons-vue';
import {createVNode} from 'vue';
import {message} from 'ant-design-vue';
import Api from '../../../api';
import dayjs from 'dayjs';

const props = defineProps({
  courseId: {required: true},
});

const spinning = ref(false);

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

const formState = reactive({
  isActive: false,
  domainId: null,
  planDeadline: [ref(null)],
  isDiagnosisActive: false,
});
const rules = reactive({
  domainId: [
    {required: true, message: '请选择专业', trigger: 'blur'}
  ],
});

const filterOption = (input, option) => {
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const disabledDate = current => {
  return  current && current < dayjs().endOf('day') || formState.planDeadline.some((dateRef) => dayjs(dateRef.value).isSame(current, "day"));
};
const removeDeadline = item => {
  const index = formState.planDeadline.indexOf(item);
  if (index !== -1) {
    formState.planDeadline.splice(index, 1);
  }
};
const addDeadline = () => {
  formState.planDeadline.push(ref(null));
};


const showConfirm = () => {
  Modal.confirm({
    centered: true,
    title: '已编辑内容未保存，是否保存？',
    icon: createVNode(ExclamationCircleOutlined),
    async onOk() {
      spinning.value = true;
      const params = {
        courseId: props.courseId,
        domainId: formState.domainId,
        planDeadline: formState.planDeadline
          .filter(itemRef => itemRef.value != null)
          .map(itemRef => { return dayjs(itemRef.value).format('YYYY-MM-DD') }),
        isDiagnosisActive: formState.isDiagnosisActive === true ? 1 : 0,
      };
      try {
        if (editType.value === 'create') {
          await Api.aiCompanionStudy.createAgentConfig(params);
        } else {
          //todo
        }
      } finally {
        spinning.value = false;
        Modal.destroyAll()
      }
      message.success('保存成功');
    },
  });
};

const masking = ref(true);
const domainOptions = ref([]);
const editType = ref('create');
onMounted(async () => {
  const options = await Api.aiCompanionStudy.getDomains(props.courseId);
  domainOptions.value = options.map(item => ({
    label: item.name,
    value: item.id,
  }));

  const agentConfig = await Api.aiCompanionStudy.getAgentConfig(props.courseId);
  if (agentConfig.agentEnable === true) {
    masking.value = false;
  }
  if (agentConfig.isActive == 1) {
    editType.value = 'update';
    formState.isActive = agentConfig.isActive == 1;
    formState.domainId = agentConfig.domainId;
    formState.planDeadline = agentConfig.planDeadline.map(item => ref(dayjs(item, 'YYYY-MM-DD')));
    formState.isDiagnosisActive = agentConfig.isDiagnosisActive == 1;
  } else {
    editType.value = 'create';
  }
});
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col w-full">
      <div
        class="py-24 pl-32 border border-x-0 border-t-0 border-[#F1F1F1] text-16 leading-16 font-medium text-[rgba(0,0,0,0.88)] border-solid">
        AI伴学服务
      </div>
      <div v-if="masking"
           class="flex justify-center items-center w-[calc(100%-200px)] h-[calc(100%-65px)] absolute z-10 left-200 top-65 bg-[rgba(0,0,0,0.70)]">
        <img class="w-924 h-530" src="../../../img/course-manage/ai-companion-study/poster.png" alt="海报">
      </div>
      <a-spin :spinning="spinning" size="large">
        <a-form
          class="mt-28"
          ref="formRef"
          :model="formState"
          :rules="rules"
          v-bind="formItemLayout"
          autocomplete="off"
          @finish="showConfirm"
        >
          <a-form-item
            label="AI 伴学服务"
            extra="开启后为课程学员提供学习计划制定、督学提醒、更精准的专业问答和题目答疑服务"
          >
            <a-switch v-model:checked="formState.isActive" checked-children="开" un-checked-children="关"/>
          </a-form-item>
          <div v-if="formState.isActive === true">
            <a-form-item
              label="选择专业"
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
            <a-form-item
              v-for="(deadlineRef, index) in formState.planDeadline"
              v-bind="index === 0 ? formItemLayout : formItemLayoutWithOutLabel"
              :label="index === 0 ? '学习计划截止时间' : ''"
            >
              <a-date-picker
                v-model:value="deadlineRef.value"
                placeholder="请选择时间"
                :show-today="false"
                :disabled-date="disabledDate"
                style="width: 280px"
              />
              <DeleteOutlined
                v-if="formState.planDeadline.length > 1"
                class="ml-12 text-16 text-[#919399]"
                @click="removeDeadline(deadlineRef)"
              />
              <div v-if="index === 9" class="text-14 leading-24 font-normal text-[#919399]">
                学员学习计划的最晚完成时间，未设置时学员可自由选择时间
              </div>
            </a-form-item>
            <a-form-item
              v-if="formState.planDeadline.length < 10"
              v-bind="formItemLayoutWithOutLabel"
            >
              <a-button type="dashed" @click="addDeadline">
                <PlusOutlined/>
                添加时间
              </a-button>
              <div class="text-14 leading-24 font-normal text-[#919399]">
                学员学习计划的最晚完成时间，未设置时学员可自由选择时间
              </div>
            </a-form-item>
            <a-form-item
              label="AI 知识点诊断"
            >
              <a-switch v-model:checked="formState.isDiagnosisActive" checked-children="开" un-checked-children="关"/>
              <a-popover placement="top">
                <template #title>
                  查看示例
                </template>
                <template #content>
                  <img class="w-480 h-318" src="../../../img/course-manage/ai-companion-study/example.png" alt="示例">
                </template>
                <a-button class="text-[--primary-color]" type="link">查看示例</a-button>
              </a-popover>
              <div class="text-14 leading-24 font-normal text-[#919399]">
                开启后，学员在网校内提交答题后根据答题结果找出学员掌握薄弱的知识点，推荐对应的课程任务进行学习
              </div>
              <a-alert class="mt-12 w-fit" message="知识点生成中，完成后将通过站内信通知您，请您放心保存AI伴学服务的配置"
                       type="info" show-icon/>
              <a-alert class="mt-12 w-fit" message="知识点生成成功"
                       type="success" show-icon/>
              <a-alert class="mt-12 w-fit" message="知识点生成失败，请联系我们"
                       type="error" show-icon/>
            </a-form-item>
            <a-form-item
              v-bind="formItemLayoutWithOutLabel"
            >
              <a-button type="primary" html-type="submit">保存</a-button>
            </a-form-item>
          </div>
        </a-form>
      </a-spin>
    </div>
  </AntConfigProvider>
</template>
