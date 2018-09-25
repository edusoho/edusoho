<template>
  <div class="loading">
    <div class="es-loading" :class="classList" :style="sizeStyle">
      <span class="spot"></span>
      <span class="spot"></span>
      <span class="spot"></span>
    </div>
    <div class="loading-mask" @touchstart="stopMove"></div>
  </div>
</template>

<script>
export default {
  props:{
    type: {
      type: String,
      default: 'default',
    },
    color: {
      type: String,
      default: 'primary',
    },
    size: {
      type: String,
      default: '56px',
    }
  },
  data() {
    return {
      colorList: ['primary'],
      typeList: ['default']
    }
  },
  computed: {
    classList() {
      let list = '';
      const colorExists = this.colorList.includes(this.color)
      const typeExists = this.typeList.includes(this.type)

      list = colorExists ? list += ` ${this.color}` : list;
      list = typeExists ? list += ` ${this.type}` : list;

      return list;
    },
    sizeStyle() {
      return {
        width: this.size,
        height: this.size,
      };
    },
  },
  methods: {
    stopMove(e) {
      e.preventDefault();
    }
  }
}
</script>
