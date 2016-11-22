import glob from 'glob';
import fs from 'fs';

const searchEntries = (entryPath, filenamePrefix = '') => {

  let files = {};

  entryPath = entryPath.replace(/\/$/, '');
  glob.sync(entryPath + '/**/index.{js,jsx}').forEach((file) => {
    const entryName = filenamePrefix + file.replace(entryPath + '/', '').replace(file.substring(file.lastIndexOf('.')), '');
    files[entryName] = file;
  });
  return files;
}

const fsExistsSync = (path) => {
  try {
    fs.accessSync(path,fs.F_OK);

  }catch(e) {
    return false;
  }

  return true;
}

const searchBundles = (paths,filterpath) => {
  let BundlesArr = [];

  paths.forEach((path) => {
    let dirs = fs.readdirSync(path);
    let rootdir = path.substring(path.lastIndexOf('/') + 1)

    dirs = dirs.filter((dir) => {
      return dir !== '.DS_Store' && dir !== '.gitkeep' && fsExistsSync(`${path}/${dir}/${filterpath}`);
    })

    dirs = dirs.map((dir) => {
      return `${rootdir}/${dir}`;
    })

    BundlesArr = BundlesArr.concat(dirs);
  })
  
  return BundlesArr;
}

export { searchEntries,searchBundles,fsExistsSync };