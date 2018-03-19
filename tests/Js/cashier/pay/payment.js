
import process from 'process';
const jsdom = require('jsdom');
const { JSDOM } = jsdom;
const dom = new JSDOM('<!DOCTYPE html><html><body><div class="short-text">tesast</div></body></html>');
var window = dom.window;
global.window = window;
global.$ = require('jquery');

let pamentPath = process.cwd() + '/app/Resources/static-src/app/js/cashier/pay/payment.js';
const BasePayment = require(pamentPath);


// var assert = require('chai').assert;
// describe('pay:payment', function() {
//   it('function:getTrade', function() {
    
//   });
// });