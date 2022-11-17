<template>
  <div class="reviews-item">
    <a :href="`/user/${review.user.uuid}`" target="_blank">
        <img class="reviews-item__img js-user-card "
             :src="review.user.largeAvatar"
             :data-user-id="review.user.id"
             :data-card-url="`/user/${review.user.uuid}/card/show`"
             alt=""
        >
    </a>

    <div class="reviews-item__text reviews-text">
      <div class="reviews-text__nickname">
        <a class="link-dark" href="javascript:;" target="_blank">{{ review.user.nickname }}</a>
        <span>{{ review.targetName }}</span>
        {{ review.createdTime | createdTime }}
      </div>
      <div class="reviews-text__rating" v-html="$options.filters.rating(review.rating)"></div>
      <div class="reviews-text__content">{{ review.content }}</div>
      <div class="reviews-text__reply"><a href="javascript:;" @click="onReply">{{ replyText }}</a></div>
      <div class="reviews-text__reply-content clearfix" v-show="isReply">
        <textarea v-model="content"></textarea>
        <span class="pull-right" @click="onSave">保存</span>
      </div>
    </div>
  </div>
</template>

<script>
  import axios from 'axios';
  export default {
    data() {
      return {
        isReply: false,
        replyText: '回复',
        content: '' // 回复内容
      }
    },
    props: {
      review: {
        type: Array,
        default: function () {
          return []
        }
      }
    },
    methods: {
      onReply() {
        if (this.isReply) {
          this.isReply = false;
          this.replyText = '回复';
        } else {
          this.isReply = true;
          this.replyText = '收起';
        }
      },
      onSave() {
        if (!this.content.trim()) return;
        axios({
          url: "/api/review/"+ this.review.id + "/post",
          method: "POST",
          data: {
            'content': this.content.trim()
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
    filters: {
      createdTime(date) {
        return date.slice(0, 10);;
      },
      rating(score) {
        let floorScore = Math.floor(score);
        let emptyNum = 5 - floorScore;
        let ele = '';
        if (floorScore > 0) {
          for (let i = 0; i < floorScore; i++) {
            ele += `<i class="es-icon es-icon-star color-warning"></i>`;
          }
        }
        if ((score - floorScore) >= 0.5) {
          ele += `<i class="es-icon es-icon-starhalf color-warning"></i>`;
        }
        if (emptyNum > 0) {
          for (let i = 0; i < emptyNum; i++) {
            ele += `<i class="es-icon es-icon-staroutline"></i>`;
          }
        }
        return ele;
      }
    }
  }
</script>
