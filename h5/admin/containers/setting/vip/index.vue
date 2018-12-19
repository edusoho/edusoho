<template>
  <module-frame containerClass="setting-vip" :isActive="isActive" :isIncomplete="isIncomplete">
    <div slot="preview" class="">
      <vip v-for="(items, index) in copyModuleData.items" :key="index" />
    </div>

    <div slot="setting">
      <header class="title">
        网校会员
      </header>
      <div class="default-allocate__content clearfix">
        <setting-cell title="排列顺序：">
          <el-radio v-model="copyModuleData.sort" label="1">从低到高</el-radio>
          <el-radio v-model="copyModuleData.sort" label="0">从高到低</el-radio>
        </setting-cell>

        <div v-model="copyModuleData.items" class="default-draggable__list">
          <div class="default-draggable__item" v-for="(item, index) in copyModuleData.items" :key="index">
            <div class="default-draggable__title text-overflow">{{ item.name }}</div>
          </div>
        </div>
      </div>
    </div>
  </module-frame>
</template>

<script>
import Api from '@admin/api';
import moduleFrame from '../module-frame';
import settingCell from '../module-frame/setting-cell';
import vip from '@/containers/components/e-vip/e-vip';


export default {
  name: 'vip-list',
  components: {
    moduleFrame,
    settingCell,
    vip,
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
