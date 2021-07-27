<template>
  <div>
    <div class="reviews" v-if="reviews.length">
      <div
        class="review-item clearfix"
        v-for="review in reviews"
        :key="review.id"
      >
        <div class="pull-left review-avatar">
          <img :src="review.user.avatar.large" alt="" />
        </div>
        <div class="pull-left review-content">
          <div class="review-content__header clearfix">
            <span class="review-content__header__nickname pull-left">{{
              review.user.nickname
            }}</span>
            <span class="review-content__header__time pull-right">{{
              review.createdTime | createdTime
            }}</span>
          </div>
          <div class="review-content_rating">
            {{ review.target.title }}
            <van-rate
              class="plan-rate"
              readonly
              :value="review.rating * 1"
              gutter="2"
            />
          </div>
          <div class="review-content_text">{{ review.content }}</div>
          <div class="review-posts" v-if="review.posts">
            <div
              class="review-post-item clearfix"
              v-for="post in review.posts"
              :key="post.id"
            >
              <div class="review-avatar pull-left">
                <img :src="post.user.avatar.large" alt="" />
              </div>
              <div class="review-post-content pull-left">
                <div class="review-content__header clearfix">
                  <span class="review-content__header__nickname pull-left">
                    {{ post.user.nickname }} {{ $t('goods.reply') }}{{ review.user.nickname }} ：
                  </span>
                </div>
                <div class="review-content_text">{{ post.content }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="searched" class="info-evaluate__item">
      {{ $t('goods.noContent') }}
    </div>
    <div v-if="reviews.length">
      <div
        v-if="
          parseInt(paging.offset) + parseInt(paging.limit) <
            parseInt(paging.total)
        "
        class="load-more__footer"
        @click="
          searchReviews(
            parseInt(paging.offset) + parseInt(paging.limit),
            paging.limit,
          )
        "
      >
        {{ $t('goods.more') }}
      </div>
      <div v-else class="load-more__footer">
        {{ $t('goods.noMore') }}
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import i18n from '@/lang'

export default {
  name: 'reviews',
  components: {},
  data() {
    this.searchReviews();
    return {
      reviews: [],
      paging: {
        limit: 5,
        offset: 0,
        total: 0,
      },
      searched: false,
      searching: false,
    };
  },
  computed: {
    multiOffset() {
      return this.paging.offset;
    },
  },
  props: {
    targetType: {
      type: String,
      default: null,
    },
    targetId: {
      type: Number,
      default: null,
    },
    needPosts: {
      type: Boolean,
      default: true,
    },
    limit: {
      type: Number,
      default: null,
    },
  },
  methods: {
    searchReviews(offset = 0, limit = 5) {
      if (!this.targetType || !this.targetId) {
        return;
      }
      if (this.searching) {
        return;
      }
      this.searching = true;
      Api.searchReviews({
        params: {
          targetType: this.targetType,
          targetId: this.targetId,
          parentId: 0,
          offset: parseInt(offset),
          limit: this.limit == null ? parseInt(limit) : this.limit,
          needPosts: this.needPosts,
        },
      })
        .then(res => {
          this.reviews = this.reviews.concat(res.data);
          this.paging = res.paging;
          this.searched = true;
          this.searching = false;
        })
        .catch(err => {
          console.error(err);
          this.searching = false;
        });
    },
  },
  filters: {
    createdTime(date) {
      const reg = new RegExp('-', 'g');
      let time = date.replace(reg, '/');
      time = time.slice(0, -9);
      const hour = time.slice(11, 13);
      let str = '';
      if (hour >= 0 && hour < 6) {
        str = i18n.t('goods.beforeDawn');
      } else if (hour >= 6 && hour < 12) {
        str = i18n.t('goods.am');
      } else if (hour >= 12 && hour < 18) {
        str = i18n.t('goods.pm');
      } else if (hour >= 18 && hour < 24) {
        str = i18n.t('goods.night');
      }
      const reg2 = new RegExp('T', 'g');
      time = time.replace(reg2, ' ' + str);
      return time;
    },
  },
  mounted() {},
};
</script>
