export const imageScale = (naturalWidth, naturalHeight, cropWidth, cropHeight) => {

  let width = cropWidth;
  let height = cropHeight;

  let naturalScale = naturalWidth / naturalHeight;
  let cropScale = cropWidth / cropHeight;

  if (naturalScale > cropScale) {
    width = naturalScale * cropWidth;
  } else {
    height =  cropHeight / naturalScale;
  }

  return {
    width: width,
    height: height
  }
};