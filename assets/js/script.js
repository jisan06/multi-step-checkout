jQuery(document).ready(function ($) {
    let currentStep = 1;

    function showStep(step) {
        $(".step-content").removeClass("active");
        $("#step-" + step).addClass("active");

        $(".step").removeClass("active");
        $(".step[data-step='" + step + "']").addClass("active");

        $("#prevStep").toggle(step > 1);
        $("#nextStep").toggle(step > 1 && step < 3);
    }

    function goToNextStep() {
        if (currentStep < 3) {
            currentStep++;
            showStep(currentStep);
        }
    }

    $(".step").click(function () {
        let clickedStep = $(this).data("step");
        if (clickedStep > 1 && currentStep === 1) return;
        currentStep = clickedStep;
        showStep(currentStep);
    });

    $("#prevStep").click(function () {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    $(".login-tab").click(function () {
        $(".login-tab").removeClass("active");
        $(this).addClass("active");

        $(".login-content").removeClass("active");
        $($(this).data("target")).addClass("active");
    });

    // Handle OTP sending
    $("#send-otp").click(function () {
        let mobileNumber = $("#mobile-number").val().trim();
        if (mobileNumber === "") {
            alert("Please enter your mobile number.");
            return;
        }

        // Simulate OTP sending (Replace with actual AJAX request)
        alert("OTP sent to " + mobileNumber);

        // Show OTP input field
        $("#otp-section").show();
    });

    // Handle OTP verification and login
    $("#verify-otp").click(function () {
        let otpCode = $("#otp-code").val().trim();
        if (otpCode === "") {
            alert("Please enter the OTP code.");
            return;
        }

        // Check against the dummy OTP code '123'
        if (otpCode === "123") {
            alert("OTP verified successfully. Logging in...");
            // Go to next step after successful login
            goToNextStep();
        } else {
            alert("Invalid OTP. Please try again.");
        }
    });

    // Handle email login
    $("#login-email").click(function () {
        let email = $("#email-address").val().trim();
        if (email === "") {
            alert("Please enter your email.");
            return;
        }

        // Simulate email login (Replace with AJAX request)
        alert("Logging in with email: " + email);

        // Go to next step after login
        goToNextStep();
    });
});

jQuery(document).ready(function($) {
    $('.shipping-method input[type="radio"]').on('change', function() {
        // Hide all shipping fields first
        $('.shipping-fields').hide();

        // Show fields for the selected shipping method
        $(this).closest('.shipping-method').next('.shipping-fields').show();
    });
});

jQuery(document).ready(function($) {
    $('#toggleCoupon').on('click', function() {
        $('#couponContent').toggle();
    });

    $('#applyCoupon').on('click', function() {
        // Assume you have logic here to calculate discount
        var discount = 10; // For example purposes
        $('#discountAmount').text('$' + discount.toFixed(2));
        $('#discountMessage').show();
    });
});