<template>
  <admin-container>
    <template #title>{{ 'admin.system.purchase_agreement_setting' | trans }}</template>
    <div class="single-content-sec" v-show="!loading">
      <a-form-model
        ref="ruleForm"
        :model="form"
        :rules="rules"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 14 }"
      >
        <a-form-model-item :label="'admin.system.purchase_agreement' | trans">
          <a-radio-group v-model="form.enabled">
            <a-radio :value="1">{{ 'decorate.turn_on' | trans }}</a-radio>
            <a-radio :value="0">{{ 'decorate.closure' | trans }}</a-radio>
          </a-radio-group>
        </a-form-model-item>

        <div v-show="form.enabled">
          <a-form-model-item :label="'admin.system.purchase_agreement.title' | trans">
            <a-input v-model="form.title" />
          </a-form-model-item>

          <a-form-model-item :label="'admin.system.purchase_agreement.content' | trans" prop="content">
            <ckeditor ref="ckeditor" />
          </a-form-model-item>

          <a-form-model-item :label="'admin.system.purchase_agreement.style_settings' | trans">
            <a-radio-group v-model="form.type">
              <div style="margin-top: 2px;">
                <a-radio value="tick">{{ 'admin.system.purchase_agreement.check_to_confirm' | trans }}</a-radio>
                <a-popover placement="topLeft">
                  <template slot="content">
                    <img src="/static-dist/app/img/vue/agreement-1.png" alt="">
                  </template>
                  <a-button style="padding: 0;" type="link">{{ 'admin.system.purchase_agreement.see_details' | trans }}</a-button>
                </a-popover>
              </div>
              <div style="margin-top: 2px;">
                <a-radio value="eject">{{ 'admin.system.purchase_agreement.popup_confirmation' | trans }}</a-radio>
                <a-popover placement="topLeft">
                  <template slot="content">
                    <img src="/static-dist/app/img/vue/agreement-2.png" alt="">
                  </template>
                  <a-button style="padding: 0;" type="link">{{ 'admin.system.purchase_agreement.see_details' | trans }}</a-button>
                </a-popover>
              </div>
            </a-radio-group>
          </a-form-model-item>
        </div>

        <a-form-model-item :wrapper-col="{ span: 14, offset: 4 }">
          <a-button type="primary" @click="onSubmit">
            {{ 'admin.system.purchase_agreement.submit' | trans }}
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
          { required: true, message: Translator.trans('admin.system.purchase_agreement.content_empty') }
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
        title: title || Translator.trans('admin.system.purchase_agreement'),
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
          this.$message.success(Translator.trans('admin.system.purchase_agreement.saved_successfully'));
        } catch (error) {
          this.$message.error(Translator.trans('admin.system.purchase_agreement.save_failed'));
        }
      });
    }
  }
}
</script>
