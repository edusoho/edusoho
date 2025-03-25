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
  return  current && current < dayjs().startOf('day') || formState.planDeadline.some((dateRef) => dayjs(dateRef.value).isSame(current, "day"));
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

const save = async () => {
  spinning.value = true;
  try {
    if (!agentConfig.value.id) {
      const params = {
        courseId: props.courseId,
        domainId: formState.domainId,
        planDeadline: formState.planDeadline
          .filter(itemRef => itemRef.value != null)
          .map(itemRef => { return dayjs(itemRef.value).format('YYYY-MM-DD') }),
        isDiagnosisActive: formState.isDiagnosisActive === true ? 1 : 0,
      };
      await Api.aiCompanionStudy.createAgentConfig(params);
    } else {
      const params = {
        isActive: formState.isActive === true ? 1 : 0,
        domainId: formState.domainId,
        planDeadline: formState.planDeadline
          .filter(itemRef => itemRef.value != null)
          .map(itemRef => { return dayjs(itemRef.value).format('YYYY-MM-DD') }),
        isDiagnosisActive: formState.isDiagnosisActive === true ? 1 : 0,
      };
      await Api.aiCompanionStudy.updateAgentConfig(props.courseId, params);
    }
  } finally {
    spinning.value = false;
    Modal.destroyAll()
  }
  message.success('保存成功');
};

const masking = ref();
const domainOptions = ref([]);
const agentConfig = ref();
onMounted(async () => {
  spinning.value = true;
  const domain = await Api.aiCompanionStudy.getDomainId(props.courseId);
  if (domain.id.trim()) {
    formState.domainId = domain.id;
  }
  const options = await Api.aiCompanionStudy.getDomains(props.courseId);
  domainOptions.value = options.map(item => ({
    label: item.name,
    value: item.id,
  }));

  agentConfig.value = await Api.aiCompanionStudy.getAgentConfig(props.courseId);
  masking.value = agentConfig.value.agentEnable !== true;
  formState.isActive = agentConfig.value?.isActive == 1 ?? false;
  formState.domainId = agentConfig.value?.domainId ?? null;
  formState.planDeadline = agentConfig.value?.planDeadline?.length > 0
    ? agentConfig.value.planDeadline.map(item => ref(dayjs(item, 'YYYY-MM-DD')))
    : [ref(null)];
  formState.isDiagnosisActive = agentConfig.value?.isDiagnosisActive == 1 ?? false;
  spinning.value = false;
});
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col w-full">
      <div class="py-24 pl-32 border border-x-0 border-t-0 border-[#F1F1F1] text-16 leading-16 font-medium text-[rgba(0,0,0,0.88)] border-solid">AI伴学服务</div>
      <a-spin :spinning="spinning" size="large" class="relative min-h-735" tip="加载中...">
        <div v-if="masking" class="flex justify-center items-center w-full min-h-735 absolute z-20 left-0 -top-28 bg-[rgba(0,0,0,0.70)]">
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
                为了约束学员在考试前完成课程学习，可设置多个学习截止时间，供学员在制定学习计划时选择
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
                为了约束学员在考试前完成课程学习，可设置多个学习截止时间，供学员在制定学习计划时选择
              </div>
            </a-form-item>
            <a-form-item
              label="AI 知识点诊断"
            >
              <a-switch v-model:checked="formState.isDiagnosisActive" checked-children="开" un-checked-children="关"/>
              <a-popover placement="right" overlayClassName="example-popover">
                <template #title>
                  查看示例
                </template>
                <template #content>
                  <div class="mb-16 text-14 leading-22 font-normal text-[#87898F] w-480">AI 知识点诊断开启后，学员在网校内提交答题后根据答题结果找出学员掌握薄弱的知识点，推荐对应的课程任务进行学习</div>
                  <img class="w-480 h-318" src="../../../img/course-manage/ai-companion-study/example.png" alt="示例">
                </template>
                <a-button style="color: var(--primary-color)" type="link">查看示例</a-button>
              </a-popover>
              <div class="text-14 leading-24 font-normal text-[#919399]">
                知识点生成完成后将通过站内信通知您，请您放心保存AI伴学服务的配置
              </div>
              <a-alert v-if="agentConfig.indexStatus === 'doing'" class="mt-12 w-fit" :message="`知识点生成中...${agentConfig.indexProgress}%`"
                       type="info" show-icon/>
              <a-alert v-if="agentConfig.indexStatus === 'success'" class="mt-12 w-fit" message="知识点生成成功"
                       type="success" show-icon/>
              <a-alert v-if="agentConfig.indexStatus === 'failed'" class="mt-12 w-fit" message="知识点生成失败，请联系我们"
                       type="error" show-icon/>
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

<style>
.example-popover .ant-popover-inner {
  border-radius: 12px;
}
</style>
