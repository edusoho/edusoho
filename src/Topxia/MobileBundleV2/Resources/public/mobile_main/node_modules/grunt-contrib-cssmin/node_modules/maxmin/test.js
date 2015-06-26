'use strict';
var test = require('ava');
var chalk = require('chalk');
var maxmin = require('./');

var max = 'function smoothRangeRandom(min,max){var num=Math.floor(Math.random()*(max-min+1)+min);return this.prev=num===this.prev?++num:num};function smoothRangeRandom(min,max){var num=Math.floor(Math.random()*(max-min+1)+min);return this.prev=num===this.prev?++num:num};function smoothRangeRandom(min,max){var num=Math.floor(Math.random()*(max-min+1)+min);return this.prev=num===this.prev?++num:num};';
var min = 'function smoothRangeRandom(b,c){var a=Math.floor(Math.random()*(c-b+1)+b);return this.prev=a===this.prev?++a:a}function smoothRangeRandom(b,c){var a=Math.floor(Math.random()*(c-b+1)+b);return this.prev=a===this.prev?++a:a}function smoothRangeRandom(b,c){var a=Math.floor(Math.random()*(c-b+1)+b);return this.prev=a===this.prev?++a:a};';

test('should generate correct output for strings', function (t) {
	t.assert(chalk.stripColor(maxmin(max, min)) === '390 B → 334 B');
	t.assert(chalk.stripColor(maxmin(max, min, true)) === '390 B → 334 B → 120 B (gzip)');
});

test('should generate correct output for buffers', function (t) {
	t.assert(chalk.stripColor(maxmin(new Buffer(max), new Buffer(min))) === '390 B → 334 B');
	t.assert(chalk.stripColor(maxmin(new Buffer(max), new Buffer(min), true)) === '390 B → 334 B → 120 B (gzip)');
});

test('should generate correct output for integers', function (t) {
	t.assert(chalk.stripColor(maxmin(max.length, min.length)) === '390 B → 334 B');
	t.assert(chalk.stripColor(maxmin(max.length, min.length, true)) === '390 B → 334 B');
});