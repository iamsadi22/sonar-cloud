!function(e){function r(r){var o=localStorage.getItem("crlms_order_id");e.ajax({type:"POST",url:crlms_checkout_params.ajax_url,data:{action:"crlms_after_paypal_payment_redirection",paypal_order_id:r,original_order:o},success:function(e){localStorage.removeItem("crlms_paypal_order_id"),localStorage.removeItem("crlms_order_id"),localStorage.removeItem("crlms_payment_method")},error:function(e,r,o){console.log(e.responseText)}})}function o(r){var o=localStorage.getItem("crlms_order_id");e.ajax({type:"POST",url:crlms_checkout_params.ajax_url,data:{action:"crlms_after_paypal_subscription_redirection",subscription_id:r,original_order:o},success:function(e){},error:function(e,r,o){console.log(e.responseText)}})}e((function(){({init:function(){e(document.body).on("click","a.showlogin",this.show_login_form).on("submit","#creator-lms-checkout-form",this.submit)},show_login_form:function(){return e(".crlms-checkout-login").slideToggle(),!1},submit:function(r){r.preventDefault();var o=e(this);if(o.find('button[type="submit"]'),o.is(".processing"))return!1;o.addClass("processing"),e.ajax({type:"POST",url:crlms_checkout_params.ajax_url,data:o.serialize(),dataType:"json",success:function(e){o.removeClass("processing"),"pending"===e.status&&void 0!==e.paypal&&(void 0!==e.paypal.subscriptionID?(localStorage.setItem("crlms_payment_method","paypal_subscription"),localStorage.setItem("crlms_paypal_subscription_id",e.paypal.subscriptionID),localStorage.setItem("crlms_order_id",e.order),window.location.href=e.paypal.approval_url):(localStorage.setItem("crlms_payment_method","paypal"),localStorage.setItem("crlms_paypal_order_id",e.paypal.orderID),localStorage.setItem("crlms_order_id",e.order),window.location.href=e.paypal.approval_url))}})}}).init()})),jQuery(document).ready((function(e){var a=new URLSearchParams(window.location.search),t=a.get("PayerID"),c=a.get("token"),s=a.get("subscription_id"),l=a.get("ba_token"),i=localStorage.getItem("crlms_paypal_order_id");i&&t&&c&&r(i),s&&l&&c&&o(s),e(".crlm_purchase").on("click",(function(r){r.preventDefault();var o=e(this).data("plan"),a=e(this).data("membership");e.ajax({type:"POST",url:crlms_checkout_params.ajax_url,data:{action:"creator_lms_purchase_membership",nonce:crlms_checkout_params.nonce,membership_id:a,plan:o},success:function(e){"success"===e.status&&(window.location=e.redirect_url)},error:function(e,r,o){console.log(e.responseText)}})}))}))}(jQuery);