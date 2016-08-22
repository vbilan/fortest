if(jQuery) {
    (function($) {
        $(document).ready(function() {
            // IWD OSC
            if(typeof(IWD) !== 'undefined' && typeof(IWD.OPC) !== 'undefined') {
                $(document).delegate('#use_customer_balance', 'change', function() {
                    IWD.OPC.Checkout.showLoader();
                    var request = new Ajax.Request(
                        payment.storeCreditUsageToggleUrl,
                        {
                            method:'post',
                            onComplete: function() {
                                IWD.OPC.Checkout.hideLoader();
                            },
                            onSuccess: function() {
                                IWD.OPC.Checkout.pullPayments();
                            },
                            onFailure: function() {
                                alert("An error occurred while trying to save your store credit usage for this checkout. Please contact our support team.");
                            },
                            parameters: Form.serialize(payment.form)
                        }
                    );
                });
            }
            // OSC(.com)
            if(typeof get_save_billing_function !== 'undefined') {
                var checker = setInterval(function(){
                    if($('#p_method_free').length > 0) {
                        if(!$('#p_method_free').is(':checked')) {
                            $('#p_method_free').click();
                        }
                        $('#p_method_free').parent('dt').siblings().hide();
                    } else {
                        $('#p_method_free').parent('dt').siblings().show();
                    }
                }, 100);

                $(document).delegate('#use_customer_balance', 'change', function() {
                    $('#customerbalance_placer').append('<div class="loading-ajax" id="customerbalance_loading">&nbsp;</div>');
                    var params = 'payment%5Buse_customer_balance%5D='+ ($('#use_customer_balance').is(':checked') ? 1 : 0);
                    var request = new Ajax.Request(
                        payment.storeCreditUsageToggleUrl,
                        {
                            method:'post',
                            onComplete: function() {
                            },
                            onSuccess: function() {
                                get_save_billing_function(payment.idevOscSaveBillingUrl, payment.idevOscSetMethodsSeparateUrl, true, true)();
                            },
                            onFailure: function() {
                                alert("An error occurred while trying to save your store credit usage for this checkout. Please contact our support team.");
                            },
                            parameters: params
                        }
                    );
                });
            }

            // Uni Opcheckout
            if (typeof OpcheckoutDialog !== "undefined") {
                console.log("OpcheckoutDialog: "+ OpcheckoutDialog);
                $(document).delegate('#use_customer_balance', 'change', function() {
                    checkout.setLoadWaiting(true);
                    var request = new Ajax.Request(
                        payment.storeCreditUsageToggleUrl,
                        {
                            method:'post',
                            onComplete: function() {
                                checkout.setLoadWaiting(false);
                            },
                            onSuccess: function() {
                                reviewStep.getReview();
                                payment.loadPaymentMethods();
                            },
                            onFailure: function() {
                                alert("An error occurred while trying to save your store credit usage for this checkout. Please contact our support team.");
                            },
                            parameters: Form.serialize(paymentForm)
                        }
                    );
                });
            }

        });
    })(jQuery);
}
