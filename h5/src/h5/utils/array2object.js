export default (array, key) => {
  const object = {};
  for (let i = 0; i < array.length; i += 1) {
    if (!array[i][key]) {
      break;
    }
    object[array[i][key]] = array[i];
  }
  return object;
};
