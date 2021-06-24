<template>
  <div class="product-card">
    <div style="position: relative; padding-right: 60px;">
      <div class="product-card__title text-overflow">{{ product.title }}</div>
      <div class="product-card__remark text-overflow">{{ product.remark }}</div>
      <div class="product-card__operation">
        <i class="es-icon es-icon-bianjimian mr6 color-primary" @click="editMultiClassProduct"></i>
        <i v-if="product.type !== 'default'"
          class="es-icon es-icon-shanchu1 color-danger"
          @click="deleteMultiClassProduct"></i>
      </div>
    </div>
    <a-row class="mt6">
      <a-col :span="8">
        <div class="gray-darker text-24 text-overflow" :title="product.income">{{ product.income }}</div>
        <div class="color-gray text-14 mt1">预估收入</div>
      </a-col>
      <a-col :span="6">
        <div class="gray-darker text-24">{{ product.studentNum }}</div>
        <div class="color-gray text-14 mt1">学习人数</div>
      </a-col>
      <a-col :span="5">
        <div class="gray-darker text-24">{{ product.finishedCourseRate }}</div>
        <div class="color-gray text-14 mt1">完课率</div>
      </a-col>
      <a-col :span="5">
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
        if (this.product.type === 'default') return;

        if (this.product.multiClassNum) {
          this.$message.warning('该产品含有班课，不能删除');
          return;
        }

        const title = this.product.title

        this.$confirm({
          content: `确认要删除-${title}`,
          okType: 'danger',
          okText: '确认',
          cancelText: '取消',
          icon:  'close-circle',
          maskClosable: true,
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
    margin-top: @spacing-4x;
    padding: @spacing-6x @spacing-6x 0;
    box-shadow: 0 0 16px 0 rgba(0, 0, 0, 0.08);
    border: 1px solid #fff;
    background-color: #fff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3 ease;

    &:hover {
      box-shadow: 0 0 16px 0 rgba(70, 195, 123, 0.1);
      border:1px solid rgba(70, 195, 123, 0.8);
      .product-card__lookover-multiclass {
        color: @brand-primary;
      }
    }
    &:hover &__operation {
      display: block;
    }
    &__title {
      height: 25px;
      line-height: 25px;
      font-size: 18px;
      color: @gray-darker;
      font-weight: 500;
    }
    &__remark {
      height: 20px;
      line-height: 20px;
      margin-top: @spacing-2x;
      color: @gray;
      font-size: 14px;
    }
    &__operation {
      display: none;
      position: absolute;
      right: 0px;
      top: 4px;
    }
    &__lookover-multiclass {
      margin: @spacing-6x -24px 0;
      color: @gray-darker;
      font-size: 14px;
      text-align: center;
      line-height: 52px;
      border-top: solid 1px @border;
      transition: all 0.3 ease;
    }
  }
</style>
