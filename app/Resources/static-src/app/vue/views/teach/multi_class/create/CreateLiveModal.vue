<template>
  <a-modal
    title="添加直播课时"
    :visible="visible"
    :confirm-loading="confirmLoading"
    ok-text="确认"
    cancel-text="取消"
    @ok="handleOk"
    @cancel="handleCancel"
    width="900px"
  >
    <a-form :form="form">
      <a-form-item
        label="课时名称"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 21 }"
      >
        <a-input
          v-decorator="['title', { rules: [
            { required: true, message: '课时名称不能为空' }
          ]}]"
          placeholder="请输入课时名称"
        />
      </a-form-item>

      <a-form-item
        label="批量生成"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 21 }"
      >
        <a-switch :checked="createMode" @change="onChangeCreateMode" />
      </a-form-item>

      <a-form-item
        v-if="createMode"
        label="课时数量"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 21 }"
      >
        <a-input-number
          style="width: 100%;"
          v-decorator="['taskNum', { rules: [
            { required: true, message: '请输入批量生成课时数量' }
          ]}]"
          :min="1"
          placeholder="请输入批量生成课时数量"
        />
      </a-form-item>

      <a-form-item
        label="开始日期"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 21 }"
      >
        <a-date-picker
          show-time
          format="YYYY-MM-DD HH:mm:ss"
          v-decorator="['startTime', { rules: [
            { type: 'object', required: true, message: '日期时间不能为空' }
          ]}]"
          placeholder="请选择日期时间"
        />
      </a-form-item>

      <a-form-item
        label="上课时长"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 21 }"
      >
        <a-select
          v-decorator="['length', { rules: [
            { required: true, message: '上课时长不能为空' }
          ]}]"
          placeholder="选择上课时长"
        >
          <a-select-option value="90">
            90分钟
          </a-select-option>
          <a-select-option value="120">
            120分钟
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item
        v-if="createMode"
        label="重复方式"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 21 }"
      >
        <a-radio-group
          v-decorator="['repeatType', { initialValue: 'day' }]"
          @change="onChangeRepeatType"
        >
          <a-radio value="day">
            按天重复
          </a-radio>
          <a-radio value="week">
            按周重复
          </a-radio>
        </a-radio-group>
      </a-form-item>

      <a-form-item
        v-if="createMode"
        :label="repeatType === 'day' ? '按天重复' : '每周重复'"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 21 }"
      >
        <template v-if="repeatType === 'day'">
          <a-select
            v-decorator="['repeatData', { initialValue: ['2'] }]"
            placeholder="选择上课时长"
          >
            <a-select-option value="2">
              每2天一次课
            </a-select-option>
            <a-select-option value="3">
              每3天一次课
            </a-select-option>
          </a-select>
        </template>
        <template v-else>
          <a-checkbox :indeterminate="indeterminate" :checked="checkAll" @change="onCheckAllChange">全选</a-checkbox>
          <a-checkbox-group
            v-decorator="['repeatData', { initialValue: defaultCheckedList }]"
            :options="repeatDataOptions"
            @change="onChangeCheckedList"
          />
        </template>
      </a-form-item>
    </a-form>
  </a-modal>
</template>

<script>
import moment from 'moment';

const repeatDataOptions = [
  { label: '周一', value: 'Monday' },
  { label: '周二', value: 'Tuesday' },
  { label: '周三', value: 'Wednesday' },
  { label: '周四', value: 'Thursday' },
  { label: '周五', value: 'Friday' },
  { label: '周六', value: 'Saturday' },
  { label: '周日', value: 'Sunday' }
];
const defaultCheckedList = ['Monday', 'Friday'];
const checkedListAll = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

export default {
  name: 'CreateLiveModal',

  props: {
    visible: {
      type: Boolean,
      required: true,
      default: false
    }
  },

  data() {
    return {
      confirmLoading: false,
      form: this.$form.createForm(this, { name: 'create_live' }),
      createMode: true,
      repeatType: 'day',
      indeterminate: true,
      checkAll: false,
      defaultCheckedList,
      repeatDataOptions
    }
  },

  methods: {
    moment,

    onChangeCreateMode(checked) {
      this.createMode = checked;
    },

    onChangeRepeatType(e) {
      this.repeatType = e.target.value;
      this.form.resetFields(['repeatData']);
    },

    onChangeCheckedList(checkedList) {
      this.indeterminate = !!checkedList.length && checkedList.length < repeatDataOptions.length;
      this.checkAll = checkedList.length === repeatDataOptions.length;
    },

    onCheckAllChange(e) {
      this.form.setFieldsValue({ ['repeatData']: e.target.checked ? checkedListAll : [] });
      Object.assign(this, {
        indeterminate: false,
        checkAll: e.target.checked
      });
    },

    handleOk() {
      this.form.validateFields((err, values) => {
        if (!err) {
          this.confirmLoading = true;
        }
      });
    },

    handleCancel() {
      this.$emit('handle-cancel');
    }
  }
}
</script>
