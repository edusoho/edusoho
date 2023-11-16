<template>
    <div class="closed-page-container">
    <img class="closed-img" src="/static/images/good-closed.png" alt="">
    <div class="closed-page-title js-closed-title">{{ closedTitle }}</div>
    <div class="closed-page-content">{{ $t('goods.closed_content') }}</div>
    <a class="closed-page-goHome" href="/">{{ $t('goods.goHome') }}</a>
</div>
</template>
<script>
export default {
    computed:{
        closedTitle() {
            const type = this.$route.query.type

            if( type == 'course' ) {
                return this.$t('goods.closed_course')
            }

            if( type == 'classroom') {
                return this.$t('goods.closed_classroom')
            }

            if( type == 'exercise') {
                return this.$t('goods.closed_exercise')
            }

            return this.$t('goods.closed_title')
        }
    }
}
</script>
<style lang="scss" scoped>
.closed-page-container {
    width: 100%;
    text-align: center;
}
.closed-img {
    display: block;
    width: 236px;
    height: 236px;
    margin: auto;
    margin-top: 160px;
}

.closed-page-title {
    color: #37393D;
    text-align: center;
    font-size: 16px;
    font-weight: 500;
    line-height: 24px; 
}

.closed-page-content {
    color: #919399;
    text-align: center;
    font-size: 12px;
    font-weight: 400;
    line-height: 20px;
    margin-top: 6px;
}

.closed-page-goHome {
    display: block;
    padding: 5px 15px;
    margin: 0 auto;
    margin-top: 36px;
    width: 88px;
    border-radius: 18px;
    background: #5DB85D;
    color: #fff;
    cursor: pointer;
    white-space: nowrap;
    outline: none;
    text-decoration: none;
}
</style>
