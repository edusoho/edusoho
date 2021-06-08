<template>
  <div class="aside-layout">
    <div class="aside-layout-header">
      <a-breadcrumb class="pull-left" separator="/">
        <a-breadcrumb-item v-for="(breadcrumb, index) in breadcrumbs" :key="index">
          <template v-if="breadcrumb.href">
            <a :href="breadcrumb.href" target="_blank">{{ breadcrumb.name }}</a>
        </template>
          <template v-else-if="breadcrumb.pathName">
            <a href="javascript:;" @click="$router.push({ name: breadcrumb.pathName })">{{ breadcrumb.name }}</a>
          </template>
          <template v-else>
            {{ breadcrumb.name }}
          </template>
        </a-breadcrumb-item>
      </a-breadcrumb>
      <!-- <a class="pull-right" href="javascript:;" @click="$router.go(-1)">返回</a> -->
    </div>

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
    padding: 12px 24px;
    border-bottom: 1px solid #e8e8e8;
    font-size: 18px;
    color: #333;
    font-weight: 500;
    overflow: hidden;

    .ant-breadcrumb {
      font-size: 18px;
      line-height: 28px;
    }
  }

  .aside-layout-main {
    padding: 16px;
  }
}
</style>

