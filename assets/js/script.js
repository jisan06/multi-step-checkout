jQuery(document).ready(function ($) {
    let currentStep = $('.msc-current-step').val() ?? 1;

    // Initial display
    showStep(currentStep);
    function showStep(step) {
        $(".step-content").removeClass("active").addClass("hidden");
        $("#step-" + step).removeClass("hidden").addClass("active");

        $(".step").removeClass("active");
        $(".step[data-step='" + step + "']").addClass("active");

        // Disable all steps except the active one
        $(".step").not(":nth-child(" + step + ")").addClass("disabled");
        $(".step").not(":nth-child(" + currentStep + ")").removeClass("disabled");
    }

    $("#backButton").click(function () {
        if (currentStep == 2) {
            if ($('.shipping-methods-details .shipping-fields').is(':visible')) {
                shippingMethodShow();
            }else {
                cartPage()
            }
        }
    });

    $('.confirm-data').click(function () {
        cartPage()
    });

    $(".login-tab").click(function () {
        $(".login-tab").removeClass("active");
        $(this).addClass("active");

        $(".login-content").removeClass("active");
        $($(this).data("target")).addClass("active");
    });

    // Handle OTP sending for mobile
    $("#send_otp").click(function () {
        let mobileNumber = $("#mobile_number").val().trim();
        let mscCountryCC = $("#msc_country_cc").val().trim();
        let formatMobile = mscCountryCC + '' + mobileNumber;
        if (mobileNumber == "") {
            alert("Please enter your mobile number.");
            return;
        }
        // Remove any existing OTP
        document.cookie = "otp=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

        // Generate a 6-digit OTP
        let otp = Math.floor(100000 + Math.random() * 900000);
        let expiryTime = new Date(new Date().getTime() + 180 * 1000).toUTCString(); // 180s expiry

        $.ajax({
            url: msc_core.ajaxurl, // Use localized script variable
            method: 'POST',
            data: {
                action: 'send_otp',
                mobile_number: formatMobile,
                otp: otp,
            },
            success: function (response) {
                if (response.success) {
                    // Store OTP in JavaScript cookie
                    document.cookie = "otp=" + otp + "; expires=" + expiryTime + "; path=/;";
                    alert('otp is send to your mobile')
                } else {
                    alert('Error: ' + response.data); // Error message
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus);
            }
        });
    });

    function getOTPFromCookie() {
        let name = "otp=";
        let decodedCookies = decodeURIComponent(document.cookie);
        let cookiesArray = decodedCookies.split(';');

        for (let i = 0; i < cookiesArray.length; i++) {
            let cookie = cookiesArray[i].trim();
            if (cookie.indexOf(name) == 0) {
                return cookie.substring(name.length, cookie.length);
            }
        }
        return null;
    }

    // Handle OTP verification and login
    $("#verify_otp").click(function () {
        let otpCode = $("#otp_code").val().trim();
        let storedOTP = getOTPFromCookie();
        if (otpCode == "") {
            alert("Please enter the OTP code.");
            return;
        }

        // Check against the dummy OTP code '123'
        if (otpCode ==storedOTP) {
            let mobile = $("#mobile_number").val();
            $.ajax({
                url: msc_core.ajaxurl,
                type: "POST",
                data: {
                    action: "msc_mobile_login",
                    mobile: mobile,
                    otp: otpCode,
                    security: msc_core.nonce
                },
                success: function (response) {
                    if (response.success) {
                        alert("You are logged in");
                        window.location.reload();
                    }else {
                        alert('Login failed, Please try again!')
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus);
                }
            });
        } else {
            alert("Invalid OTP. Please try again.");
        }
    });

    // Handle OTP sending for email
    $("#send_otp_email").click(function () {
        let email = $("#email_address").val().trim();
        if (email == "") {
            alert("Please enter your email address.");
            return;
        }
        // Remove any existing OTP
        document.cookie = "otp=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

        // Generate a 6-digit OTP
        let otp = Math.floor(100000 + Math.random() * 900000);
        let expiryTime = new Date(new Date().getTime() + 180 * 1000).toUTCString(); // 180s expiry

        $.ajax({
            url: msc_core.ajaxurl, // Use localized script variable
            method: 'POST',
            data: {
                action: 'send_otp_email',
                email: email,
                otp: otp,
            },
            success: function (response) {
                if (response.success) {
                    // Store OTP in JavaScript cookie
                    document.cookie = "otp=" + otp + "; expires=" + expiryTime + "; path=/;";
                    alert('otp is send to your email')
                } else {
                    alert('Error: ' + response.data); // Error message
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus);
            }
        });
    });

    // Handle email login
    $("#login_email").click(function () {
        let otpCode = $("#email_otp_code").val().trim();
        let storedOTP = getOTPFromCookie();
        if (otpCode == "") {
            alert("Please enter the OTP code.");
            return;
        }

        // Check against the dummy OTP code '123'
        if (otpCode ==storedOTP) {
            let email = $("#email_address").val().trim(); // Fixed ID selector to match input field
            if (email == "") {
                alert("Please enter your email.");
                return;
            }

            $.ajax({
                url: msc_core.ajaxurl, // Use localized script variable
                method: 'POST',
                data: {
                    action: 'email_login',
                    email: email,
                    otp: otpCode,
                    security: msc_core.nonce // Optionally include a nonce for security
                },
                success: function (response) {
                    if (response.success) {
                        alert("You are logged in");
                        window.location.reload();
                    } else {
                        alert('Login failed: ' + response.data); // Show error message
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus);
                }
            });
        }
    });


    $('#toggleShipping').on('click', function() {
        shippingMethodShow();
    });
    $('.shipping-methods .next-step').on('click', function() {
        shippingMethodFields();
    })

    $('input[name="shipping_method"]').on('change', function() {
        $(".shipping-methods .next-step").removeClass('disabled');
        $("#placeOrderButton").removeClass('disabled');
        var shipLabel = $(this).parents('.shipping-method:first').find('.shipping-label').text()
        $('#selectedShippingMethod').text(shipLabel)
    });

    function cartPage() {
        $('.cart-items-wrap').show();
        $('.toggleShipWrap').show();
        $('#coupon_wrap').hide();
        $('.shipping-fields').hide();
        $('.msc-nav').show();
        $(".backButton").hide()
        $('.confirm-data').hide()
        $('.shipping-methods').hide();
    }
    function shippingMethodShow() {
        $('.toggleShipWrap').hide();
        $('#backButton').show();
        $('.shipping-methods').show();
        $('.shipping-fields').hide();
        $('.cart-items-wrap').hide();
        $('#coupon_wrap').hide();
        $('.msc-nav').hide();
        $('.confirm-data').hide()
    }

    function shippingMethodFields() {
        $('.confirm-data').show();
        $('.shipping-methods').hide();
        $('.shipping-fields').hide();
        var selectedMethodId = $('input[name="shipping_method"]:checked').val();
        $('.shipping-methods-details').show();
        $('.shipping-methods-details .shipping-fields[data-method-id="' + selectedMethodId + '"]').show();
        $.ajax({
            type: 'POST',
            url: msc_core.ajaxurl,
            data: {
                action: 'update_shipping',
                shipping_method: selectedMethodId,
            },
            success: function(response) {
                if (response.success) {
                    // Update the cart total dynamically
                    $('.msc-nav-total').html(response.data.total); // Update total cart amount
                }
            }
        });
    }


    //Coupon section
    $('#toggleCoupon').on('click', function () {
        $('.msc-nav').hide();
        $('.shipping-fields').hide();
        $('.cart-items-wrap').hide();
        $('.shipping-methods-details').hide();
        $('#coupon_wrap').show();
        $('#backButton').show();
        $('.confirm-data').show();
    });
    // Apply Coupon
    $('.apply-button').on('click', function () {
        var coupon_code = $(this).data('coupon');

        $.ajax({
            url: msc_core.ajaxurl,
            type: 'POST',
            data: {
                action: 'apply_coupon',
                coupon_code: coupon_code
            },
            success: function (response) {
                if (response.success) {
                    $('#discountAmount').html(response.data.discount);
                    $('.msc-nav-total').html(response.data.total); // Update total cart amount
                    $('#coupon_summary').show();
                    $('#appliedCoupon .coupon-amount').html(response.data.discount);
                    $('#appliedCoupon').show();
                    $('.apply-button').hide();
                } else {
                    alert(response.message);
                }
            }
        });
    });

    // Remove Coupon
    $('#removeCoupon').on('click', function () {
        let button = $(this)
        let buttonDefault = $(this).text()
        button.text('Removing...')
        $.ajax({
            url: msc_core.ajaxurl,
            type: 'POST',
            data: {
                action: 'remove_all_coupons'
            },
            success: function (response) {
                if (response.success) {
                    $('#discountAmount').text('$0.00');
                    $('.msc-nav-total').html(response.data.total); // Update total cart amount
                    $('#coupon_summary').hide();
                    $('#appliedCoupon').hide();
                    $('.apply-button').show();
                    button.text(buttonDefault)
                }
            }
        });
    });

//place order
    $('#placeOrderButton').on('click', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Collect the selected payment method
        // var selectedPaymentMethod = $('input[name="payment_method"]:checked').val();
        // if (!selectedPaymentMethod) {
        //     alert('Please select a payment method.');
        //     return;
        // }

        // Collect selected shipping method
        var shipping = $('input[name="shipping_method"]:checked');
        var shippingCost = shipping.data('cost');
        var shippingTitle = shipping.data('title');
        var shippingMethod = shipping.val();
        if (!shippingMethod) {
            alert('Please select a shipping method.');
            return;
        }

        // Find the corresponding shipping fields container
        var shippingContainer = $('.shipping-fields[data-method-id="' + shippingMethod + '"]');

        // Retrieve relevant values based on selected shipping method
        var shippingAddress = shippingContainer.find('.shipping-address').val();
        var contactNumber = shippingContainer.find('.shipping-number').val();
        var country = shippingContainer.find('#calc_shipping_country').val();
        var state = shippingContainer.find('#calc_shipping_state').val();
        var receiptDate = shippingContainer.find('.shipping-receipt-date').val();
        var deliveryTime = shippingContainer.find('.shipping-time').val();

        var couponCode = $('#couponCode').val(); // Get coupon code

        // Order data object
        var orderData = {
            action: 'place_order',
            // payment_method: selectedPaymentMethod,
            shipping_method: shippingMethod,
            shipping_title: shippingTitle,
            shipping_cost: shippingCost,
            shipping_address: shippingAddress ?? '',
            country: country ?? '',
            state: state ?? '',
            contact_number: contactNumber ?? '',
            coupon_code: couponCode,
            receipt_date: receiptDate,
            delivery_time: deliveryTime,
        };

        // Send AJAX request to place order
        $.ajax({
            url: msc_core.ajaxurl,
            type: 'POST',
            data: orderData,
            beforeSend: function() {
                $('#placeOrderButton').text('Placing Order...');
            },
            success: function(response) {
                if (response.success && response.data.redirect_url) {
                    window.location.href = response.data.redirect_url;
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus);
            }
        });
    });


    let cartAdded = false;
    let cartCount = Number(msc_core.cart_count);
    let totalQty = 0;
    //cart button update

    $(document).on('click',  '.woosb-quantity-minus', function () {
        let productId = $(this).attr("data-product-id");
        let quantityInput = $(this).parents('.woosb-price-quantity:first').find('.woosb-quantity-input');
        if (quantityInput.length) {
            let currentValue = parseInt(quantityInput.val(), 10) || 0;
            if (currentValue > 1) {
                quantityInput.val(currentValue - 1);
            }
        }
        updateAddToCartButton();
    });

    $(document).on('click',  '.woosb-quantity-plus', function () {
        let productId = $(this).attr("data-product-id");
        let quantityInput = $(this).parents('.woosb-price-quantity:first').find('.woosb-quantity-input');
        if (quantityInput.length) {
            let currentValue = parseInt(quantityInput.val(), 10) || 0;
            quantityInput.val(currentValue + 1);
        }
        updateAddToCartButton();
    });

    $(document).on('click',  '.elementor-menu-cart__toggle', function () {
        let totalCartQty = 0
        $('.woosb-bundle .woosb-quantity .woosb-quantity-input').each(function() {
            let quantity = parseInt($(this).val());
            totalCartQty += quantity;
        });
        totalCartQty += cartCount;
        $('.msc-mini-qty-wrap .msc-mini-qty').text(totalCartQty)
    });

    $(document).on('input',  '.woosb-quantity-input', function () {
        updateAddToCartButton();
    })

    // Update button state based on total quantity

    function updateAddToCartButton() {
        totalQty = 0
        let totalMiniQty = 0
        $('.woosb-quantity .woosb-quantity-input').each(function() {
            let quantity = parseInt($(this).val());
            totalQty += quantity;
        });
        $('.elementor-menu-cart__product:first .woosb-quantity .woosb-quantity-input').each(function() {
            let quantity = parseInt($(this).val());
            totalMiniQty += quantity;
        });
        if( totalMiniQty < 6 ) {
            let addMoreQty = 6 - totalMiniQty;
            $('.msc-mini-add-more').text(addMoreQty)
            $('.msc-mini-add-more-wrap').show()
        }else {
            $('.msc-mini-add-more-wrap').hide()
        }
        if (totalQty >= 6 || cartCount >= 6) {
            $('#woosb-multi-add-to-cart').removeClass('disabled');
        } else {
            $('#woosb-multi-add-to-cart').addClass('disabled');
        }
    }

    $(document).on('click',  '#woosb-multi-mini-add-to-cart', function () {
        var bundles = [];

        $('.elementor-menu-cart__product:first .woosb-quantity .woosb-quantity-input').each(function() {
            var bundleId = $(this).data('product-id');
            var quantity = parseInt($(this).val()) || 1;

            if (quantity > 0) {
                bundles.push({ id: bundleId, qty: quantity });
            }
        });

        if (bundles.length == 0) {
            alert("Please select at least one bundle.");
            return;
        }
        $.ajax({
            type: 'POST',
            url: msc_core.ajaxurl,
            data: {
                action: 'woosb_multi_add_to_cart',
                bundles: bundles
            },
            beforeSend: function() {
                $('#woosb-multi-mini-add-to-cart').text('添加...').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert("Bundles added to cart!");
                    $(document.body).trigger('wc_fragment_refresh');
                } else {
                    alert("Error adding bundles.");
                }
                $('#woosb-multi-mini-add-to-cart').text('立即下單').prop('disabled', false);
            }
        });
    });

});