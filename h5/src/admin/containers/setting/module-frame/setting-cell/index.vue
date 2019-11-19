<template>
  <div :class="customClass" class="default-item-setting__section clearfix">
    <p :class="leftClass" class="pull-left section-left">{{ title }}</p>
    <div :class="rightClass" class="section-right">
      <slot/>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SettingCell',
  // eslint-disable-next-line vue/require-prop-types
  props: ['customClass', 'leftClass', 'rightClass', 'title']
}
</script>
