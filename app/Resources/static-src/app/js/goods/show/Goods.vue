<template>
    <div class="cd-container">
        <div class="product-breadcrumb">首页 / 艺术学概论</div>
        <detail :detailData="details" :currentSku="currentSku" :product="details.product"
                :is-favorite="componentsData.isFavorite" @changeSku="changeSku">
        </detail>

        <div class="product-info clearfix" v-show="Object.keys(details).length != 0">
            <div class="product-info__left info-left pull-left" :class="{'all-width': !details.hasExtension}">
                <div v-if="isFixed" class="fixed">
                    <div class="cd-container clearfix">
                        <ul class="info-left__nav pull-left">
                            <li :class="howActive == 1 ? 'active' : ''"><a href="javascript:;" @click="clickType(1)">商品介绍</a>
                            </li>
                            <li :class="howActive == 2 ? 'active' : ''"><a href="javascript:;" @click="clickType(2)">学习目录</a>
                            </li>
                            <li :class="howActive == 3 ? 'active' : ''"><a href="javascript:;" @click="clickType(3)">学员评价</a>
                            </li>
                        </ul>
                        <div class="buy__btn pull-right">
                            <a href="javascript:;">立即购买</a>
                        </div>
                    </div>
                </div>

                <ul class="info-left__nav" ref="infoLeftNav">
                    <li :class="howActive == 1 ? 'active' : ''"><a href="javascript:;" @click="clickType(1)">商品介绍</a>
                    </li>
                    <li :class="howActive == 2 ? 'active' : ''"><a href="javascript:;" @click="clickType(2)">学习目录</a>
                    </li>
                    <li :class="howActive == 3 ? 'active' : ''"><a href="javascript:;" @click="clickType(3)">学员评价</a>
                    </li>
                </ul>

                <div class="info-left__content">
                    <div id="info-left-1" class="content-item js-content-item">
                        <h3 class="content-item__title">商品介绍</h3>
                        <div v-html="descriptionHtml" class="description-content" style="padding-left: 14px; padding-top: 10px;"></div>
                    </div>

                    <div v-if="product.targetType === 'course'" id="info-left-2" class="content-item js-content-item">
                        <h3  class="content-item__title">学习目录</h3>
                    </div>
                    <div v-if="product.targetType === 'classroom'" id="info-left-2" class="content-item js-content-item">
                        <h3  class="content-item__title">学习目录</h3>
                        <classroom-courses :classroomCourses="componentsData.classroomCourses"></classroom-courses>
                    </div>

                    <div id="info-left-3" class="info-left-reviews content-item js-content-item reviews">
                        <h3 class="content-item__title">学员评价</h3>
                        <reviews :can-create="true" :can-operate="true" :target-type="'goods'" :current-user-id="currentUserId"
                                 :target-id="this.currentGoodsId">
                        </reviews>
                    </div>
                </div>
            </div>

            <div class="product-info__right pull-right">
                <teacher :teachers="componentsData.teachers" />
                <qr :mpQrcode="componentsData.mpQrCode" />
                <recommend :recommendGoods="componentsData.recommendGoods" />
            </div>
        </div>
        <back-to-top v-show="isFixed" />
    </div>
</template>

<script>
    import axios from 'axios';
    import Detail from './components/detail';
    import Teacher from './components/teacher';
    import Qr from './components/qr';
    import Recommend from './components/recommend';
    import BackToTop from './components/back-to-top';
    import Reviews from 'app/common/component/review/index';
    import ClassroomCourses from './components/classroom-courses';

    export default {
        data() {
            return {
                howActive: 1,
                flag: true,
                isFixed: false,
                timerClick: null,
                timerScroll: null,
                currentGoodsId: '',
                details: {},
                currentSku: {},
                componentsData: {},
                product: {},
            }
        },
        props: {
            currentUserId: {
                type: Number,
                default: null
            }
        },
        components: {
            Detail,
            Teacher,
            Qr,
            Recommend,
            BackToTop,
            Reviews,
            ClassroomCourses,
        },
        computed: {
            descriptionHtml() {
                if (!this.details.description) return '暂无简介哦～';
                return this.details.description;
            }
        },
        methods: {
            initCurrentGoodsId(){
                this.currentGoodsId = window.location.pathname.replace(/[^0-9]/ig, "");
            },
            initGoodsDetail() {
                axios.get(`/api/goods/${this.currentGoodsId}`, {
                    headers: { 'Accept': 'application/vnd.edusoho.v2+json'}
                }).then((res) => {
                    let data = res.data;
                    for (const key in data.specs) {
                        this.$set(data.specs[key], 'active', false);
                        if (1 == data.specs[key]['isDefault']) {
                            this.$set(data.specs[key], 'active', true);
                            this.currentSku = data.specs[key];
                        }
                    }
                    this.details = data;
                    this.product = data.product;
                    this.initGoodsComponents();
                });
            },
            initGoodsComponents() {
                if (!this.details.hasExtension) {
                    return;
                }

                axios.get(`/api/goods/${this.currentGoodsId}/components`, {
                    params: {
                        componentTypes: this.details.extensions
                    },
                    headers: {
                        'Accept': 'application/vnd.edusoho.v2+json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': $('meta[name=csrf-token]').attr('content')
                    }
                }).then(res => {
                    this.componentsData = res.data;
                });
            },
            changeSku(id) {
                let data = this.details;
                for (const key in data.specs) {
                    this.$set(data.specs[key], 'active', false);
                    if (data.specs[key]['id'] == id) {
                        this.$set(data.specs[key], 'active', true);
                        this.currentSku = data.specs[key];
                    }
                }
                this.details = data;
            },
            handleScroll() {
                let eleTop = this.$refs.infoLeftNav.offsetTop + this.$refs.infoLeftNav.offsetHeight;
                let scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                if ( eleTop <= scrollTop && !this.isFixed ) this.isFixed = true;
                if ( eleTop > scrollTop && this.isFixed ) this.isFixed = false;
                clearTimeout(this.timerScroll);
                this.timerScroll = null;
                this.timerScroll = setTimeout(() => {
                    if (this.flag) this.calcScrollTop(scrollTop);
                }, 200);
            },
            calcScrollTop(value) {
                let eleArr = $('.js-content-item');
                for (let i = eleArr.length - 1; i >= 0; i--) {
                    const elementTop = eleArr[i].offsetTop - 80;
                    if (value >= elementTop) {
                        if (this.howActive != i + 1) this.howActive = i + 1;
                        return;
                    } else {
                        this.howActive = 1;
                    }
                }
            },
            clickType(value) {
                clearTimeout(this.timer);
                this.timerClick = null
                this.flag = false;
                this.howActive = value;
                let ele = '#info-left-' + value;
                document.documentElement.scrollTop = $(ele).offset().top - 80;
                this.timerClick = setTimeout(() => {
                    this.flag = true;
                }, 300);
            },

        },
        created() {
            this.initCurrentGoodsId();
            this.initGoodsDetail();
        },
        mounted() {
            window.addEventListener("scroll", this.handleScroll);
        },
        destroyed() {
            window.removeEventListener('scroll', this.handleScroll);
        }
    }
</script>
