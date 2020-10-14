<template>
    <div>
<!--        <div class="clearfix">-->
<!--            <a v-if="sku.isMember" :class="btnClass" class="goods-btn-hover pull-right" :href="sku.learnUrl">-->
<!--                <slot>去学习</slot>-->
<!--            </a>-->
<!--            <span v-else-if="sku.status !=='published'" class="product-detail__unpublished pull-right">商品还未发布，不允许加入和购买</span>-->
<!--            <span v-else-if="sku.buyable == 1 && sku.buyableEndTime != 0 && new Date(sku.buyableEndTime).getTime() < new Date().getTime() + 86400000" class="product-detail__unpublished pull-right">抱歉，该商品已超过加入有效期，请联系客服</span>-->
<!--            <span v-else-if="sku.buyable != 1" class="product-detail__unpublished pull-right">抱歉，该商品为限制商品，请联系客服</span>-->
<!--            <span v-else>-->
<!--                <span class="product-detail__disable_btn goods-btn-hover pull-right" v-if="(sku.vipLevelInfo && !sku.vipUser) || sku.vipLevelInfo && sku.vipUser && sku.vipLevelInfo.seq > sku.vipUser.level.seq" data-container=".product-detail__disable_btn" data-toggle="popover" data-placement="top" data-trigger="hover" data-html="true"-->
<!--                      :data-content="sku.vipUser ? `你还不是${sku.vipLevelInfo.name}，<a class='color-primary' href='/vip/upgrade' target='_blank'>升级会员</a>` : `你还不是${ sku.vipLevelInfo.name }，<a class='color-primary' href='/vip/buy' target='_blank'>购买会员</a>`">-->
<!--                    <slot>会员免费学</slot>-->
<!--                </span>-->
<!--                <a v-if="!sku.isMember" :class="btnClass" class="goods-btn-hover pull-right" href="javascript:;" @click="buySku">-->
<!--                    <slot v-if="sku.displayPrice == 0">免费加入</slot>-->
<!--                    <slot v-else-if="sku.vipLevelInfo && sku.vipUser && sku.vipLevelInfo.seq <= sku.vipUser.level.seq">会员免费学</slot>-->
<!--                    <slot v-else>立即购买</slot>-->
<!--                </a>-->
<!--            </span>-->
<!--        </div>-->
        <div class="clearfix">
            <a v-if="sku.isMember" :class="btnClass" class="goods-btn-hover pull-right" :href="sku.learnUrl">
                <slot>去学习</slot>
            </a>
            <span v-else>
                <span v-if="buyViewMode === 'text'" class="product-detail__unpublished pull-right">
                    {{ buyViewText }}
                </span>
                <span v-if="buyViewMode === 'btn'">
                    <a :class="btnClass"
                       class="goods-btn-hover pull-right"
                       @click="buySku">
                        <slot>{{ buyViewText }}</slot>
                    </a>
                    <span class="product-detail__disable_btn goods-btn-hover pull-right"
                          v-if="(sku.vipLevelInfo && !sku.vipUser) || sku.vipLevelInfo && sku.vipUser && sku.vipLevelInfo.seq > sku.vipUser.level.seq"
                          data-container=".product-detail__disable_btn"
                          data-toggle="popover"
                          data-placement="top"
                          data-trigger="hover"
                          data-html="true"
                          :data-content="vipBtnTips(sku)">
                        <slot>会员免费学</slot>
                    </span>
                </span>
            </span>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import qs from 'qs';

    axios.interceptors.request.use((config) => {
        config.headers = {
            'Accept': 'application/vnd.edusoho.v2+json',
            'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        };

        return config;
    });

    export default {
        name: "buy-sku",
        data() {
            return {
                buyViewMode : '', //btn text
                buyViewText : '',
            }
        },
        props: {
            btnClass: {
                type: String,
                default: null,
            },
            sku: {
                type: Object,
                default: null
            },
            goods: {
                type: Object,
                default: null,
            },
            isUserLogin: {
                type: Number,
                default: 0,
            }
        },
        methods: {
            renderModal(template) {
                axios.get('/goods/buy_flow/modal?' + qs.stringify({template: template}), {}).then(res => {
                    $('#modal').modal('show').html(res.data);
                });
            },
            buySku() {
                if (!this.isUserLogin) {
                    axios.get($('#login-modal').data('url')).then(res => {
                        $('#login-modal').modal('show').html(res.data);
                    });
                    return;
                };

                axios({
                    url: '/api/goods/' + this.sku.goodsId + '/check',
                    method: "POST",
                    data: {
                        'targetId': this.sku.id,
                    }
                }).then(res => {
                    if (res.data.success) {
                        window.location.href = '/order/show?' + qs.stringify({
                            targetId: this.sku.id,
                            targetType: this.goods.type
                        });
                        return;
                    }

                    if (res.data.code === 'is-joined') {
                        window.location.href = this.goods.type === 'course' ? '/my/course/' + this.sku.targetId : '/classroom/' + this.sku.targetId;
                        return;
                    }

                    this.renderModal(res.data.code);
                }).catch();
            },
            vipBtnTips(sku) {
                return sku.vipUser ? `你还不是${sku.vipLevelInfo.name}，<a class='color-primary' href='/vip/upgrade' target='_blank'>升级会员</a>` : `你还不是${ sku.vipLevelInfo.name }，<a class='color-primary' href='/vip/buy' target='_blank'>购买会员</a>`;
            },
            mainBtnView(sku) {
                if (sku.status !== 'published') { //如果商品未发布
                    this.buyViewMode = 'text';
                    this.buyViewText = '商品还未发布，不允许加入和购买';
                } else if (sku.buyable != 1) {  // 已发布，但是未开放购买
                    this.buyViewMode = 'text';
                    this.buyViewText = '抱歉，该商品为限制商品，请联系客服';
                } else if (sku.buyable == 1
                    && sku.buyableEndTime != 0
                    && new Date(sku.buyableEndTime).getTime() < new Date().getTime() + 86400 * 1000) { // 已发布，开放购买，但是不在开放时间区间内
                    this.buyViewMode = 'text';
                    this.buyViewText = '抱歉，该商品已超过加入有效期，请联系客服';
                } else if (['date', 'end_date'].includes(sku.usageMode)
                    && new Date(sku.usageEndTime).getTime() < new Date().getTime() + 86400 * 1000) { // 已发布，开放购买，但是超过最后学习期限了
                    this.buyViewMode = 'text';
                    this.buyViewText = '超过学习截止日期，不允许加入和购买';
                } else if (sku.displayPrice == 0) {
                    this.buyViewMode = 'btn';
                    this.buyViewText = '免费加入';
                } else if (sku.vipLevelInfo && sku.vipUser && sku.vipLevelInfo.seq <= sku.vipUser.level.seq) {
                    this.buyViewMode = 'btn';
                    this.buyViewText = '会员免费学';
                } else {
                    this.buyViewMode = 'btn';
                    this.buyViewText = '立即购买';
                }
            },
        },
        created() {
            $(function () {
                $("[data-toggle='popover']").popover();
            });
        },
        updated() {
            $(function () {
                $("[data-toggle='popover']").popover();
            });
        },
        watch: {
            sku: {
                immediate: true,
                handler(val) {
                    this.mainBtnView(val);
                },
            },
        },
        filters: {
            goodsBtn(code, type, vipAccessToJoin) {
                if (vipAccessToJoin) {
                    return '会员免费学';
                }
                const targetType = {
                    course: '课程',
                    classroom: '班级',
                };
                switch (code) {
                    case 'success':
                    case 'user.not_login':
                        code = '加入学习';
                        break;
                    case 'user.locked':
                        code = '用户被锁定';
                        break;
                    case 'member.member_exist':
                        code = '课程学员已存在';
                        break;
                    case `${type}.reach_max_student_num`:
                        code = '学员达到上限';
                        break;
                    case `${type}.not_found`:
                        code = '计划不存在';
                        break;
                    case `${type}.unpublished`:
                        code = `${targetType[type]}未发布`;
                        break;
                    case `${type}.closed`:
                        code = `${targetType[type]}已关闭`;
                        break;
                    case `${type}.not_buyable`:
                        code = `${targetType[type]}不可加入`;
                        break;
                    case `${type}.buy_expired`:
                        code = '购买有效期已过';
                        break;
                    case `${type}.expired`:
                        code = '学习有效期已过';
                        break;
                    case `${type}.only_vip_join_way`:
                    case 'course.only_vip_join_way': // type 为班级时，code显示为 'course.only_vip_join_way', 此处临时处理
                        code = '只能通过VIP加入';
                        break;
                    default:
                }
                return code;
            }
        }
    }
</script>

<style scoped>

</style>