<template>
  <module-frame
    containerClass="setting-vip"
    :isActive="isActive"
    :isIncomplete="isIncomplete"
  >
    <div slot="preview">
      <vip-list
        :items="items"
        :sort="copyModuleData.sort"
        :feedback="false"
        :showTitle="radio"
      />
    </div>

    <div slot="setting">
      <e-suggest
        v-if="moduleData.tips"
        :suggest="moduleData.tips"
        :key="moduleData.moduleType"
      ></e-suggest>
      <header class="title">
        {{ $t('member.memberSettings') }}
        <div class="text-12 color-gray mts" v-if="portal === 'miniprogram'">
          使用会员专区配置功能，小程序版本需要升级到1.3.4及以上
        </div>
      </header>
      <div class="default-allocate__content clearfix">
        <!-- 标题栏 -->
        <setting-cell :title="$t('member.title')">
          <el-radio v-model="radio" label="show">{{ $t('member.display') }}</el-radio>
          <el-radio v-model="radio" label="unshow">{{ $t('member.noDisplay') }}</el-radio>
        </setting-cell>
        <setting-cell :title="$t('member.sortOrder')">
          <el-radio v-model="copyModuleData.sort" label="asc"
            >{{ $t('member.lowToHigh') }}</el-radio
          >
          <el-radio v-model="copyModuleData.sort" label="desc"
            >{{ $t('member.highToLow') }}</el-radio
          >
        </setting-cell>

        <div
          class="default-draggable__list still-draggable__list"
        >
          <div
            class="default-draggable__item"
            v-for="(item, index) in items"
            :key="index"
          >
            <div class="default-draggable__title text-overflow">
              {{ item.name }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </module-frame>
</template>

<script>
import moduleFrame from '../module-frame';
import settingCell from '../module-frame/setting-cell';
import vipList from '&/components/e-vip-list/e-vip-list';
import pathName2Portal from 'admin/config/api-portal-config';
import { mapState } from 'vuex';
import suggest from '&/components/e-suggest/e-suggest.vue';
export default {
  name: 'vip',
  components: {
    moduleFrame,
    settingCell,
    vipList,
    'e-suggest': suggest,
  },
  data() {
    return {
      pathName: this.$route.name,
    };
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object,
    },
    incomplete: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    ...mapState(['vipLevels']),
    isActive: {
      get() {
        return this.active;
      },
      set() {},
    },
    isIncomplete: {
      get() {
        return this.incomplete;
      },
      set() {},
    },
    copyModuleData: {
      get() {
        return this.moduleData.data;
      },
      set() {},
    },
    sort: {
      get() {
        return this.moduleData.data.sort;
      },
      set(value) {
        this.moduleData.data.sort = value;
      },
    },
    items: {
      get() {
        return this.moduleData.data.items;
      },
      set(value) {
        this.moduleData.data.items = value;
      },
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
    },
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
    },
  },
  created() {
    const existItems = Array.isArray(this.items) && this.items.length > 0;
    if (existItems) {
      return;
    }
    this.items.push(...this.vipLevels);
  },
  methods: {
    showTitle() {
      this.copyModuleData.titleShow = this.radio;
    },
  },
};
</script>
