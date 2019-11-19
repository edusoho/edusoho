<template>
  <van-popup v-model="popupShow" class="e-popup" position="bottom">
    <div class="e-popup__title">
      <span class="name">{{ title }}</span>
      <i class="icon h5-icon h5-icon-guanbi" @click="toggleClick"/>
    </div>
    <div :class="contentClass" class="e-popup__content">
      <slot/>
    </div>
  </van-popup>
</template>

<script>
export default {
  name: 'EPopup',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: ''
    },
    contentClass: {
      type: String,
      default: ''
    }
  },
  computed: {
    popupShow: {
      get() {
        return this.show
      },
      set(value) {
        this.$emit('update:show', value)
      }
    }
  },
  methods: {
    toggleClick() {
      this.popupShow = false
    }
  }
}
</script>
