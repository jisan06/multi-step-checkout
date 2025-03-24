let locationRegions = {}
let selectedRegion  = {}
let selectedDistrict  = {}
let selectedAddress  = {}
loadLocationJSON('english')
async function loadLocationJSON(lang) {
    try {
        const response = await fetch('/wp-content/plugins/multi-step-checkout/lib/locations.json');

        // Parse the JSON content
        let jsonParse = await response.json();
        locationRegions = jsonParse[lang]?.regions;
    } catch (error) {
        console.error('Error loading JSON:', error);
    }
}
(function ($) {
    let otpBtn = $('.send-otp-btn');
    window.recaptchaCallback = function () {
        otpBtn.prop('disabled', false); // Enable button
        otpBtn.removeClass('mouse-disable');
    };

    window.recaptchaExpiredCallback = function () {
        otpBtn.prop('disabled', true); // Disable the button
        otpBtn.addClass('mouse-disable'); // Add the disable class
    };
})(jQuery);

jQuery(document).ready(function ($) {
    $('.select2').select2({
        width: '100%',
        placeholder: "Select an option",
        allowClear: false
    });
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
        let ShippingField = $(this).parents('.shipping-methods-details:first').find('.shipping-fields:visible')
        let ShippingMethod = ShippingField.attr('data-method-id')
        if( ShippingMethod === 'local_pickup' ) {
            let region = ShippingField.find('#shipping_region').val();
            let district = ShippingField.find('#shipping_district').val();
            let shippingAddress = ShippingField.find('#shipping_address').val();
            if( region === ''){
                alert('選擇地區')
                return false;
            }else if( district === ''){
                alert('選擇地區')
                return false;
            }else if( shippingAddress === ''){
                alert('選擇地址')
                return false;
            }
        }else {
            let shippingAddress = ShippingField.find('.shipping-address').val();
            let shippingNumber = ShippingField.find('.shipping-number').val();
            let shippingPerson = ShippingField.find('.shipping-person').val();
            if( shippingAddress === ''){
                alert('需要送貨地址')
                return false;
            }else if( shippingNumber === ''){
                alert('需要聯絡電話')
                return false;
            }else if( shippingPerson === ''){
                alert('需要聯絡人')
                return false;
            }
        }
        cartPage()
    });

    $(".login-tab").click(function () {
        $(".login-tab").removeClass("active");
        $(this).addClass("active");

        $(".login-content").removeClass("active");
        $($(this).data("target")).addClass("active");
    });

    function otpTimer(otpBtn, otpBtnText) {
        let timerInterval;
        let timeLeft = 60; // 1 minute (60 seconds)
        let otpTimer = $('#otp_timer');

        // Show the timer div
        otpTimer.show();

        // Start the countdown
        timerInterval = setInterval(function() {
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            otpTimer.text(minutes + 'm ' + (seconds < 10 ? '0' : '') + seconds + 's');

            if (timeLeft <= 0) {
                clearInterval(timerInterval);  // Stop the timer
                otpBtnEnable(otpBtn, otpBtnText)
                otpTimer.hide(); // Hide the timer
            }

            timeLeft--;
        }, 1000); // 1000ms = 1 second
    }

    function otpBtnEnable(otpBtn, otpBtnText) {
        otpBtn.removeClass('mouse-disable');
        otpBtn.prop('disabled', false).text(otpBtnText);
    }

    // Handle OTP sending for mobile
    $("#send_otp").click(function () {
        let that = $(this);
        let otpBtnText = that.text();
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
        let expiryTime = new Date(new Date().getTime() + 60 * 1000).toUTCString(); // 1 minute expiry

        that.addClass('mouse-disable');
        that.prop('disabled', true).text(otpBtnText + '...');
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
                    that.text(otpBtnText);
                    otpTimer(that, otpBtnText);
                    alert('otp is send to your mobile')
                } else {
                    otpBtnEnable(that, otpBtnText);
                    alert('Error: ' + response.data); // Error message
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                otpBtnEnable(that, otpBtnText);
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
            let mscCountryCC = $("#msc_country_cc").val().trim();
            let formatMobile = mscCountryCC + '' + mobile;
            $.ajax({
                url: msc_core.ajaxurl,
                type: "POST",
                data: {
                    action: "msc_mobile_login",
                    countryCode: mscCountryCC,
                    mobile: mobile,
                    formatMobile: formatMobile,
                    otp: otpCode,
                    security: msc_core.nonce
                },
                success: function (response) {
                    if (response.success) {
                        document.cookie = "otp=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
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
        let expiryTime = new Date(new Date().getTime() + 180 * 1000).toUTCString(); // 3 minute expiry

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
                        document.cookie = "otp=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
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


    //Shipping code
    $('#toggleShipping').on('click', function() {
        shippingMethodShow();
    });
    // $('.shipping-methods .next-step').on('click', function() {
    //     shippingMethodFields();
    // })

    $('input[name="shipping_method"]').on('click', function() {
        // $(".shipping-methods .next-step").removeClass('disabled');
        $("#placeOrderButton").removeClass('disabled');
        var shipLabel = $(this).parents('.shipping-method:first').find('.shipping-label').text()
        $('#selectedShippingMethod').text(shipLabel);
        shippingMethodFields();
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

    $('.shipping_region').on('change', function () {
        let regionVal = $(this).val();
        const districtDropdown = $('#shipping_district');
        districtDropdown.empty();

        // Add a default option
        districtDropdown.append('<option value="">Select District</option>');

        selectedRegion = locationRegions.find(region => region.name === regionVal);

        if (selectedRegion && selectedRegion.districts) {
            // Populate districts based on the selected region
            selectedRegion.districts.forEach(district => {
                const option = $('<option></option>');  // jQuery way to create an option element
                option.val(district.name);
                option.text(district.name);
                districtDropdown.append(option);  // Append option to the district dropdown
            });
        }
    })

    $('.shipping_district').on('change', function () {
        let districtVal = $(this).val();
        selectedDistrict = selectedRegion ? selectedRegion.districts.find(district => district.name === districtVal) : null;

        const addressDropdown = $('#shipping_address_wrap .shipping_address');
        addressDropdown.empty().append('<option value="">Select Address</option>');

        if (selectedDistrict) {
            selectedDistrict.stores.forEach(store => {
                let storeDetails = `${store.code} - ${store.address} (Mon-Fri: ${store.business_hours.mon_to_fri}, Sat-Sun: ${store.business_hours.sat_sun_public_holidays})`;
                addressDropdown.append(`<option value="${store.code}">${storeDetails}</option>`);
            });
        }
    })

    $('#shipping_address_wrap #shipping_address').on('change', function () {
        selectedAddress = $("#shipping_address_wrap #shipping_address option:selected").val();
    })

    //Shipping code end


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
    $('.apply-coupon-checkmark').on('change', function () {
        let that = $(this);
        var coupon_code = that.val();
        if( that.is(':checked') ) {
            $('.apply-coupon-checkmark').not(this).prop('checked', false);
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
        }else {
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
        }
    });

    // Remove Coupon
    // $('#removeCoupon').on('click', function () {
    //     let button = $(this)
    //     let buttonDefault = $(this).text()
    //     button.text('Removing...')
    //     $.ajax({
    //         url: msc_core.ajaxurl,
    //         type: 'POST',
    //         data: {
    //             action: 'remove_all_coupons'
    //         },
    //         success: function (response) {
    //             if (response.success) {
    //                 $('#discountAmount').text('$0.00');
    //                 $('.msc-nav-total').html(response.data.total); // Update total cart amount
    //                 $('#coupon_summary').hide();
    //                 $('#appliedCoupon').hide();
    //                 $('.apply-button').show();
    //                 button.text(buttonDefault)
    //             }
    //         }
    //     });
    // });

//place order
    $('#placeOrderButton').on('click', function(e) {
        e.preventDefault(); // Prevent default form submission

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
        var shippingAddress = shippingContainer.find('#shipping_address').val();
        var contactNumber = shippingContainer.find('.shipping-number').val();
        var contactPerson = shippingContainer.find('.shipping-person').val();
        var deliveryNote = shippingContainer.find('.delivery-note').val();

        var country = shippingContainer.find('#shipping_country').val();
        var region = shippingContainer.find('#shipping_region').val();
        var district = shippingContainer.find('#shipping_district').val();

        if( shippingMethod === 'local_pickup' ) {
            shippingAddress = selectedAddress;
        }
        var couponCode = $('#couponCode').val(); // Get coupon code

        // Order data object
        var orderData = {
            action: 'place_order',
            shipping_method: shippingMethod,
            shipping_slug: shipping.data('slug'),
            shipping_title: shippingTitle,
            shipping_cost: shippingCost,

            shipping_address: shippingAddress ?? '',
            contact_number: contactNumber ?? '',
            contact_person: contactPerson ?? '',
            delivery_note: deliveryNote ?? '',

            country: country ?? '',
            region: region ?? '',
            district: district,

            coupon_code: couponCode,
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
            let currentValue = parseInt(quantityInput.val(), 10);
            let val = currentValue - 1;
            if( val < 0 ) {
                val = 0;
            }
            quantityInput.val(val);
            updateAddToCartButton();
        }
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
        let requiredQty = 0;
        $('.woosb-bundle .woosb-quantity .woosb-quantity-input').each(function() {
            let quantity = parseInt($(this).val());
            totalQty += quantity;
        });
        $('.elementor-menu-cart__product:first .woosb-quantity .woosb-quantity-input').each(function() {
            let quantity = parseInt($(this).val());
            totalMiniQty += quantity;
        });
        if( totalMiniQty < requiredQty ) {
            let addMoreQty = requiredQty - totalMiniQty;
            $('.msc-mini-add-more').text(addMoreQty)
            $('.msc-mini-add-more-wrap').show()
        }else {
            $('.msc-mini-add-more-wrap').hide()
        }
        if (totalQty > requiredQty/* || cartCount >= requiredQty*/) {
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
                $('#woosb-multi-mini-add-to-cart').text('??...').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert("Bundles added to cart!");
                    $(document.body).trigger('wc_fragment_refresh');
                } else {
                    alert("Error adding bundles.");
                }
                $('#woosb-multi-mini-add-to-cart').text('????').prop('disabled', false);
            }
        });
    });

});