<template>
  <div class="student-manage">
    <iframe id="iframe" :src="`/admin/v2/multi_class/overview/${this.$route.params.id}`" 
      frameborder="0" 
      style="position:inherit; left:0; top:0; min-height: 600px; height:100%; width:100%; border:0px;" allowfullscreen="true" scrolling="yes" allow="screen-wake-lock"></iframe>
  </div>
</template>

<script>
  export default {
    name: 'DataPreview',
    methods: {
      getIframeStatus() {
        let iframe = document.getElementById("iframe");
        let iframeWindow=iframe.contentWindow;
        //内容是否加载完
        if(iframeWindow.document.readyState === "complete")
        {
          let iframeWidth,iframeHeight;
          //获取Iframe的内容实际宽度
          iframeWidth=iframeWindow.document.documentElement.scrollWidth;
          //获取Iframe的内容实际高度
          iframeHeight=iframeWindow.document.documentElement.scrollHeight;
          //设置Iframe的宽度
          iframe.width=iframeWidth;
          //设置Iframe的高度
          iframe.height=iframeHeight;
        } else {
          setTimeout(this.getIframeStatus,10);
        }
      }
    },
    created() {
      setTimeout(this.getIframeStatus,10);
    }
  }
</script>
