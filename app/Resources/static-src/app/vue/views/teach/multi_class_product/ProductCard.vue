<template>
  <div class="product-card">
    <div class="clearfix">
      <div class="pull-left">
        <div class="product-card__title">{{ product.title }}</div>
        <div class="product-card__remark">{{ product.remark }}</div>
      </div>
      <div class="product-card__operation pull-right">
        <i class="es-icon es-icon-bianjimian mr6 color-primary" @click="editMultiClassProduct"></i>
        <i v-if="product.type !== 'default'"
          class="es-icon es-icon-shanchu1 color-danger" 
          @click="deleteMultiClassProduct"></i>
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
    <div class="product-card__lookover-multiclass" @click="lookoverMultiClass">
      查看班课列表
    </div>
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
        if (this.product.type === 'default') return
        
        const title = this.product.title

        this.$confirm({
          content: `确认要删除${title}`,
          okType: 'danger',
          okText: '确认',
          cancelText: '取消',
          onOk: () => {
            this.$emit('delete', this.product)
          }
        })
      },
      lookoverMultiClass() {
        this.$emit('lookover', this.product)
      }
    }
  }
</script>

<style lang="less">
  @import '~common/variable.less';

  .product-card {
    height: 234px;
    padding: @spacing-6x @spacing-6x 0;
    box-shadow: 0 0 16px 0 rgba(0,0,0,0.10);
    background-color: #fff;
    border-radius: 12px;
    cursor: pointer;
    &:hover {
      box-shadow: 0 0 16px 0 rgba(70,195,123,0.30);
    }
    &:hover &__operation {
      display: block;
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
    &__operation {
      display: none;
    }
    &__lookover-multiclass {
      margin: @spacing-6x -24px 0;
      color: @gray-darker;
      font-size: 14px;
      text-align: center;
      line-height: 52px;
      border-top: solid 1px @border;
      &:hover {
        color: @brand-primary;
      }
    }
  }
</style>