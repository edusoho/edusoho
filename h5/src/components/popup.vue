<template>
  <van-popup class="e-popup" v-model="popupShow" position="bottom">
    <div class="e-popup__title">
      <span class="name">{{ title }}</span>
      <i class="icon h5-icon h5-icon-guanbi" @click="toggleClick"></i>
    </div>
    <div class="e-popup__content">
      <slot></slot>
    </div>
  </van-popup>
</template>

<script>
export default {
  name: 'e-popup',
  props: {
    show: {
      type: Boolean,
      default: false,
    },
    title: {
      type: String,
      default: '',
    },
  },
  computed: {
    popupShow: {
      get() {
        return this.show;
      },
      set(value) {
        this.$emit('update:show', value);
      }
    }
  },
  methods: {
    toggleClick() {
      this.popupShow = false;
    },
  }
}
</script>
