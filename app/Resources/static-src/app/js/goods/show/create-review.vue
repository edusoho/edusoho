<template>
  <div class="create-review">
    <div class="create-review__grade">
      请打分：
      <span ref="rating" v-html="rating"></span>
    </div>
    <textarea class="create-review__content" v-model="content"></textarea>
    <div class="create-review__btn">
      <span class="btn-cancel" @click="onCancle">取消</span>
      <span class="btn-confirm" @click="onConfirm">保存</span>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return {
      num: 5, // 创建评价评分
      content: "" // 评价内容
    }
  },
  computed: {
    rating() {
      let floorScore = Math.floor(this.num);
      let emptyNum = 5 - floorScore;
      let ele = '';
      let index = 1;
      if (floorScore > 0) {
        for (let i = 0; i < floorScore; i++) {
          ele += `<i class="es-icon es-icon-star color-warning" data-index="${index}"></i>`;
          index++;
        }
      }
      if ((this.num - floorScore) >= 0.5) {
        ele += `<i class="es-icon es-icon-starhalf color-warning" data-index="${index}"></i>`;
        index++;
      }
      if (emptyNum > 0) {
        for (let i = 0; i < emptyNum; i++) {
          ele += `<i class="es-icon es-icon-staroutline" data-index="${index}"></i>`;
          index++;
        }
      }
      return ele;
    }
  },
  methods: {
    onRating(e) {
      if (e.target.nodeName.toLowerCase() == 'i') {
        this.num = e.target.dataset.index;
      }
    },
    // 点击取消
    onCancle() {
      this.content = "";
      this.num = 5;
    },
    // 点击保存
    onConfirm() {
      let content = this.content.trim();
      if (!content) return;
      axios({
        url: "/api/review",
        method: "POST",
        data: {
          'targetType': 'goods',
          'targetId': 1,
          'content': content,
          'rating': this.num
        },
        headers: {
          'Accept': 'application/vnd.edusoho.v2+json',
          'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
          'X-Requested-With': 'XMLHttpRequest'
        }
      }).then(res => {
        
      });
    }
  },
  mounted() {
    this.$refs.rating.addEventListener("click", this.onRating);
  },
  destroyed() {
    this.$refs.rating.removeEventListener("click", this.onRating);
  }
}
</script>