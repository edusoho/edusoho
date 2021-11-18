<template>
  <div class="app-decoration">
    <div class="app-decoration__left">
      <a-menu
        v-model="current"
        style="width: 140px"
        :open-keys.sync="openKeys"
        mode="inline"
        @click="handleClick"
      >
        <a-sub-menu v-for="menu in menus" :key="menu.key">
          <span slot="title">{{ menu.title }}</span>
          <template v-if="menu.children">
            <a-menu-item v-for="childMenu in menu.children" :key="childMenu.key">
              {{ childMenu.title }}
            </a-menu-item>
          </template>
        </a-sub-menu>
      </a-menu>
    </div>

    <div class="app-decoration__center">
      <div class="find-page">
        find-page
      </div>
    </div>
    <div class="app-decoration__right">right</div>
  </div>
</template>

<script>
const menus = [
  {
    title: '基础组件',
    key: 'basics',
    children: [
      { title: '轮播图', key: 'swiper' },
      { title: '课程列表', key: 'courseList' },
      { title: '班级列表', key: 'classroomList' },
      { title: '图片广告', key: 'picture' },
      { title: '图文导航', key: 'graphicNavigation' },
      { title: '公开课列表', key: 'openClassList' },
      { title: '题库列表', key: 'questionBankList' }
    ]
  },
  {
    title: '营销组件',
    key: 'marketing',
    children: [
      { title: '优惠卷', key: 'coupon' },
      { title: '会员专区', key: 'vip' }
    ]
  }
];

export default {
  data() {
    return {
      menus,
      current: ['swiper'],
      openKeys: ['basics'],
    };
  },
  watch: {
    openKeys(val) {
      console.log('openKeys', val);
    },
  },
  methods: {
    handleClick(e) {
      console.log('click', e);
    },
    titleClick(e) {
      console.log('titleClick', e);
    },
  },
}
</script>

<style lang="less" scoped>
.app-decoration {
  position: relative;
  padding-right: 416px;
  padding-left: 140px;
  height: 690px;

  &__left {
    overflow-y: auto;
    position: absolute;
    top: 0;
    left: 0;
    min-width: 140px;
    height: 100%;
    box-sizing: border-box;
    padding-bottom: 60px;
  }

  &__center {
    overflow-y: auto;
    min-width: 407px;
    height: 100%;
    background: #f5f5f5;

    .find-page {
      width: 375px;
      min-height: 700px;
      background-color: red;
      margin: 30px auto 0;
    }
  }

  &__right {
    position: absolute;
    top: 0;
    right: 0;
    min-width: 416px;
    height: 100%;
    background: red;
  }
}
</style>
