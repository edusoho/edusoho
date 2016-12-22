export const trim = (str,is_global=true)=> {
  let result = str.replace(/(^\s+)|(\s+$)/g,"");
  if(is_global) {
    result = result.replace(/\s/g,"");
  }
  return result;
}

export const numberConvertLetter = (number)=>  {
  return number <= 26 ? String.fromCharCode(number + 64) : convert(~~((number - 1) / 26)) + convert(number % 26 || 26);
}
