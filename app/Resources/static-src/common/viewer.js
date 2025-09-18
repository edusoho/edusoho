import Viewer from 'viewerjs';

const handleClickImage = (container) => {
  console.log('handleClickImage', container);
  if (!container) return;
  const viewer = new Viewer(container, {
    title: false,
    toolbar: {
      zoomIn: {
        show: true,
        size: 'large',
      },
      zoomOut: {
        show: true,
        size: 'large',
      },
      oneToOne: false,
      reset: {
        show: true,
        size: 'large',
      },
      prev: {
        show: true,
        size: 'large',
      },
      play: false,
      next: {
        show: true,
        size: 'large',
      },
      rotateLeft: {
        show: true,
        size: 'large',
      },
      rotateRight: {
        show: true,
        size: 'large',
      },
      flipHorizontal: {
        show: true,
        size: 'large',
      },
      flipVertical: {
        show: true,
        size: 'large',
      },
    }
  });
}

export {
  handleClickImage,
};