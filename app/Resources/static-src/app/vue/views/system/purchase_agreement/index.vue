<template>
  <admin-container>
    <template #title>购买协议设置</template>
    <div class="single-content-sec" v-show="!loading">
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

        <div v-show="form.enabled">
          <a-form-model-item label="名称">
            <a-input v-model="form.title" />
          </a-form-model-item>

          <a-form-model-item label="协议内容" prop="content">
            <ckeditor ref="ckeditor" />
          </a-form-model-item>

          <a-form-model-item label="样式设置">
            <a-radio-group v-model="form.type">
              <div style="margin-top: 2px;">
                <a-radio value="tick">勾选确认</a-radio>
                <a-popover placement="topLeft">
                  <template slot="content">
                    <img src="/static-dist/app/img/vue/agreement-1.png" alt="">
                  </template>
                  <a-button style="padding: 0;" type="link">查看详情</a-button>
                </a-popover>
              </div>
              <div style="margin-top: 2px;">
                <a-radio value="eject">弹出确认</a-radio>
                <a-popover placement="topLeft">
                  <template slot="content">
                    <img src="/static-dist/app/img/vue/agreement-2.png" alt="">
                  </template>
                  <a-button style="padding: 0;" type="link">查看详情</a-button>
                </a-popover>
              </div>
            </a-radio-group>
          </a-form-model-item>
        </div>

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
import _ from 'lodash';
import AdminContainer from 'app/vue/views/layouts/AdminContainer.vue';
import Ckeditor from 'app/vue/components/Ckeditor.vue';

import { PurchaseAgreement } from 'common/vue/service';

export default {
  name: 'PurchaseAgreementSettings',

  components: {
    AdminContainer,
    Ckeditor
  },

  data() {
    return {
      loading: true,
      form: {
        enabled: 1,
        title: '',
        content: '',
        type: 'tick'
      },
      radioStyle: {
        display: 'block',
        marginTop: '4px',
        height: '30px',
        lineHeight: '30px',
      },
      rules: {
        content: [
          { required: true, message: '协议内容不能为空' }
        ]
      },
    };
  },

  mounted() {
    this.fetchPurchaseAgreement();
  },

  methods: {
    async fetchPurchaseAgreement() {
      const { enabled, title, content, type } = await PurchaseAgreement.get();
      _.assign(this.form, {
        enabled,
        title: title || '用户购买协议',
        content,
        type
      });
      this.$refs.ckeditor.initCkeditor(content);
      this.loading = false;
    },

    onSubmit() {
      this.form.content = this.$refs.ckeditor.getData();

      this.$refs.ruleForm.validate(async valid => {
        if (!valid) return false;

        try {
          await PurchaseAgreement.update({ data: this.form });
          this.$message.success('保存成功！')
        } catch (error) {
          this.$message.error('保存失败！')
        }
      });
    }
  }
}
</script>
