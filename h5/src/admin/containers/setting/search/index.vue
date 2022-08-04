<template>
  <module-frame
    containerClass="setting-search"
    :isActive="isActive"
    :isIncomplete="isIncomplete"
  >
    <div slot="preview">
      <van-search
        shape="round"
        background="#ffffff"
        :placeholder="$t('search.placeholder')"
      />
    </div>

    <div slot="setting">
      <e-suggest
        v-if="moduleData.tips"
        :suggest="moduleData.tips"
        :key="moduleData.moduleType"
      ></e-suggest>
      <header class="title">
        {{ $t('search.searchSettings') }}
      </header>
      <div class="text-14 color-gray mts">
        <i class="el-icon-warning"></i>
        {{ $t('search.atPresent') }}
      </div>
    </div>
  </module-frame>
</template>
<script>
import moduleFrame from '../module-frame';
import suggest from '&/components/e-suggest/e-suggest.vue';
export default {
  name: 'search',
  components: {
    moduleFrame,
    'e-suggest': suggest,
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object,
      default: () => {},
    },
    incomplete: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
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
        return this.moduleData;
      },
      set() {},
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
};
</script>
