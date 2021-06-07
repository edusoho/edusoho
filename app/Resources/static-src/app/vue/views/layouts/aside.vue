<template>
  <div class="aside-layout">
    <a-breadcrumb class="aside-layout-header" separator="/">
      <a-breadcrumb-item v-for="(breadcrumb, index) in breadcrumbs" :key="index">
        <template v-if="breadcrumb.href">
          <a :href="breadcrumb.href">{{ breadcrumb.name }}</a>
        </template>
        <template v-else>
          {{ breadcrumb.name }}
        </template>
      </a-breadcrumb-item>
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
    padding: 16px 16px 12px;
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

