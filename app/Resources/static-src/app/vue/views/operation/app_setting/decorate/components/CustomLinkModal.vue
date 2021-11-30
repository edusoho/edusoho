<template>
  <a-modal
    :visible="visible"
    title="自定义链接"
    @ok="handleOk"
    @cancel="handleCancel"
  >
    <a-form-model
      ref="customLinkForm"
      :model="form"
      :rules="rules"
      :label-col="labelCol"
      :wrapper-col="wrapperCol"
    >
      <a-form-model-item label="链接地址" prop="link">
        <a-input v-model="form.link" placeholder="http://" />
      </a-form-model-item>
    </a-form-model>
  </a-modal>
</template>

<script>
const pattern = /^http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?$/i;

const checkLink = (rule, value, callback) => {
  if (value === '') {
    callback(new Error('请输入链接地址'));
  } else if (!pattern.test(value)) {
    callback(new Error('链接有误，请以 http:// 或者 https:// 开头'));
  } else {
    callback();
  }
}

export default {
  name: 'CustomLinkModal',

  data() {
    return {
      visible: false,
      labelCol: { span: 4 },
      wrapperCol: { span: 20 },
      form: {
        link: ''
      },
      rules: {
        link: [
          { validator: checkLink, trigger: 'blur' }
        ]
      }
    }
  },

  methods: {
    showModal() {
      this.visible = true;
    },

    handleOk() {
      this.$refs.customLinkForm.validate(valid => {
        if (valid) {
          const params = {
            type: '',
            target: '_blank',
            url: this.form.link
          };
          this.$emit('update-link', params);
          this.handleCancel();
        } else {
          return false;
        }
      });
    },

    handleCancel() {
      this.visible = false;
    }
  }
}
</script>
