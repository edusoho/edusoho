define(function(require, exports, module) {
	var Validator = require('bootstrap.validator');


	Validator.addRule.apply(Validator, [
		'payPasswordRequired', 
		function(options) {
		    var coinNum = $("input[role='coinNum']").val();
		    var pass = false;
			if(coinNum>0) {
				var password = options.element.value;
				if($.trim(password) != "") {
					pass = true;
				}
			}
		    return pass;
		}, 
		'请填写支付密码'
	]);

	exports.run = function() {
		var cashRateElement = $('[role="cash-rate"]');
		var cashRate = 1;
		if($('[role="cash-rate"]').val() != "")
			cashRate = $('[role="cash-rate"]').val();

		function conculatePrice(){
			var totalPrice = parseFloat($('[role="total-price"]').text());
			var couponTotalPrice = $('[role="coupon-price"]').find(".price_r_num").text();
			if($.trim(couponTotalPrice) == "" || isNaN(couponTotalPrice)){
				couponTotalPrice = 0;
			}
			var couponAmount = parseFloat(couponTotalPrice);
			var payAmount = totalPrice-couponAmount;
			if(payAmount <= 0){
				payAmount = 0;
				$('[role="cash-discount"]').text(0);
				$('[role="coinNum"]').val(0);
			} 

			if(payAmount>0) {
				var coinAmount = parseFloat($('[role="cash-discount"]').text());
				payAmount = payAmount-coinAmount;
			}

			if(cashRateElement.data("coursePriceShowType") == "Coin") {
				$('[role="pay-coin"]').text(payAmount);
				var payRmb = payAmount/cashRate;
				fixedPayRmb = parseFloat(payRmb.toFixed(2));
				if(fixedPayRmb<payRmb){
					fixedPayRmb = parseFloat(fixedPayRmb)+0.01;
				}
				$('[role="pay-rmb"]').text(fixedPayRmb);
				$('input[name="shouldPayMoney"]').val(fixedPayRmb);
			} else {
				fixedPayAmount = payAmount.toFixed(2);
				if(fixedPayAmount<payAmount){
					fixedPayAmount = parseFloat(fixedPayAmount)+0.01;
				}
				$('[role="pay-rmb"]').text(fixedPayAmount);
				$('input[name="shouldPayMoney"]').val(fixedPayAmount);
			}
		}

		$('[role="coinNum"]').blur(function(e){
			var coin = $(this).val();
			if(isNaN(coin)){
				$(this).val("0");
				$('[role="cash-discount"]').text("0");
				return;
			}
			var cash = $('[role="accountCash"]').text();
			var discount = 0;
			if(parseFloat(cash) < parseFloat(coin)) {
				$(this).val(cash);
				if(cashRateElement.data("coursePriceShowType") != "Coin"){
					discount = cash/cashRate;
				} else {
					discount = cash;
				}
			} else {
				if(cashRateElement.data("coursePriceShowType") != "Coin"){
					discount = coin/cashRate;
				} else {
					discount = coin;
				}
			}

			var totalPrice = parseFloat($('[role="total-price"]').text());
			if(discount>totalPrice){
				discount = totalPrice;
				if(cashRateElement.data("coursePriceShowType") != "Coin"){
					$(this).val(discount*cashRate);
				} else {
					$(this).val(discount);
				}
			}

			$('[role="cash-discount"]').text(discount);
			conculatePrice();
		});

		$("#coupon-code-btn").click(function(e){
			$('[role="coupon-code"]').show().focus();
			$('[role="no-use-coupon-code"]').hide();
			$('[role="cancel-coupon"]').show();
			$('[role="code-notify"]').show();
			$(this).hide();
		})

		$('[role="cancel-coupon"]').click(function(e){
			$('[role="coupon-code"]').hide();
			$('[role="no-use-coupon-code"]').show();
			$("#coupon-code-btn").show();
			$('[role="code-notify"]').hide();
			$('[role="coupon-price"]').find(".price_r_num").text("0");
			$('[role="code-notify"]').text("");
			$('[role="coupon-code"]').val("");
			$(this).hide();
			conculatePrice();
		});

		$('[role="coupon-code"]').blur(function(e){
			var data={};
			data.code = $(this).val();
			if(data.code == ""){
				return;
			}
			data.targetType = "course";
			data.targetId = $(this).data("targetId");
			data.amount = $(this).data("amount");
			
			$.post('/course/'+data.targetId+'/coupon/check', data, function(data){
				if(data.useable == "no") {
					$('[role="code-notify"]').css("color","red").text(data.message);
				} else if(data.useable == "yes"){
					$('[role="code-notify"]').css("color","green").text("优惠码可用");
					if(cashRateElement.data("coursePriceShowType") == "RMB") {
						$('[role="coupon-price"]').find(".price_r_num").text(data.decreaseAmount);
					} else {
						var coinPrice = data.decreaseAmount*cashRate;
						var totalPrice = parseFloat($('[role="total-price"]').text());
						if(totalPrice < coinPrice){
							coinPrice = totalPrice;
						}
						$('[role="coupon-price"]').find(".price_r_num").text(coinPrice);
					}

				}
				conculatePrice();
			})
		})


		var validator = new Validator({
            element: '#order-create-form',
            triggerType: 'change',
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
				
                $('#order-create-btn').button('submiting').addClass('disabled');
            }
        });

		if($("input[name='payPassword'").length>0) {
			// validator.addItem({
			//     element: '[name=payPassword]',
			//     rule:'payPasswordRequired'
			// });
		}
 		
 		$('[role="coinNum"]').blur();
 		conculatePrice();

	}
});