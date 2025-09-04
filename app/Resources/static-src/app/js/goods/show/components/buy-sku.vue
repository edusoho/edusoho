<template>
    <div>
        <div class="clearfix">
            <a v-if="sku.isMember" :class="btnClass" class="goods-btn-hover pull-right" :href="sku.learnUrl">
                <slot>{{ 'goods.show_page.goto_learn'|trans }}</slot>
            </a>
            <span v-else>
                <span v-if="buyViewMode === 'text' && isShow" class="pull-right">
                    {{ buyViewText }}
                </span>
                <span v-if="buyViewMode === 'btn'">
                    <a :class="btnClass"
                       v-if="!needBuyVip || (needBuyVip && parseInt(sku.buyable))"
                       class="goods-btn-hover pull-right"
                       @click="buySku">
                        <slot>{{ buyViewText }}</slot>
                    </a>
                    <a :class="btnClass"
                       v-if="sku.hidePrice != '1' && isShow && needBuyVip"
                       class="goods-btn-hover pull-right"
                       :href="needBuyVipUrl"
                       >
                        <slot>{{ needBuyVipText|trans({'%vipName%': sku.vipLevelInfo.name}) }}</slot>
                    </a>
                    <span v-if="!sku.isMember && goods.type === 'classroom' && parseInt(sku.buyable) && isShow">
                        <a class="btn btn-link pull-right" style="margin-top:8px;" :href="`/classroom/${sku.targetId}/becomeAuditor`">{{ 'classroom.go_inside'|trans }}</a>
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
                needBuyVip : 0,
                needBuyVipText: '',
                needBuyVipUrl: "",
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
            },
            isShow: {
                type: Boolean,
                default: true
            },
            vipEnabled: {
                type: Number,
                default: 1
            }
        },
        methods: {
            renderModal(template) {
                axios.get('/goods/buy_flow/modal?' + qs.stringify({template: template}), {}).then(res => {
                    $('#modal').modal('show').html(res.data);
                });
            },
            buySku() {
                if(this.goods.product.target.status == 'closed') {
                    return
                }

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

                    if (['before_event', 'after_event'].includes(res.data.code)) {
                        window.location.href = res.data.context.url;
                        return;
                    }
                    this.renderModal(res.data.code);
                }).catch(err => {
                    window.location.reload()
                })
            },
            vipBtnTips(sku) {
                return sku.vipUser && parseInt(sku.vipUser.deadline) * 1000 > new Date().getTime() ? `你还不是${sku.vipLevelInfo.name}，<a class='color-primary' href='/vip/upgrade?targetId=${sku.vipLevelInfo.id}' target='_blank'>升级会员</a>` : `你还不是${ sku.vipLevelInfo.name }，<a class='color-primary' href='/vip/buy?level=${sku.vipLevelInfo.id}' target='_blank'>购买会员</a>`;
            },
            mainBtnView(sku) {
                this.needBuyVip = 0;
                if(this.goods.product.target.status == 'closed') {
                    this.buyViewMode = 'text';
                    this.buyViewText = Translator.trans('goods.show_page.closed_tips');
                }  else if (sku.status !== 'published' && this.goods.product.target.status !== 'unpublished') { //如果商品未发布
                    this.buyViewMode = 'text';
                    this.buyViewText = Translator.trans('goods.show_page.unpublished_tips');
                }  else if (sku.buyable == 1
                    && sku.buyableEndTime != 0
                    && new Date(sku.buyableEndTime).getTime() < new Date().getTime()) { // 已发布，开放购买，但是不在开放时间区间内
                    console.log(new Date(sku.buyableEndTime).getTime());
                    this.buyViewMode = 'text';
                    this.buyViewText = Translator.trans('goods.show_page.join_expiry_tips');
                } else if (['date', 'end_date'].includes(sku.usageMode)
                    && new Date(parseInt(sku.usageEndTime)).getTime() * 1000 < new Date().getTime()) { // 已发布，开放购买，但是超过最后学习期限了
                    console.log(new Date(parseInt(sku.usageEndTime)).getTime() * 1000);
                    this.buyViewMode = 'text';
                    console.log(new Date(parseInt(sku.usageEndTime)).getTime());
                    this.buyViewText = Translator.trans('goods.show_page.usage_expiry_tips');
                } else if (sku.vipLevelInfo && this.vipEnabled) {
                    this.buyViewMode = 'btn';
                    if (sku.vipUser && sku.canVipJoin && parseInt(sku.vipUser.deadline) * 1000 > new Date().getTime()) {
                        this.buyViewText = Translator.trans('goods.show_page.vip_free_learn_new');
                        this.needBuyVip = 0;
                    } else {
                        this.needBuyVip = 1;
                        this.buyViewText = sku.displayPrice == 0 ? Translator.trans('goods.show_page.free_join_btn') : Translator.trans('goods.show_page.buy_btn');
                        if (!sku.vipUser || (sku.vipUser && parseInt(sku.vipUser.deadline) * 1000 < new Date().getTime())){
                            this.needBuyVipText = Translator.trans('goods.show_page.vip_buy',{vipName:sku.vipLevelInfo.name});
                            this.needBuyVipUrl = "/vip?levelId=" + sku.vipLevelInfo.id;
                        }else if (sku.vipUser){
                            this.needBuyVipText = Translator.trans('goods.show_page.vip_upgrade',{vipName:sku.vipLevelInfo.name});
                            this.needBuyVipUrl = "/vip?levelId=" + sku.vipLevelInfo.id;
                        }
                    }
                } else if (sku.buyable != 1) {  // 已发布，但是未开放购买
                    this.buyViewMode = 'text';
                    if (this.goods.type == 'course'){
                        this.buyViewText = Translator.trans('goods.show_page.course_not_buyable_tips');
                    }else{
                        this.buyViewText = Translator.trans('goods.show_page.classroom_not_buyable_tips');
                    }
                } else if (sku.displayPrice == 0) {
                    this.buyViewMode = 'btn';
                    this.buyViewText = Translator.trans('goods.show_page.free_join_btn');
                } else {
                    this.buyViewMode = 'btn';
                    this.buyViewText = Translator.trans('goods.show_page.buy_btn');
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
