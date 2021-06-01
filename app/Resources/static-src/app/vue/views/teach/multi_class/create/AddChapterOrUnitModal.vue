<template>
  <a-modal
    :title="modalInfo.title"
    :visible="visible"
    okText="新增一栏"
    @ok="handleOk"
    @cancel="handleCancel"
  >
    <a-form :form="form" >
      <a-form-item
        :label="modalInfo.label"
        :label-col="{ span: 5 }"
        :wrapper-col="{ span: 16 }"
      >
        <a-input
          v-decorator="['title', { rules: [{ required: true, message: '标题不能为空' }] }]"
        />
      </a-form-item>
    </a-form>
  </a-modal>
</template>

<script>
export default {
  name: 'AddChapterOrUnitModal',

  props: {
    visible: {
      type: Boolean,
      required: true,
      default: false
    },

    type: String
  },

  data() {
    return {
      form: this.$form.createForm(this, { name: 'add_chapter_or_unit' })
    }
  },

  computed: {
    modalInfo() {
      const types = {
        chapter: '章',
        unit: '节'
      };

      const text = types[this.type];

      return {
        title: `创建 ${text}`,
        label: `${text} 标题`
      }
    }
  },

  methods: {
    handleOk() {
      this.form.validateFields((err, values) => {
        if (!err) {
          console.log('Received values of form: ', values);
        }
      });
    },

    handleCancel() {
      this.$emit('handle-cancel');
      this.form.resetFields();
    }
  }
}
</script>
