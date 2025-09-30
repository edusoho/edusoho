const { execFile } = require('child_process');
const chalk = require('chalk');

const vue3Process = execFile('node', ['./webpack-build/vue3_dev.js']);
vue3Process.stdout.on('data', data => {
  console.log(data);
});
vue3Process.stderr.on('data', data => {
  console.log(chalk.red(data));
});

const esProcess = execFile('node', ['./webpack-build/es_dev.js']);
esProcess.stdout.on('data', data => {
  console.log(data);
});
esProcess.stderr.on('data', data => {
  console.log(chalk.red(data));
});
