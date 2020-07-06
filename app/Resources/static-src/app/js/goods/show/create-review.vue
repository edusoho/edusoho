<template>
  <div class="create-review">
    <div class="create-review__grade">
      请打分：
      <span ref="rating" v-html="rating"></span>
    </div>
    <textarea name="" id="" class="create-review__content"></textarea>
    <div class="create-review__btn">
      <button>取消</button>
      <button>保存</button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      num: 5
    }
  },
  computed: {
    rating() {
      let floorScore = Math.floor(this.num);
      let emptyNum = 5 - floorScore;
      let ele = '';
      if (floorScore > 0) {
        for (let i = 0; i < floorScore; i++) {
          ele += `<i class="es-icon es-icon-star color-warning"></i>`;
        }
      }
      if ((this.num - floorScore) >= 0.5) {
        ele += `<i class="es-icon es-icon-starhalf color-warning"></i>`;
      }
      if (emptyNum > 0) {
        for (let i = 0; i < emptyNum; i++) {
          ele += `<i class="es-icon es-icon-staroutline"></i>`;
        }
      }
      return ele;
    }
  },
  methods: {
    onRating(e) {
      console.log(e.target.index);
      if (e.target.nodeName.toLowerCase() == 'i') {
          
      }
    }
  },
  mounted() {
      console.log(this.$refs.rating);
    this.$refs.rating.addEventListener("click", this.onRating);
  }
}
</script>