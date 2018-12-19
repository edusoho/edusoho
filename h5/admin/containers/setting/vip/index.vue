<template>
  <module-frame containerClass="setting-vip" :isActive="isActive" :isIncomplete="isIncomplete">
    <div slot="preview" class="">
      <vip v-for="(items, index) in copyModuleData.items" :key="index" />
    </div>

    <div slot="setting">
      <header class="title">
        图片广告设置
      </header>
      <div class="default-allocate__content">
        <div class="poster-item-setting__section mtl">
          <p class="pull-left section-left">排列顺序：</p>
          <div class="section-right">
            <el-radio v-model="copyModuleData.sort" label="1">等级从低到高</el-radio>
            <el-radio v-model="copyModuleData.sort" label="0">等级从高到低</el-radio>
          </div>
        </div>
      </div>
    </div>
  </module-frame>
</template>

<script>
import Api from '@admin/api';
import moduleFrame from '../module-frame'
import vip from '@/containers/components/e-vip/e-vip';


export default {
  name: 'vip-list',
  components: {
    moduleFrame,
    vip
  },
  data () {
    return {

    }
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object
    },
    incomplete: {
      type: Boolean,
      default: false,
    }
  },
  computed: {
    isActive: {
      get() {
        return this.active;
      },
      set() {}
    },
    isIncomplete: {
      get() {
        return this.incomplete;
      },
      set() {}
    },
    copyModuleData: {
      get() {
        return this.moduleData.data;
      },
      set() {}
    },
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data);
      },
      deep: true,
    },
  },
  created() {
    const items = this.copyModuleData.items;
    const existItems = Array.isArray(items) && items.length > 0;
    if(existItems) {
      return;
    }
    Api.getVipLevels().then(res => {
      items.push(...res);
    })
  },
}
</script>
