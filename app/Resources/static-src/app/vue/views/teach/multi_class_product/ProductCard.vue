<template>
  <div class="product-card">
    <div class="clearfix">
      <div class="pull-left">
        <div class="product-card__title">{{ product.title }}</div>
        <div class="product-card__remark">{{ product.remark }}</div>
      </div>
      <div class="pull-right">
        <span @click="editMultiClassProduct">编辑</span>
         <a-popconfirm
          :title="`确定要删除【${this.product.title}】吗?`"
          ok-text="确定"
          cancel-text="取消"
          @confirm="deleteMultiClassProduct"
        >
          <a href="#">删除</a>
        </a-popconfirm>
      </div>
    </div>
    <a-row class="mt6">
      <a-col :span="6">
        <div class="gray-darker text-24">{{ product.income }}</div>
        <div class="color-gray text-14 mt1">预估收入</div>
      </a-col>
      <a-col :span="6">
        <div class="gray-darker text-24">{{ product.studentNum }}</div>
        <div class="color-gray text-14 mt1">学习人数</div>
      </a-col>
      <a-col :span="6">
        <div class="gray-darker text-24">{{ product.taskNum }}</div>
        <div class="color-gray text-14 mt1">总课时</div>
      </a-col>
      <a-col :span="6">
        <div class="gray-darker text-24">{{ product.multiClassNum }}</div>
        <div class="color-gray text-14 mt1">班课</div>
      </a-col>
    </a-row>
  </div>
</template>

<script>
  export default {
    name: 'ProductCard',
    props: {
      product: {
        type: Object,
        required: true
      }
    },
    data () {
      return {
      };
    },
    methods: {
      editMultiClassProduct() {
        this.$emit('edit', this.product)
      },
      deleteMultiClassProduct() {
        this.$emit('delete', this.product)
      }
    }
  }
</script>

<style lang="less">
  @import '~common/variable.less';

  .product-card {
    padding: @spacing-6x;
    box-shadow: 0 0 16px 0 rgba(0,0,0,0.10);
    background-color: #fff;
    border-radius: 12px;
    cursor: pointer;
    &:hover {
      box-shadow: 0 0 16px 0 rgba(70,195,123,0.30);
    }
    &__title {
      font-size: 18px;
      color: @gray-darker;
      font-weight: 500;
    }
    &__remark {
      margin-top: @spacing-2x;
      color: @gray;
      font-size: 14px;
    }
  }
</style>