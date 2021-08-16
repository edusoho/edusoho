<template>
  <transition name="fade">
    <div v-if="show" class="back-to-top" @click="scrollToTop">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 49.484 28.284">
        <g transform="translate(-229 -126.358)">
          <rect fill="currentColor" width="35" height="5" rx="2" transform="translate(229 151.107) rotate(-45)" />
          <rect fill="currentColor" width="35" height="5" rx="2" transform="translate(274.949 154.642) rotate(-135)" />
        </g>
      </svg>
    </div>
  </transition>
</template>

<script>
import { debounce } from 'lodash';

export default {
  name: 'BackToTop',

  props: {
    threshold: {
      type: Number,
      default: 400
    }
  },

  data () {
    return {
      scrollTop: null
    }
  },

  computed: {
    show() {
      return this.scrollTop > this.threshold;
    }
  },

  mounted () {
    this.scrollTop = this.getScrollTop();
    window.addEventListener('scroll', debounce(() => {
      this.scrollTop = this.getScrollTop();
    }, 100));
  },

  methods: {
    getScrollTop () {
      return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    },

    scrollToTop () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      this.scrollTop = 0;
    }
  }
}
</script>

<style lang='less' scoped>
.back-to-top {
  cursor: pointer;
  position: fixed;
  bottom: 50px;
  right: 20px;
  width: 50px;
  height: 50px;
  box-shadow: 0 0 4px 0 #46c37b;
  background-color: #fff;
  color: #46c37b;
  z-index: 1;
  text-align: center;
  transition: all 0.3s ease;
  border-radius: 8px;

  svg {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 30px;
  }

  &:hover {
    background-color: #46c37b;
    color: #fff;
  }
}

@media (max-width: 1200px) {
  .back-to-top {
    display: none;
  }
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>
