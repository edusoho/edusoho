<template>
  <a-modal
    class="question-category-add-modal"
    :visible="visible"
    :width="420"
    :centered="true"
    :maskClosable="false"
    title="添加题目分类"
    cancelText="取消"
    okText="保存"
    @cancel="onCancel"
    @ok="onOk"
  >
    <div class="question-category-add-modal-body">
      <span class="question-category-add-modal-body-description">可批量添加分类，一行一个，每个分类最多输入30个字符</span>
      <div class="question-category-add-modal-body-content">
        <div class="question-category-add-modal-body-content-label">
          <span>分类名称</span>
        </div>
        <a-form :form="form">
          <a-form-item>
            <a-textarea
              placeholder="请输入"
              v-decorator="[
                'names',
                {
                  initialValue: names,
                  rules: [
                    { required: true, message: '请输入分类名称' },
                    { validator: validateNames },
                  ]
                },
              ]"
              @change="onNamesChange"
            ></a-textarea>
          </a-form-item>
        </a-form>
      </div>
    </div>
  </a-modal>
</template>

<script>
import {apiClient} from 'common/vue/service/api-client';

export default {
  props: {
    visible: undefined,
    bankId: undefined,
  },
  data() {
    const validateNames = (rule, value, callback) => {
      let err;
      if (value !== undefined && value !== '') {
        value.split('\n').map(name => {
          if (name.length > 30) {
            err = new Error(Translator.trans('question_bank.question_category.name_max_message'));
          }
          if (name.trim().length === 0) {
            err = new Error(Translator.trans('validate.visible_character.message'));
          }
        });
      }
      callback(err);
    };

    return {
      form: this.$form.createForm(this, {name: 'create-question-category'}),
      names: '',
      validateNames
    };
  },
  methods: {
    onCancel() {
      this.$emit('cancel');
    },
    onOk() {
      this.form.validateFields(err => {
        if (!err) {
          apiClient.post(`/api/item_bank/${this.bankId}/item_category`, {names: this.names}).then(res => {
            if (res.ok) {
              this.$emit('ok');
              this.$message.success('添加成功');
            }
          });
        }
      });
    },
    onNamesChange(event) {
      this.names = event.target.value;
    }
  },
}
</script>
