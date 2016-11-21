import { argv } from 'yargs';
import path from 'path';
import parameters from './parameters';
import { searchEntries } from './util';

let specialArgv = {};
argv._.forEach((arg) => {
  if (arg.indexOf(':') > 0) {
    let argArr = arg.split(':');
    specialArgv[argArr[0]] = argArr[1];
  }
});

let port = specialArgv.port || 3030;
let debugMode = !!argv.debugMode;
let devMode = process.env.NODE_ENV === 'development';

const currentDir = path.resolve(__dirname);
const globalAssetsDir = path.resolve(currentDir, '../');
const rootDir = path.resolve(currentDir, '../../../../');
const srcDir = path.resolve(rootDir, 'src');
const nodeModulesDir = path.resolve(rootDir, 'node_modules');
const libsDir = path.resolve(globalAssetsDir, 'libs');
const commonDir = path.resolve(globalAssetsDir, 'common');

let assetsSrcDirs = [globalAssetsDir];
let bundleEntry = {};
  /**
   * let bundleEntry = {
   *    topxiaweb: {
   *      topxiaweb: '/src/Topxia/WebBundle/Resources/assets/main.js',
   *      'app/default/index': '/src/Topxia/WebBundle/Resources/assets/js/default/index.js',
   *      ...
   *    },
   *    topxiaadmin: {
   *      topxiaadmin: '/src/Topxia/AdminBundle/Resources/assets/main.js',
   *      'admin/default/index': '/src/Topxia/AdminBundle/Resources/assets/js/default/index.js',
   *      ...
   *    },
   * };
   */
parameters.registeredBundles.forEach((bundle) => {
  const bundleAssetsDir = `${rootDir}/${bundle}/Resources/assets`;

  const bundleBuildDir = `${bundle}/Resources/build`;

  const bundleName = bundle.replace('src','').replace('plugins','').replace('Bundle', '').replace(/\//g, '').toLowerCase();

  bundleEntry[bundleName] = {};

  if(devMode) {
    bundleEntry[bundleName][`${bundleName}/main`] = `${bundleAssetsDir}/main.js`;
    Object.assign(bundleEntry[bundleName], searchEntries(`${bundleAssetsDir}/js`, `${bundleName}/js/`));

  }else {
    bundleEntry[bundleName][`${bundleBuildDir}/main`] = `${bundleAssetsDir}/main.js`;
    Object.assign(bundleEntry[bundleName], searchEntries(`${bundleAssetsDir}/js`, `${bundleBuildDir}/js/`));
  }

  assetsSrcDirs.push(bundleAssetsDir);
});

console.log('bundleEntry',bundleEntry)

let libEntry = {};
let libEntryPrefix = 'libs/';
//convert relative path to absolute path if it's a js file
for (let key in parameters.libs) {
  libEntry[`${libEntryPrefix}${key}`] = [];
  parameters.libs[key].forEach((le) => {
    if (le.indexOf('.js') > 0) {
      libEntry[`${libEntryPrefix}${key}`].push(path.resolve(currentDir, le));
    } else {
      libEntry[`${libEntryPrefix}${key}`].push(le);
    }
  });
}

let onlyCopys = [];
let copyitem = {};
parameters.onlyCopys.forEach((item) => {
  copyitem = {
    from : `${nodeModulesDir}/${item.name}`,
    to: `${libEntryPrefix}${item.name}`,
    ignore: item.ignore
  }
  onlyCopys.push(copyitem);
})

let outputPath = devMode ? path.resolve(currentDir, parameters.output.path) : path.resolve(currentDir, parameters.output.buildpath)

let config = {

  // Environment
  __DEBUG__: debugMode,
  __DEV__: devMode,
  __DEV_SERVER_PORT__: port,

  // Dir
  rootDir,
  srcDir,
  libsDir,
  commonDir,
  assetsSrcDirs,
  nodeModulesDir,

  // Webpack
  bundleEntry: bundleEntry,
  libEntry: libEntry,

  output: {
    path : outputPath,
    publicPath: parameters.output.publicPath
  },
  noParseDeps: parameters.noParseDeps || [],
  onlyCopys: onlyCopys || [],
  commonsChunkFilename: parameters.commonsChunkFilename
};

export default config;
