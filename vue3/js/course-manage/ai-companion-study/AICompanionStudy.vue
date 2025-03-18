<script setup>
import {onMounted, reactive, ref} from 'vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import { DeleteOutlined, PlusOutlined } from '@ant-design/icons-vue';
import { Modal } from 'ant-design-vue';
import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
import { createVNode } from 'vue';
import { message } from 'ant-design-vue';

const spinning = ref(false);

const formItemLayout = {
  labelCol: { span: 5 },
  wrapperCol: { span: 18 },
};
const formItemLayoutWithOutLabel = {
  wrapperCol: {
      span: 18,
      offset: 5,
    },
};

const formState = reactive({
  state: false,
  major: null,
  deadlines: [{
      value: '',
      key: Date.now(),
    }],
  diagnose: false,
});

const rules = reactive({
  major: [
    { required: true, message: '请选择专业', trigger: 'blur' }
  ],
});

const options = [...Array(5)].map((_, i) => ({
  value: (i + 10).toString(36) + (i + 1),
}));
const filterOption = (input, option) => {
  return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const removeDeadline = item => {
  const index = formState.deadlines.indexOf(item);
  if (index !== -1) {
    formState.deadlines.splice(index, 1);
  }
};
const addDeadline = () => {
  formState.deadlines.push({
    value: '',
    key: Date.now(),
  });
};

const showConfirm = () => {
  Modal.confirm({
    centered: true,
    title: '已编辑内容未保存，是否保存？',
    icon: createVNode(ExclamationCircleOutlined),
    async onOk() {
      spinning.value = true;
      //todo
      spinning.value = false;
      message.success('保存成功');
    },
  });
};

onMounted(() => {
 //todo
})
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col w-full">
      <div
        class="py-24 pl-32 border border-x-0 border-t-0 border-[#F1F1F1] text-16 leading-16 font-medium text-[rgba(0,0,0,0.88)] border-solid">
        AI伴学服务
      </div>
      <div class="flex justify-center items-center w-[calc(100%-200px)] h-[calc(100%-65px)] absolute z-10 left-200 top-65 bg-[rgba(0,0,0,0.70)]">
        <div class="relative">
          <img class="w-924 h-530" src="../../../img/course-manage/ai-companion-study/poster.png" alt="海报">
          <div class="w-140 py-8 bg-white flex justify-center items-center cursor-pointer absolute bottom-70 left-400" style="border-radius: 9999px;">
            <div class="mr-10 text-16 leading-16 font-normal text-[#165DFF]">联系客服</div>
            <img class="w-10 h-12" src="../../../img/course-manage/ai-companion-study/arrows.svg" alt="箭头">
          </div>
        </div>
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
            name="state"
            extra="开启后为课程学员提供学习计划制定、督学提醒、更精准的专业问答和题目答疑服务"
          >
            <a-switch v-model:checked="formState.state" checked-children="开" un-checked-children="关"/>
          </a-form-item>
          <div v-if="formState.state === true">
            <a-form-item
              label="选择专业"
              name="major"
              extra="请选择AI教师的专业为学员提供精准的题目答疑和解答服务"
            >
              <a-select
                v-model:value="formState.major"
                style="width: 280px"
                placeholder="请选择专业"
                show-search
                :options="options"
                :filter-option="filterOption"
              ></a-select>
            </a-form-item>
            <a-form-item
              v-for="(deadline, index) in formState.deadlines"
              v-bind="index === 0 ? formItemLayout : formItemLayoutWithOutLabel"
              :label="index === 0 ? '学习计划截止时间' : ''"
              :name="['deadlines', index, 'value']"
            >
              <a-date-picker
                v-model:value="deadline.value"
                placeholder="请选择时间"
                style="width: 280px"
              />
              <DeleteOutlined
                v-if="formState.deadlines.length > 1"
                class="ml-12 text-16 text-[#919399]"
                @click="removeDeadline(deadline)"
              />
              <div v-if="index === 9" class="text-14 leading-24 font-normal text-[#919399]">学员学习计划的最晚完成时间，未设置时学员可自由选择时间</div>
            </a-form-item>
            <a-form-item
              v-if="formState.deadlines.length < 10"
              v-bind="formItemLayoutWithOutLabel"
            >
              <a-button type="dashed" @click="addDeadline">
                <PlusOutlined />
                添加时间
              </a-button>
              <div class="text-14 leading-24 font-normal text-[#919399]">学员学习计划的最晚完成时间，未设置时学员可自由选择时间</div>
            </a-form-item>
            <a-form-item
              label="AI 知识点诊断"
              name="diagnose"
            >
              <a-switch v-model:checked="formState.diagnose" checked-children="开" un-checked-children="关"/>
              <a-popover placement="top">
                <template #title>
                  查看示例
                </template>
                <template #content>
                  <img class="w-480 h-318" src="../../../img/course-manage/ai-companion-study/example.png" alt="示例">
                </template>
                <a-button class="text-[--primary-color]" type="link">查看示例</a-button>
              </a-popover>
              <div class="text-14 leading-24 font-normal text-[#919399]">开启后，学员在网校内提交答题后根据答题结果找出学员掌握薄弱的知识点，推荐对应的课程任务进行学习</div>
              <a-alert class="mt-12 w-fit" message="知识点生成中，完成后将通过站内信通知您，请您放心保存AI伴学服务的配置" type="info" show-icon />
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
