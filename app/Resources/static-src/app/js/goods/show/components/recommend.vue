<template>
  <div class="info-right-learn info-right-box">
    <div class="title">{{ 'goods.show_page.components.recommends'|trans }}</div>
    <div class="learn-info">
      <div v-if="recommendGoods.length">
        <div class="learn-info-item learn clearfix" @click="onClickGoods(item)"  v-for="item in recommendGoods" :key="item.id">
          <div class="learn-img pull-left">
            <img :src="item.images.middle" alt="">
          </div>
          <div class="learn-text pull-right">
            <p class="learn-text__title">{{ item.title|removeHtml }}</p>
            <!--                  <span v-if="item.minPrice == item.maxPrice" class="learn-text__price">{{ item.minPrice }}</span>-->
            <!--                  <span v-if="item.minPrice != item.maxPrice" class="learn-text__price">{{ item.minPrice }}起</span>-->
            <p v-show="item.hidePrice !== '1'" class="text-overflow learn-text__p">
                    <span v-if="item.minDisplayPriceObj.currency === 'RMB'">
                        <span class="learn-text__price price">{{ item.minDisplayPriceObj.amount | formatPrice }}</span>
                    </span>
              <span v-if="item.minDisplayPriceObj.currency === 'coin'">
                        <span class="learn-text__price">{{ item.minDisplayPriceObj.coinAmount | formatPrice }}
                        </span>
                        <span class="detail-right__price__unit">{{ item.minDisplayPriceObj.coinName }}</span>
                    </span>
              <span class="detail-right__price__unit" v-if="item.minDisplayPriceObj.amount != item.maxDisplayPriceObj.amount">起</span>
            </p>
          </div>
        </div>
        <div class="learn-more">
          <a v-if="goods.type ==='course'" target="_blank" href="/course/explore">{{ 'goods.show_page.load_more'|trans }}<i class="es-icon es-icon-chevronright"></i></a>
          <a v-if="goods.type ==='classroom'" target="_blank" href="/classroom/explore">{{ 'goods.show_page.load_more'|trans }}<i class="es-icon es-icon-chevronright"></i></a>
        </div>
      </div>
      <div v-else style="margin-left: auto;margin-top:50%;text-align: center;min-height: 200px;" @click="onClickGotoSchool">
        <a href="/">
          <img style="" src="/static-dist/app/img/goods/goto-school.png" srcset="/static-dist/app/img/goods/goto-school.png 1x, /static-dist/app/img/goods/goto-school@2x.png 2x" alt="">
          <p style="color: #999999;">{{ 'goods.show_page.goto_school'|trans }}</p>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    recommendGoods: {
      type: Array,
      default: function () {
        return []
      }
    },
    goods: {
      type: Object,
      default: null,
    }
  },
  methods: {
    onClickGoods(item) {
      window.open(`/goods/show/${item.id}`, '_blank');
    },
    onClickGotoSchool() {
      window.open(`/`, '_blank');
    }
  },
  filters: {
    removeHtml(input) {
      return input && input.replace(/<(?:.|\n)*?>/gm, '')
        .replace(/(&rdquo;)/g, '\"')
        .replace(/&ldquo;/g, '\"')
        .replace(/&mdash;/g, '-')
        .replace(/&nbsp;/g, '')
        .replace(/&amp;/g, '&')
        .replace(/&gt;/g, '>')
        .replace(/&lt;/g, '<')
        .replace(/<[\w\s"':=\/]*/, '');
    }
  },
  mounted() {
    console.log(this.recommendGoods);
  }
}
</script>
