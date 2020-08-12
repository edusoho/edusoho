<template>
    <div>
        <a v-if="sku.isMember" :class="btnClass" :href="sku.learnUrl">
            <slot>去学习</slot>
        </a>
        <span v-else-if="sku.status !=='published'" class="product-detail__unpublished">商品还未发布，不允许加入和购买</span>
        <span v-else-if="sku.buyable == 1 && sku.buyableEndTime != 0 && new Date(sku.buyableEndTime).getTime() > new Date().getTime() + 86400000" class="product-detail__unpublished">抱歉，该商品已超过加入有效期，请联系客服</span>
        <span v-else-if="sku.buyable != 1" class="product-detail__unpublished">抱歉，该商品为限制商品，请联系客服</span>
        <a class="product-detail__disable_btn" v-if="(sku.vipLevelInfo && !sku.vipUser) || sku.vipLevelInfo && sku.vipUser && sku.vipLevelInfo.seq < sku.vipUser.level.seq" data-toggle="tooltip" data-trigger="hover"
           data-placement="top" title="" href="javascript:;">
            <slot>会员免费学</slot>
        </a>
        <a v-else-if="sku.status === 'published' && !sku.isMember" :class="btnClass" href="javascript:;" @click="buySku">
            <slot v-if="sku.price == 0">免费加入</slot>
            <slot v-if="sku.vipLevelInfo && sku.vipUser && sku.vipLevelInfo.seq >= sku.vipUser.level.seq">会员免费学</slot>
            <slot v-else>立即购买</slot>sku
        </a>
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

                    if (res.data.code == 'is-joined') {
                        window.location.href = this.goods.type == 'course' ? '/my/course/' + this.sku.targetId : '/classroom/' + this.sku.targetId;
                        return;
                    }

                    this.renderModal(res.data.code);
                }).catch();
            }
        }
    }
</script>

<style scoped>

</style>