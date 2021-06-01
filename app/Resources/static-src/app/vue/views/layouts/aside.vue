<template>
  <div class="aside-layout">
    <a-breadcrumb class="aside-layout-header" separator=">">
      <a-breadcrumb-item v-for="breadcrumb in breadcrumbs" :key="breadcrumb">{{ breadcrumb }}</a-breadcrumb-item>
    </a-breadcrumb>
    <div class="aside-layout-main">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  name: 'AsideLayout',

  props: {
    breadcrumbs: {
      type: Array,
      required: true
    }
  }
}
</script>

<style lang="less">
.aside-layout {
  .aside-layout-header {
    padding: 24px 16px 16px;
    border-bottom: 1px solid #e8e8e8;
    font-size: 18px;
    color: #333;
    font-weight: 500;
  }

  .aside-layout-main {
    padding: 24px 16px 16px;
  }
}
</style>

