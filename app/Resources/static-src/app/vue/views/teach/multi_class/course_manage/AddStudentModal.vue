<template>
  <a-modal
    title="添加学员"
    :visible="visible"
    @cancel="handleCancel"
  >
    <a-form
      :form="form"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 20 }"
    >
      <a-form-item label="学员" extra="只能添加系统中已经注册的用户">
        <a-input
          v-decorator="['name', { rules: [
            { required: true, message: '请输入学员' }
          ]}]"
          placeholder="邮箱／手机／用户名"
        />
      </a-form-item>

      <a-form-item label="购买价格" extra="本课程的价格为 0.00 元">
        <a-input
          type="number"
          v-decorator="['price', { rules: [
            { validator: validatorPrice }
          ]}]"
          addon-after="元"
        />
      </a-form-item>

      <a-form-item label="备注" extra="选填">
        <a-input
          v-decorator="['price1', { rules: [
            {  }
          ]}]"
        />
      </a-form-item>
    </a-form>

    <template slot="footer">
      <a-button key="back" @click="handleCancel">
        取消
      </a-button>
      <a-button key="submit" type="primary" @click="handleSubmit">
        确认
      </a-button>
    </template>
  </a-modal>
</template>

<script>
export default {
  props: {
    visible: {
      type: Boolean,
      required: true,
      default: false
    }
  },

  data() {
    return {
      form: this.$form.createForm(this, { name: 'dynamic_rule' })
    };
  },

  methods: {
    handleCancel() {
      this.$emit('handle-cancel');
    },

    check() {
      this.form.validateFields(err => {
        if (!err) {
          console.info('success');
        }
      });
    },

    handleChange(e) {
      this.checkNick = e.target.checked;
      this.$nextTick(() => {
        this.form.validateFields(['nickname'], { force: true });
      });
    },

    handleSubmit() {
      this.form.validateFields(err => {
        if (!err) {
          console.info('success');
        }
      });
    },

    validatorPrice(rule, value, callback) {
      if (value > 0) {
        callback();
        return;
      }
      callback('请输入正整数');
    }
  }
}
</script>
