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
            { required: true, message: '请填写课时名称' },
            { max: 40, message: '最长 40 个字符' }
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
            { required: true, message: '请输入批量生成课时数量' },
            { validator: validatorTaskNum }
          ]}]"
          :min="1"
          placeholder="请输入课时数量"
        />
      </a-form-item>

      <a-form-item
        label="开始日期"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 21 }"
      >
        <a-date-picker
          :show-time="{ format: 'HH:mm' }"
          format="YYYY-MM-DD HH:mm"
          :disabled-date="disabledDate"
          :disabled-time="disabledDateTime"
          v-decorator="['startDate', {
            initialValue: moment().add(5, 'minutes'),
            rules: [
              { type: 'object', required: true, message: '日期时间不能为空' },
              { validator: validatorStartDate }
            ]
          }]"
          placeholder="请选择日期时间"
        />
      </a-form-item>

      <a-form-item
        label="上课时长"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 21 }"
      >
        <a-select
          v-decorator="['length', {
            initialValue: 60,
            rules: [{ required: true, message: '上课时长不能为空' }
          ]}]"
          placeholder="选择上课时长"
        >
          <a-select-option v-for="i in 10" :key="`length${i}`" :value="30 * i">{{ 30 * i }} 分钟</a-select-option>
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
            v-decorator="['repeatData', { initialValue: 1 }]"
            placeholder="选择上课时长"
          >
            <a-select-option v-for="i in 6" :value="i" :key="i">
              每 {{ i }} 天一次课
            </a-select-option>
          </a-select>
        </template>
        <template v-else>
          <a-checkbox :indeterminate="indeterminate" :checked="checkAll" @change="onCheckAllChange">全选</a-checkbox>
          <a-checkbox-group
            v-decorator="['repeatData', {
              initialValue: checkedList,
              rules: [{ required: true, message: '请选择每周重复天数' }]
            }]"
            :options="repeatDataOptions"
            @change="onChangeCheckedList"
          />
        </template>
      </a-form-item>
    </a-form>
  </a-modal>
</template>

<script>
import _ from '@codeages/utils';
import { Course } from 'common/vue/service';

const repeatDataOptions = [
  { label: '周一', value: 'Monday' },
  { label: '周二', value: 'Tuesday' },
  { label: '周三', value: 'Wednesday' },
  { label: '周四', value: 'Thursday' },
  { label: '周五', value: 'Friday' },
  { label: '周六', value: 'Saturday' },
  { label: '周日', value: 'Sunday' }
];
const checkedListAll = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

export default {
  name: 'CreateLiveModal',

  props: {
    visible: {
      type: Boolean,
      required: true,
      default: false
    },

    courseId: {
      type: [Number, String],
      required: true,
      default: 0
    }
  },

  data() {
    return {
      confirmLoading: false,
      form: this.$form.createForm(this, { name: 'create_live' }),
      createMode: false,
      repeatType: 'day',
      checkedList: [],
      indeterminate: false,
      checkAll: false,
      repeatDataOptions
    }
  },

  methods: {
    moment,

    onChangeCreateMode(checked) {
      this.createMode = checked;
    },

    onChangeRepeatType(e) {
      this.form.resetFields(['repeatData']);
      this.repeatType = e.target.value;
    },

    onChangeCheckedList(checkedList) {
      this.checkedList = checkedList;
      this.indeterminate = !!checkedList.length && checkedList.length < repeatDataOptions.length;
      this.checkAll = checkedList.length === repeatDataOptions.length;
    },

    onCheckAllChange(e) {
      const checkedList = e.target.checked ? checkedListAll : [];
      this.form.setFieldsValue({ ['repeatData']: checkedList });
      _.assign(this, {
        checkedList,
        indeterminate: false,
        checkAll: e.target.checked
      });
    },

    validatorTaskNum: _.debounce((rule, value, callback) => {
      value > 50 ? callback('一次批量生成最大为50个课时') : callback();
    }, 300),

    validatorStartDate: _.debounce((rule, value, callback) => {
      value._d <= moment() ? callback('开始时间不能小于当前时间') : callback();
    }, 300),

    range(start, end) {
      const result = [];
      for (let i = start; i < end; i++) {
        result.push(i);
      }
      return result;
    },

    disabledDate(current) {
      return current && current < moment().startOf('day');
    },

    disabledDateTime() {
      return {
        disabledHours: () => this.range(0, moment().hour())
      };
    },

    async createTask(params) {
      let result = await Course.addLiveTask(this.courseId, params);
      const { data } = result;
      this.$emit('change-lesson-directory', { addData: data });
      this.handleCancel();
    },

    batchCreation(params) {
      const { taskNum } = params;
      let loopNum = _.floor(taskNum / 5);

      if (taskNum % 5 != 0) {
        loopNum++;
      }

      for (let index = 0; index < loopNum; index++) {
        this.createTask(_.assign({}, _.assign(params, {
          start: index * 5,
          limit: 5
        })));
      }
    },

    handleOk() {
      this.form.validateFields((err, values) => {
        if (!err) {
          values.startDate = values.startDate._d;
          if (this.createMode) {
            this.handleCancel();

            let that = this;

            this.$confirm({
              title: '提醒',
              content: `确定批量新增 ${values.taskNum} 个直播课时吗?`,
              onOk() {
                that.batchCreation(values);
              },
              onCancel() {
                that.handleCancel(true);
              }
            });
          } else {
            this.createTask(values);
          }
        }
      });
    },

    handleCancel(visible = false) {
      this.$emit('handle-cancel', visible);
    }
  }
}
</script>
