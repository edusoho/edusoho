<template>
  <div class="multi-class-product">
    <a-button type="primary" @click="modalVisible = true">
      新建产品
    </a-button>

    <a-row>
      <a-col :span="8"><product-card /></a-col>
      <a-col :span="8"><product-card /></a-col>
      <a-col :span="8"><product-card /></a-col>
      <a-col :span="8"><product-card /></a-col>
      <a-col :span="8"><product-card /></a-col>
    </a-row>

    <a-modal
      title="新建产品"
      okText="确认"
      cancelText="取消"
      :width="920"
      :visible="modalVisible"
    >
      <a-form :form="form" :label-col="{ span: 3 }" :wrapper-col="{ span: 21 }">
        <a-form-item label="产品名称">
          <a-input
            placeholder="请输入产品名称"
            v-decorator="['title', { rules: [
              { required: true, message: '产品名称不能为空' },
              { max: 20, message: '产品名称不能超过20个字' },
              { validator: validatorTitle }
            ] }]"
          />
        </a-form-item>
        <a-form-item label="备注">
          <a-input
            placeholder="备注30个字以内"
            v-decorator="['remark', { rules: [{ max: 30, message: '备注不能超过30个字' }] }]"
          />
        </a-form-item>
      </a-form>
      <template slot="footer">
        <a-button key="back" @click="modalVisible = false">
          取消
        </a-button>
        <a-button key="submit" type="primary" :disabled="!form.getFieldValue('title')" @click="addMultiClassProduct">
          确认
        </a-button>
      </template>
    </a-modal>

    <MultiClassModal title="系统默认" :visible="multiClassModalVisible" @close="event => multiClassModalVisible = event" />
  </div>
</template>

<script>
  import _ from 'lodash';
  import { MultiClassProduct, ValidationTitle } from 'common/vue/service';
  import ProductCard from './ProductCard.vue';
  import MultiClassModal from './MultiClassModal.vue';

  export default {
    name: '',
    components: {
      ProductCard,
      MultiClassModal,
    },
    props: {},
    data () {
      return {
        modalVisible: false,
        multiClassModalVisible: false,
        form: this.$form.createForm(this),
      };
    },
    created() {
      console.log(MultiClassProduct)
    },
    methods: {
      validatorTitle: _.debounce(function(rule, value, callback) {
        ValidationTitle.search({
          type: 'multiClassProduct',
          title: value
        }).then(res => {
          console.log('校验标题', res)
        })

        callback()
      }, 300),
      addMultiClassProduct () {
        this.form.validateFields((err, values) => {
          if (err) return;

          MultiClassProduct.add(this.form.getFieldsValue()).then(response => {

          })
        });
      }
    }
  }
</script>