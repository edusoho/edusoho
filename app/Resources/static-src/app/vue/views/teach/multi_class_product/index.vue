<template>
  <aside-layout :breadcrumbs="[{ name: '产品库' }]">
    <a-spin class="multi-class-product" :spinning="getListLoading || ajaxProductLoading">
      <div class="clearfix">
        <a-input-search placeholder="请输入产品名称"
          v-model="title"
          style="width: 262px" @search="searchProductList" />

        <a-button class="pull-right" type="primary" @click="createMultiClassProduct">
          新建产品
        </a-button>
      </div>

      <a-row :gutter="24">
        <a-col :sm="24" :lg="12" :xl="8" v-for="product in productList" :key="product.id">
          <product-card
            class="cd-mt24"
            :product="product"
            @edit="startEditMultiClassProduct"
            @delete="deleteMultiClassProduct"
            @lookover="lookoverMultiClass"
          />
        </a-col>
      </a-row>

      <div class="text-center">
        <a-pagination class="mt6"
          v-if="paging && productList.length > 0"
          v-model="paging.page"
          :total="paging.total"
          show-less-items
          @change="onChangePagination"
        />
      </div>

      <a-modal
        :title="modalTitle"
        okText="确认"
        cancelText="取消"
        :width="920"
        :visible="modalVisible"
        @cancel="closeModal"
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
          <a-button key="back" @click="closeModal">
            取消
          </a-button>
          <a-button key="submit" type="primary"
            :loading="ajaxProductLoading"
            :disabled="!form.getFieldValue('title')"
            @click="ajaxMultiClassProduct">
            确认
          </a-button>
        </template>
      </a-modal>

      <MultiClassModal
        :product="currentProduct"
        :visible="multiClassModalVisible"
        @close="event => multiClassModalVisible = event" />
    </a-spin>

    <a-empty v-if="!(getListLoading || ajaxProductLoading) && !productList.length" />
  </aside-layout>
</template>

<script>
  import AsideLayout from 'app/vue/views/layouts/aside.vue';
  import _ from 'lodash';
  import { MultiClassProduct, ValidationTitle } from 'common/vue/service';
  import ProductCard from './ProductCard.vue';
  import MultiClassModal from './MultiClassModal.vue';

  export default {
    name: 'MultiClassProduct',
    components: {
      ProductCard,
      MultiClassModal,
      AsideLayout
    },
    props: {},
    data () {
      return {
        modalVisible: false,
        multiClassModalVisible: false,
        form: this.$form.createForm(this),
        productList: [],
        paging: {
          offset: 0,
          limit: 9,
          total: 0,
        },
        title: '',
        getListLoading: false,
        ajaxProductLoading: false,
        editingProduct: null,
        currentProduct: {},
        modalTitle: ''
      };
    },
    created() {
      this.getProductList()
    },
    methods: {
      async getProductList (params = {}) {
        this.getListLoading = true;
        try {
          const { data, paging } = await MultiClassProduct.search({
            keywords: params.title || this.title,
            offset: params.offset || this.paging.offset || 0,
            limit: params.limit || this.paging.limit || 9,
          })
          paging.page = (paging.offset / paging.limit) + 1;

          this.productList = data;
          this.paging = paging
        } finally {
          this.getListLoading = false;
        }
      },
      searchProductList (title = '') {
        this.getProductList({ title })
      },
      createMultiClassProduct(){
        this.modalVisible = true;
        this.modalTitle = '新建产品'
      },
      validatorTitle: _.debounce(async function(rule, value, callback) {
        const { result } = await ValidationTitle.search({
          type: 'multiClassProduct',
          title: value
        })

        if (!result) {
          this.form.setFields({
            title: { value, errors: [new Error('产品名称不能与已创建的相同')] }
          })
          return
        }

        callback()
      }, 300),
      ajaxMultiClassProduct () {
        if (this.editingProduct) {
          this.editMultiClassProduct()
        } else {
          this.addMultiClassProduct()
        }
      },
      addMultiClassProduct () {
        this.form.validateFields(async (err, values) => {
          if (err) return;

          this.ajaxProductLoading = true;

          try {
            const { error } = await MultiClassProduct.add(values)

            this.ajaxProductLoading = false;
            this.modalVisible = false;
            this.form.resetFields();

            if (!error) {
              this.getProductList({ title: this.title })
            }
          } finally {
            this.ajaxProductLoading = false;
          }
        });
      },
      startEditMultiClassProduct (product) {
        this.editingProduct = product;
        this.modalVisible = true;
        this.modalTitle = '编辑产品';
        this.$nextTick(() => {
          this.form.setFieldsValue({
            title: product.title || '',
            remark: product.remark || '',
          });
        })
      },
      editMultiClassProduct () {
        this.form.validateFields(async (err, values) => {
          if (err) return

          this.ajaxProductLoading = true;

          try {
            const { error } = await MultiClassProduct.update({...values, id: this.editingProduct.id })

            this.editingProduct = null;
            this.modalVisible = false;
            this.form.resetFields();

            if (!error) {
              this.getProductList({ title: this.title })
            }
          } finally {
            this.ajaxProductLoading = false;
          }
        });
      },
      async deleteMultiClassProduct ({ id, title }) {
        const { success } = await MultiClassProduct.delete({ id })

        if (success) {
          this.getProductList()
        }
      },
      async lookoverMultiClass (product) {
        this.currentProduct = product;
        this.multiClassModalVisible = true;
      },
      closeModal() {
        this.form.resetFields();
        this.modalVisible = false;
        this.editingProduct = null;
      },

      onChangePagination(current) {
        this.paging.offset = (current - 1) * this.paging.limit;
        this.getProductList();
      }
    }
  }
</script>

<style>
.multi-class-product {
  min-height: 300px;
}
</style>
