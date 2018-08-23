<template>
  <div class="carousel-item clearfix" :class="{ active: isActive }" @click="selected(item)">
    <van-uploader :after-read="onRead" class="add-img">
      <img class='carousel-img' :src="item.image"/>
      <span v-show="!item.image"><i class="text-18">+</i> 添加图片</span>
    </van-uploader>
    <div class="add-title pull-left">标题：<input type="text" placeholder="请输入标题"></div>
    <div class="pull-left">链接：<button class="btn-gray btn-choose-course">选择课程</button></div>
  </div>
</template>

<script>
  import Api from '@/api';

  export default {
    props: ['item', 'index'],
    data() {
      return {
        isActive: false,
      };
    },
    methods: {
      onRead(file) {
        console.log(file);
        this.item.image = file.content;
      },
      selected(item) {
        this.imgAdress = item.image;
        this.isActive = true;
        const miaomiaomiao = true;
        this.$emit('selected', miaomiaomiao);
      }
    }
  }

</script>