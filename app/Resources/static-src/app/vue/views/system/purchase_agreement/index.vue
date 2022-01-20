<template>
  <admin-container>
    <template #title>购买协议设置</template>
    <div class="single-content-sec">
      <a-form-model
        ref="ruleForm"
        :model="form"
        :rules="rules"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 14 }"
      >
        <a-form-model-item label="用户购买协议">
          <a-radio-group v-model="form.enabled">
            <a-radio :value="1">开启</a-radio>
            <a-radio :value="0">关闭</a-radio>
          </a-radio-group>
        </a-form-model-item>

        <template v-if="form.enabled">
          <a-form-model-item label="名称">
            <a-input v-model="form.title" />
          </a-form-model-item>

          <a-form-model-item label="协议内容" prop="content">
            <ckeditor ref="ckeditor" />
          </a-form-model-item>

          <a-form-model-item label="样式设置">
            <a-radio-group v-model="form.type">
              <a-radio value="tick">勾选确认</a-radio>
              <a-radio value="eject">弹出确认</a-radio>
            </a-radio-group>
          </a-form-model-item>
        </template>

        <a-form-model-item :wrapper-col="{ span: 14, offset: 4 }">
          <a-button type="primary" @click="onSubmit">
            提交
          </a-button>
        </a-form-model-item>
      </a-form-model>
    </div>
  </admin-container>
</template>

<script>
import AdminContainer from 'app/vue/views/layouts/AdminContainer.vue';
import Ckeditor from 'app/vue/components/Ckeditor.vue';

export default {
  name: 'PurchaseAgreementSettings',

  components: {
    AdminContainer,
    Ckeditor
  },

  data() {
    return {
      form: {
        enabled: 1,
        title: '',
        content: '',
        type: 'tick'
      },
      rules: {
        content: [
          { required: true, message: '协议内容不能为空' }
        ]
      },
    };
  },

  methods: {
    onSubmit() {
      this.form.content = this.$refs.ckeditor.getData();

      this.$refs.ruleForm.validate(valid => {
        if (!valid) return false;

        console.log(this.form);
      });
    }
  }
}
</script>
