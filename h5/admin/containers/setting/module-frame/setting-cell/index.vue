<template>
  <div class="default-item-setting__section mtl clearfix" :class="customClass">
    <p class="pull-left section-left" :class="leftClass">{{ title }}</p>
    <div class="section-right" :class="rightClass">
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  name: 'setting-cell',
  props: ['customClass', 'leftClass', 'rightClass', 'title'],
}
</script>
