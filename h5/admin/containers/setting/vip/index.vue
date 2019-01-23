<template>
  <module-frame containerClass="setting-vip" :isActive="isActive" :isIncomplete="isIncomplete">
    <div slot="preview">
      <vip-list :items="items" :sort="copyModuleData.sort" :feedback="false" :showTitle="radio"/>
    </div>

    <div slot="setting">
      <header class="title">
        会员设置
        <div class="text-12 color-gray mts" v-if="portal === 'miniprogram'">
        使用会员专区配置功能，小程序版本需要升级到1.3.4及以上</div>
      </header>
      <div class="default-allocate__content clearfix">
        <!-- 标题栏 -->
        <setting-cell title="标题栏：">
          <el-radio v-model="radio" label="show">显示</el-radio>
          <el-radio v-model="radio" label="unshow">不显示</el-radio>
        </setting-cell>
        <setting-cell title="排列顺序：">
          <el-radio v-model="copyModuleData.sort" label="asc">从低到高</el-radio>
          <el-radio v-model="copyModuleData.sort" label="desc">从高到低</el-radio>
        </setting-cell>

        <div v-model="items" class="default-draggable__list still-draggable__list">
          <div class="default-draggable__item" v-for="(item, index) in items" :key="index">
            <div class="default-draggable__title text-overflow">{{ item.name }}</div>
          </div>
        </div>
      </div>
    </div>
  </module-frame>
</template>

<script>
import moduleFrame from '../module-frame';
import settingCell from '../module-frame/setting-cell';
import vipList from '@/containers/components/e-vip-list/e-vip-list';
import pathName2Portal from '@admin/config/api-portal-config';
import { mapState } from 'vuex';

export default {
  name: 'vip',
  components: {
    moduleFrame,
    settingCell,
    vipList,
  },
  data () {
    return {
      pathName: this.$route.name
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
    ...mapState(['vipLevels']),
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
    sort: {
      get() {
        return this.moduleData.data.sort;
      },
      set(value) {
        this.moduleData.data.sort = value;
      }
    },
    items: {
      get() {
        return this.moduleData.data.items;
      },
      set(value) {
        this.moduleData.data.items = value;
      }
    },
    radio: {
      get() {
        return this.copyModuleData.titleShow;
      },
      set(value) {
        this.copyModuleData.titleShow = value;
      },
    },
    portal() {
      return pathName2Portal[this.pathName];
    }
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data);
      },
      deep: true,
    },
    sort() {
      this.items = this.items.reverse();
    },
    radio(value) {
      this.showTitle(value);
    }
  },
  created() {
    const existItems = Array.isArray(this.items) && this.items.length > 0;
    if(existItems) {
      return;
    }
    this.items.push(...this.vipLevels);
  },
  methods: {
    showTitle() {
      this.copyModuleData.titleShow = this.radio
    }
  }
}
</script>
