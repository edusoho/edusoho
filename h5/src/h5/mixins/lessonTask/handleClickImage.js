import { ImagePreview } from 'vant'

export default {
  methods: {
    handleClickImage (imagesUrl) {
      if (imagesUrl === undefined) return;
      event.stopPropagation();//  阻止冒泡
      const images = [imagesUrl]
      ImagePreview({
        images
      })
    },
    
  },
};
