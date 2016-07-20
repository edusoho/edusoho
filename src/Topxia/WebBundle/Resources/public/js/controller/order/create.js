define(function(require, exports, module) {

	require('placeholder');
	
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

	exports.run = function() {
		var cashRateElement = $('[role="cash-rate"]');
		var cashRate = 1;
		if($('[role="cash-rate"]').val() != ""){
			cashRate = $('[role="cash-rate"]').val();
			cashRate = parseInt(cashRate*100)/100;
		}
		var validator = new Validator({
            element: '#order-create-form',
            triggerType: 'change',
            //autoSubmit: false,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#order-create-btn').button('submiting').attr('disabled', true);
            }
        });

		function divition(x,y) {
			return Math.round(Math.round(x*1000)/Math.round(y*1000)*1000)/1000;
		}

		function multiple(x, y){
			return Math.round(Math.round(x*100) * Math.round(y*100))/10000;
		}

		function subtract(x,y) {
			return Math.round(Math.round(x*1000)-Math.round(y*1000))/1000;
		}

		function moneyFormatFloor(value) {
	        // 转化成字符串
	        value = value + '';
	        value = parseInt(Math.round(value * 1000));
	        // 抹去最后１位
	        value = parseInt(value/10) * 10 / 1000;
	        return value.toFixed(2);
	    }

		function moneyFormatCeil(value) {
	        value = value + '';
	        value = parseFloat(value).toFixed(3);
	        var length = value.length;
	        if (value.substr(length-1, 1) === '0') {
	            return moneyFormatFloor(value);
	        }
	        return moneyFormatFloor(parseFloat(value) + 0.01);
	    }

		function afterCouponPay(totalPrice){
			var couponTotalPrice = $('[role="coupon-price"]').find("[role='price']").text();
			if($.trim(couponTotalPrice) == "" || isNaN(couponTotalPrice)){
				couponTotalPrice = 0;
			}
			if(totalPrice < couponTotalPrice){
 				couponTotalPrice = totalPrice;
 			}
			totalPrice = subtract(totalPrice, couponTotalPrice);
			return totalPrice;
		}

		function afterCoinPay(coinNum){
			var accountCash = $('[role="accountCash"]').text();
			if(accountCash == "" || isNaN(accountCash) || parseFloat(accountCash) == 0) {
				coinPriceZero();
				return 0;
			}
			var coin = Math.round(accountCash*1000)>Math.round(coinNum*1000) ? coinNum : accountCash;
			if(cashRateElement.data("priceType") == "RMB"){
				var totalPrice = parseFloat($('[role="total-price"]').text());
				var cashDiscount = Math.round(moneyFormatFloor(divition(coin, cashRate))*100)/100;
				if(totalPrice < cashDiscount){
	 				cashDiscount = totalPrice;
	 			}
				$('[role="cash-discount"]').text(moneyFormatFloor(cashDiscount));
			}else{
				$('[role="cash-discount"]').text(moneyFormatFloor(coin));
			}
			return coin;
			
		}

		function getMaxCoinCanPay(totalCoinPrice){
			var maxCoin = parseFloat($('[role="maxCoin"]').text());
			var maxCoinCanPay = totalCoinPrice < maxCoin ? totalCoinPrice: maxCoin;
			var myCashAccount = $('[role="accountCash"]');
			if(myCashAccount.length>0){
				var myCash = parseFloat(myCashAccount.text()*100)/100;
				maxCoinCanPay = maxCoinCanPay < myCash ? maxCoinCanPay: myCash;
			}

			return maxCoinCanPay;
		}

		function conculatePrice(){
			var totalPrice = parseFloat($('[role="total-price"]').text());
			totalPrice = afterCouponPay(totalPrice);
			
			var cashModel = cashRateElement.data('cashModel');
			switch(cashModel){
				case 'none':
					totalPrice = totalPrice >= 0 ? totalPrice : 0;
					shouldPay(totalPrice);
					break;
				case 'deduction':
					var totalCoinPrice = multiple(totalPrice, cashRate);
					totalCoinPrice = moneyFormatCeil(totalCoinPrice);
					var maxCoinCanPay = getMaxCoinCanPay(totalCoinPrice);
					var coinNumPay = $('[role="coinNum"]').val();

					if(maxCoinCanPay <= parseFloat(coinNumPay)){
						coinNumPay = maxCoinCanPay;
					}

					$('[role="coinNum"]').val(coinNumPay);
					if(coinNumPay==0) {
						coinPriceZero();
					}

					if(coinNumPay && $('[name="payPassword"]').length>0){
						coinNumPay = afterCoinPay(coinNumPay);
						var cashDiscount = $('[role="cash-discount"]').text();
						totalPrice = subtract(totalPrice, cashDiscount);
					} else {
						$('[role="coinNum"]').val(0);
						$('[role="cash-discount"]').text("0.00");
					}

					totalPrice = totalPrice >= 0 ? totalPrice : 0;
					shouldPay(totalPrice);
					break;
				case 'currency':
					
					var totalCoinPrice = totalPrice;
					var coinNumPay = $('[role="coinNum"]').val();

					if(totalCoinPrice <= parseFloat(coinNumPay)){
						coinNumPay = totalCoinPrice;
					}

					$('[role="coinNum"]').val(coinNumPay);

					if(coinNumPay==0) {
						coinPriceZero();
					}

					if(coinNumPay && $('[name="payPassword"]').length>0){
						coinNumPay = afterCoinPay(coinNumPay);
						var cashDiscount = $('[role="cash-discount"]').text();
						totalPrice = subtract(totalPrice, cashDiscount);
					} else {
						$('[role="coinNum"]').val(0);
						$('[role="cash-discount"]').text("0.00");
					}

					totalPrice = totalPrice >= 0 ? totalPrice : 0;
					shouldPay(totalPrice);

					break;
			}

		}

		function shouldPay(totalPrice){
			totalPrice = Math.round(totalPrice*1000)/1000;
			if(cashRateElement.data("priceType") == "RMB") {
				totalPrice = moneyFormatCeil(totalPrice);
				$('[role="pay-rmb"]').text(totalPrice);
				$('input[name="shouldPayMoney"]').val(totalPrice);
			} else {
				var payRmb = moneyFormatCeil(divition(totalPrice, cashRate));
				var shouldPayMoney = Math.round(payRmb*100)/100;
				$('[role="pay-coin"]').text(totalPrice);
				$('[role="pay-rmb"]').text(shouldPayMoney);
				$('input[name="shouldPayMoney"]').val(shouldPayMoney);
			}
		}

		function coinPriceZero(){
			$('[role="coinNum"]').val(0);
			$('[role="cash-discount"]').text("0.00");
			$("[role='password-input']").hide();
			validator.removeItem('[name="payPassword"]');
		}

		function showPayPassword(){
			$("[role='password-input']").show();
			validator.addItem({
				element: '[name="payPassword"]',
				required: true,
				display: '支付密码',
    			rule: 'remote'
			});
		}

		$('[role="coinNum"]').blur(function(e){
			var coinNum = $(this).val();
			coinNum = Math.round(coinNum*100)/100;
			$(this).val(coinNum);
			if(isNaN(coinNum) || coinNum<=0){
				$(this).val("0.00");
				coinPriceZero();
			} else {
				showPayPassword();
			}
			conculatePrice();
		});

		$("#coupon-code-btn").click(function(e){
			// $('[role="cancel-coupon"]').trigger('click');
			$('[role="coupon-price"]').find("[role='price']").text("0.00");
			$('[role="code-notify"]').text("").removeClass('alert-success');
			$('[role="coupon-code"]').val("");
			$('[role="cancel-coupon"]').hide();
			$('[role="coupon-code-verified"]').val("");
			$('[role="coupon-code-input"]').val("");
			conculatePrice();
			$('[role="coupon-code"]').show();
			$('[role="coupon-code-input"]').focus();
			// $('[role="no-use-coupon-code"]').hide();
			$('[role="cancel-coupon"]').show();
			$('[role="null-coupon-code"]').hide();

			// $('[role="code-notify"]').show();
			$(this).hide();
		})

		$('[role="cancel-coupon"]').click(function(e){
			if($('#coupon-select').val() != "") {
				couponCode = $('[role="coupon-code-input"]');
				couponCode.val(couponDefaultSelect);
				$('button[role="coupon-use"]').trigger('click');
			}
			$('[role="coupon-code"]').hide();
			// $('[role="no-use-coupon-code"]').show();
			$("#coupon-code-btn").show();
			$('[role="null-coupon-code"]').show();
			$('[role="code-notify"]').hide();
			$('[role="coupon-price"]').find("[role='price']").text("0.00");
			$('[role="code-notify"]').text("");
			$('[role="coupon-code"]').val("");
			$(this).hide();
			$('[role="coupon-code-verified"]').val("");
			$('[role="coupon-code-input"]').val("");
			conculatePrice();

		});

		$('button[role="coupon-use"]').click(function(e){
			var data={};
			var couponCode = $('[role="coupon-code-input"]');
			data.code = couponCode.val();
			if(data.code == ""){
				$('[role="coupon-price-input"]').find("[role='price']").text("0.00");
				return;
			}
			data.targetType = couponCode.data("targetType");
			data.targetId = couponCode.data("targetId");

			var totalPrice = parseFloat($('[role="total-price"]').text());
			
			data.amount = totalPrice;
			$.post('/'+data.targetType+'/'+data.targetId+'/coupon/check', data, function(data){
				$('[role="code-notify"]').css("display","inline-block");
				if(data.useable == "no") {
					$('[role=no-use-coupon-code]').show();
					$('[role="code-notify"]').removeClass('alert-success').addClass("alert-danger").text(data.message);
				} else if(data.useable == "yes"){
					$('[role=no-use-coupon-code]').hide();
					$('[role="code-notify"]').removeClass('alert-danger').addClass("alert-success").text("优惠券可用，您当前使用的是"+((data['type']=='discount')? ('打'+data['rate']+'折') : ('抵价'+data['rate']+'元'))+'的优惠券');
					$('[role="coupon-price"]').find("[role='price']").text(moneyFormatFloor(data.decreaseAmount));
					$('[role="coupon-code-verified"]').val(couponCode.val());
				}
				conculatePrice();
			})
		})
		
		var couponDefaultSelect = $('#coupon-select').val();
		if(couponDefaultSelect != "") {
			couponCode = $('[role="coupon-code-input"]');
			couponCode.val(couponDefaultSelect);
			$('button[role="coupon-use"]').trigger('click');
		}

		$('#coupon-select').change(function(e){
			//新添加js
			var coupon = $(this).children('option:selected');
			if(coupon.data('code') == "")
			{
				$('[role=no-use-coupon-code]').show();
				$('[role="cancel-coupon"]').trigger('click');
				return;
			}else{
				$('[role=no-use-coupon-code]').hide();
			}
			couponCode = $('[role="coupon-code-input"]');
			couponCode.val(coupon.data('code'));
			$('button[role="coupon-use"]').trigger('click');
			$('[role="code-notify"]').removeClass('alert-success');
		})

 		var totalPrice = parseFloat($('[role="total-price"]').text());
 		if($('[role="coinNum"]').length>0) {
 			var coinNum = $('[role="coinNum"]').val();
 			if(isNaN(coinNum) || coinNum<=0){
				$(this).val("0.00");
				coinPriceZero();
			} else {
				showPayPassword();
			}
			if(cashRateElement.data("priceType") == "RMB") {
	 			var discount = divition(coinNum, cashRate);
	 			if(totalPrice<discount){
	 				discount = totalPrice;
	 			}
	 			$('[role="cash-discount"]').text(moneyFormatFloor(discount));
	 			totalPrice = subtract(totalPrice, discount);
 			} else {
 				$('[role="cash-discount"]').text(moneyFormatFloor(coinNum));
 				totalPrice = subtract(totalPrice, coinNum);
 			}
 		} else {
 			$('[role="cash-discount"]').text("0.00");
 		}
 		shouldPay(totalPrice);
		
		if($('#js-order-create-sms-btn').length > 0){
	 		$('#js-order-create-sms-btn').click(function(e){
	 			var coinToPay = $('#coinPayAmount').val();
	 			if (coinToPay && (coinToPay.length > 0)&&(!isNaN(coinToPay))&&(coinToPay > 0)&&($("#js-order-create-sms-btn").length>0)){
	 				$("#payPassword").trigger("change");
	 				if ( $('[role="password-input"]').find('span[class="text-danger"]').length > 0) {
	 					e.stopPropagation();
	 				}
	 			} else {
	 				e.stopPropagation();
	 				$("#order-create-form").submit();
	 			}
	 		});
		}
	}
});