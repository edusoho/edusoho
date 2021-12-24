<template>
  <a-modal
    :visible="visible"
    :title="'decorate.custom_link' | trans"
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
      <a-form-model-item :label="'decorate.link_address' | trans" prop="link">
        <a-input v-model="form.link" placeholder="http://" />
      </a-form-model-item>
    </a-form-model>
  </a-modal>
</template>

<script>
const pattern = /^http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?$/i;

const checkLink = (rule, value, callback) => {
  if (value === '') {
    callback(new Error(Translator.trans('decorate.please_enter_the_link_address')));
  } else if (!pattern.test(value)) {
    callback(new Error(Translator.trans('decorate.link_wrong')));
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
        if (!valid) {
          return false;
        }

        this.$emit('update-link', {
          type: 'url',
          target: null,
          url: this.form.link
        });
        this.handleCancel();
      });
    },

    handleCancel() {
      this.visible = false;
    }
  }
}
</script>
