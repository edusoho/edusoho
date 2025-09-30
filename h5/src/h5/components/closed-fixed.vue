<template>
    <div class="closed-tips">
      <van-icon class="closed-icon" name="warning-o" color="#FF7D00" />
      <div class="ml-8 flex flex-col items-start">
        <span class="title">{{ title }}</span>
        <span class="content" v-if="isJoin">{{ content }}</span>
    </div>
    </div>
</template>
<script>
export default {
    props: {
        title: {
            type: String,
            default: ''
        },
        content: {
            type: String,
            default: ''
        },
        isJoin: {
            type: Boolean,
            default: false
        }
    }
}
</script>
<style scoped>
.closed-tips {
    position: absolute;
    bottom: 100%;
    background-color: #FFF7E8;
    width: 100%;
    padding: 8px 16px;
    display: flex;
}
.closed-icon {
    line-height: 20px
}
.title {
    font-size: 14px;
    font-weight: 400;
    line-height: 140%;
    color: #37393D;
}
.content {
    font-size: 12px;
    font-weight: 400;
    line-height: 140%;
    color: #919399;
    margin-top: 4px;
}
</style>