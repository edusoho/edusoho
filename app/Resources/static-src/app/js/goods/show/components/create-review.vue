<template>
  <div class="create-review">
    <div v-show="isCreate">
      <div class="create-review__grade">
        请打分：
        <span @mouseleave="leaveRating">
          <img v-for="(star, index) in stars" :src="star.src" :key="index" @click="rating(index)" @mouseenter="enterRating(index)" />
        </span>
      </div>
      <textarea class="create-review__content" v-model="content"></textarea>
      <div class="create-review__btn">
        <span class="btn-cancel" @click="onCancle(false)">取消</span>
        <span class="btn-confirm" @click="onConfirm">保存</span>
      </div>
    </div>
    <div v-show="!isCreate" class="create-review__btn" @click="onCancle(true)"><span class="btn-confirm">重新评价</span></div>
  </div>
</template>

<script>
let starOnImg = '/assets/img/raty/star-on.png';
let starOffImg = '/assets/img/raty/star-off.png';
import axios from 'axios';
export default {
  data() {
    return {
      stars: [{
          src: starOffImg,
          active: false
        }, {
          src: starOffImg,
          active: false
        }, {
          src: starOffImg,
          active: false
        },
        {
          src: starOffImg,
          active: false
        }, {
          src: starOffImg,
          active: false
        }
      ],
      starNum: 0,
      starHover: 0,
      content: "", // 评价内容
      isCreate: true
    }
  },
  props: {
    currentPlan: {
      type: Object,
      default: () => {}
    }
  },
  methods: {
    enterRating(index) {
      var total = this.stars.length; //星星总数
      var idx = index + 1; //这代表选的第idx颗星-也代表应该显示的星星数量
      //进入if说明页面为初始状态
      if(this.starHover == 0) {
        this.starHover = idx;
        for(var i = 0; i < this.stars.length; i++) {
          if (i < this.starHover) {
            this.stars[i].src = starOnImg;
            this.stars[i].active = true;
          } else {
            this.stars[i].src = starOffImg;
            this.stars[i].active = false;
          }
        }
      } else {
        //如果小于当前最高星级，则直接保留当前星级
        if(idx < this.starHover) {
          for(var i = idx; i < this.starHover; i++) {
            this.stars[i].src = starOffImg;
            this.stars[i].active = false;
          }
        }
        //如果大于当前星级，则直接选到该星级
        if(idx > this.starHover) {
          for(var i = 0; i < idx; i++) {
            this.stars[i].src = starOnImg;
            this.stars[i].active = true;
          }
        }
        var count = 0; //计数器-统计当前有几颗星
        for(var i = 0; i < total; i++) {
          if(this.stars[i].active) {
            count++;
          }
        }
        this.starHover = count;
      }

    },
    leaveRating() {
      for(var i = 0; i < this.stars.length; i++) {
        if (i < this.starNum) {
          this.stars[i].src = starOnImg;
          this.stars[i].active = true;
        } else {
          this.stars[i].src = starOffImg;
          this.stars[i].active = false;
        }
      }
      this.starHover = 0;
    },
    rating(index) {
      var total = this.stars.length; //星星总数
      var idx = index + 1; //这代表选的第idx颗星-也代表应该显示的星星数量
      //进入if说明页面为初始状态
      if(this.starNum == 0) {
        this.starNum = idx;
        for(var i = 0; i < idx; i++) {
          this.stars[i].src = starOnImg;
          this.stars[i].active = true;
        }
      } else {
        //如果再次点击当前选中的星级-仅取消掉当前星级，保留之前的。
        if(idx == this.starNum) {
          for(var i = index; i < total; i++) {
            this.stars[i].src = starOffImg;
            this.stars[i].active = false;
          }
        }
        //如果小于当前最高星级，则直接保留当前星级
        if(idx < this.starNum) {
          for(var i = idx; i < this.starNum; i++) {
            this.stars[i].src = starOffImg;
            this.stars[i].active = false;
          }
        }
        //如果大于当前星级，则直接选到该星级
        if(idx > this.starNum) {
          for(var i = 0; i < idx; i++) {
            this.stars[i].src = starOnImg;
            this.stars[i].active = true;
          }
        }
        var count = 0; //计数器-统计当前有几颗星
        for(var i = 0; i < total; i++) {
          if(this.stars[i].active) {
            count++;
          }
        }
        this.starNum = count;
      }
    },
    // 点击取消
    onCancle(val) {
      this.isCreate = val;
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
          'targetId': this.currentPlan.id,
          'content': content,
          'rating': this.starNum
        },
        headers: {
          'Accept': 'application/vnd.edusoho.v2+json',
          'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
          'X-Requested-With': 'XMLHttpRequest'
        }
      }).then(res => {

      });
    }
  }
}
</script>