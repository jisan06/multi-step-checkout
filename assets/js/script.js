jQuery(document).ready(function ($) {
    let currentStep = msc_core.is_logged_in ? 2 : 1;

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

    $("#nextStep").click(function () {
        if (currentStep === 2) {
            currentStep++;
            showStep(currentStep);
            $('#backButton').hide();
        }
    });

    $("#backButton").click(function () {
       if (currentStep === 2) {
            $('.cart-items-wrap').show();
            $('#coupon_wrap').hide();
            $('.shipping-fields').hide();
        }
        $(this).hide()
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
        if (mobileNumber === "") {
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
            if (cookie.indexOf(name) === 0) {
                return cookie.substring(name.length, cookie.length);
            }
        }
        return null;
    }

    // Handle OTP verification and login
    $("#verify_otp").click(function () {
        let otpCode = $("#otp_code").val().trim();
        let storedOTP = getOTPFromCookie();
        if (otpCode === "") {
            alert("Please enter the OTP code.");
            return;
        }

        // Check against the dummy OTP code '123'
        if (otpCode ===storedOTP) {
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
        if (email === "") {
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
        if (otpCode === "") {
            alert("Please enter the OTP code.");
            return;
        }

        // Check against the dummy OTP code '123'
        if (otpCode ===storedOTP) {
            let email = $("#email_address").val().trim(); // Fixed ID selector to match input field
            if (email === "") {
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
});

jQuery(document).ready(function($) {
    $('input[name="shipping_method"]').on('click', function() {
        // Hide elements
        $('.shipping-fields').hide();
        $('.cart-items-wrap').hide();
        $('#coupon_wrap').hide();

        // Get the selected method ID
        var selectedMethodId = $(this).val();

        // Show the corresponding shipping fields
        $('.shipping-methods-details .shipping-fields[data-method-id="' + selectedMethodId + '"]').show();
        $('#backButton').show();

        // Trigger WooCommerce to update the cart total via AJAX
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
    });
});


//Coupon section
jQuery(document).ready(function ($) {
    $('#toggleCoupon').on('click', function () {
        $('.shipping-fields').hide();
        $('.cart-items-wrap').hide();
        $('#coupon_wrap').show();
        $('#backButton').show();
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
                } else {
                    alert(response.message);
                }
            }
        });
    });

    // Remove Coupon
    $('#removeCoupon').on('click', function () {
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
                }
            }
        });
    });
});

//place order
jQuery(document).ready(function($) {
    $('#placeOrderButton').on('click', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Collect the selected payment method
        var selectedPaymentMethod = $('input[name="payment_method"]:checked').val();
        if (!selectedPaymentMethod) {
            alert('Please select a payment method.');
            return;
        }

        // Collect shipping details
        var shippingMethod = $('input[name="shipping_method"]:checked').val();
        var shippingAddress = $('.shipping-address').val();
        var contactNumber = $('.shipping-number').val();

        // Collect additional fields
        var country = $('#calc_shipping_country').val();
        var state = $('#calc_shipping_state').val();
        var receiptDate = $('.shipping-receipt-date').val();
        var deliveryTime = $('.shipping-time').val();

        // Additional data you may need from Step 2 and Step 3
        var couponCode = $('#couponCode').val(); // Collect the coupon code
        var orderData = {
            action: 'place_order',
            payment_method: selectedPaymentMethod,
            shipping_method: shippingMethod,
            shipping_address: shippingAddress,
            country: country, // Include country
            state: state, // Include state
            contact_number: contactNumber,
            coupon_code: couponCode,
            receipt_date: receiptDate, // Include receipt date
            delivery_time: deliveryTime,
        };

        // Send an AJAX request to place the order
        $.ajax({
            url: msc_core.ajaxurl, // WooCommerce AJAX URL
            type: 'POST',
            data: orderData,
            beforeSend: function() {
                $('#placeOrderButton').text('Placing Order...'); // Change button text while processing
            },
            success: function(response) {
                if (response.success) {
                    // Hide the form and show success message
                    $('.payment-methods-container').hide();
                    $('.msc-checkout-form .order-success').show();
                } else {
                    alert(response.data.message); // Show error message
                }
            },
            complete: function() {
                $('#placeOrderButton').text('Place Order'); // Reset button text
            }
        });
    });
});




