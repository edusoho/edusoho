<template>
    <a :href="drpInfo.recruitUrl" v-if="drpRecruitSwitch && !drpInfo.bindRelation && drpInfo.isMobile">
        <span class="distribution-tips text-overflow">赚{{ drpInfo.earnings }}元</span>
    </a>
    <a href="#modal" v-else-if="drpRecruitSwitch && !drpInfo.bindRelation && !drpInfo.isMobile" data-target="#modal" data-toggle="modal" :data-url="`/drp/show/recriut_link_qrcode_modal?type=${drpInfo.type}&id=${drpInfo.id}`">
        <span class="distribution-tips text-overflow">赚{{ drpInfo.earnings }}元</span>
    </a>
    <a :href="drpInfo.generalizeUrl" v-else-if="drpInfo.bindRelation && drpInfo.isMobile">
        <span class="distribution-tips text-overflow">赚{{ drpInfo.earnings }}元</span>
    </a>
    <a href="#modal" v-else-if="drpInfo.bindRelation && !drpInfo.isMobile" data-target="#modal" data-toggle="modal" :data-url="`/drp/show/generalize_link_qrcode_modal?type=${drpInfo.type}&id=${drpInfo.id}`">
        <span class="distribution-tips text-overflow">赚{{ drpInfo.earnings }}元</span>
    </a>
</template>

<script>
    export default{
        props: {
            goods: {
                type: Object,
                default: null
            },
            isUserLogin: {
                type: Number,
                default: 0,
            },
            drpInfo: {
                type: Array,
                default: null,
            },
            drpRecruitSwitch: {
                type: Number,
                default: 0
            }
        }
    }
</script>