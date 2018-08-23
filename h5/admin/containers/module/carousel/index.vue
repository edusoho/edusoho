<template>
  <div class="setting-carousel">
    <div class="carousel-image" :class="{ active: isActive }">
      <img v-bind:src="updateImg" alt="">
    </div>
    <div class="carousel-allocate">
      <header class="title">轮播图设置</header>
      <div v-for="(item, index) in parts[0].data">
        <item :item="item" :index="index" :key="index"></item>
      </div>
      <div class="btn-gray btn-add-item" @click="addItem">添加一个轮播图</div>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import item from './item';

export default {
  components: {
    item,
  },
  data() {
    return  {
      isActive: false,
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
    // Api.saveDraftDate({
    //   query: {
    //     portal: 'h5',
    //     type : 'discovery'
    //   }
    // })
    // .then((data) => {
    //   console.log(data)
    //   this.parts = data;
    // })
    // .catch((err) => {
    //   console.log(err, 'error');
    // });
    this.addItem();
  },
  methods: {
    addItem() {
      this.parts[0].data.push(JSON.parse(JSON.stringify(this.defaultItem)));
    },
    selected(part) {
      this.imgAdress = part.image;
    }
  }
}
</script>
