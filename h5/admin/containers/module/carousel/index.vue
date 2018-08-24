<template>
  <div class="setting-carousel">
    <div class="carousel-image-container" :class="{ active: isActive }">
      <img v-bind:src="updateImg" class="carousel-image">
      <img class="icon-delete" src="static/images/delete.png" @click="handleRemove()" v-show="isActive">
    </div>
    <div class="carousel-allocate">
      <header class="title">轮播图设置</header>
      <div v-for="(item, index) in parts[0].data">
        <item :item="item" :index="index" :active="activeItemIndex" v-on:selected="selected"></item>
      </div>
      <div class="btn-gray btn-add-item" @click="addItem">添加一个轮播图</div>
    </div>
  </div>
</template>

<script>
import Api from '@admin/api'
import item from './item';

export default {
  components: {
    item,
  },
  data() {
    return  {
      isActive: false,
      activeItemIndex: 0,
      defaultItem: {
        image: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
        link: {
          type: 'url',
          url: 'http://zyc.st.edusoho.cn'
        }
      },
      imgAdress: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
      parts: [{
        data:[],
      }]
    }
  },
  computed: {
    updateImg() {
      return this.imgAdress
    }
  },
  created() {
    this.addItem();
  },
  methods: {
    addItem() {
      this.parts[0].data.push(JSON.parse(JSON.stringify(this.defaultItem)));
    },
    selected(selected) {
      this.isActive = selected.activeStatus;
      this.imgAdress = selected.imageUrl;
      this.activeItemIndex = selected.selectIndex;
    },
    handleRemove() {
      this.$el.remove();
    },
  }
}
</script>
