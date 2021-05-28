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
            { required: true, message: 'Please input your note!' }] }]"
          placeholder="请输入课时名称"
        />
      </a-form-item>

      <a-form-item
        label="批量生成"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 21 }"
      >
        <a-switch v-decorator="['switch', { valuePropName: 'checked' }]" />
      </a-form-item>

      <a-form-item
        label="上课时长"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 21 }"
      >
        <a-select
          v-decorator="['gender', { rules: [
            { required: true, message: 'Please select your gender!' }
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
    </a-form>
  </a-modal>
</template>

<script>
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
    return { formLayout: 'horizontal',
      confirmLoading: false,
      form: this.$form.createForm(this, { name: 'create_live' }),
    }
  },

  methods: {
    handleOk() {

    },

    handleCancel() {
      this.$emit('handle-cancel');
    }
  }
}
</script>
