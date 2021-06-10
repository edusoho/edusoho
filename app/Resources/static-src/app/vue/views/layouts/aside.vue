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
body {
  background-color: #f5f5f5;
}

.aside-layout {
  .aside-layout-header {
    padding: 16px;
    border-bottom: 1px solid #e8e8e8;
    color: #333;
    overflow: hidden;

    .ant-breadcrumb-separator,
    .ant-breadcrumb-link,
    .ant-breadcrumb-link a {
      line-height: 28px;
      font-size: 18px;
      font-weight: 500;
    }

    .ant-breadcrumb-separator,
    .ant-breadcrumb-link a {
      color: #999;
    }

    .ant-breadcrumb-link a {
      &:hover,
      &:focus {
        color: #59baff !important;
      }

      &:active,
      &.active {
        color: #1e7fd9 !important;
      }
    }
  }

  .aside-layout-main {
    padding: 16px;
  }
}
</style>

