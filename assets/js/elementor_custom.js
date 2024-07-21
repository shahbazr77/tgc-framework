(function ($) {
    $(document).ready(function () {
        var lucy_date_to=$("#todateval").val();
        var lucy_hours_to=$("#tohours").val();
        const givenDateTime = new Date(lucy_date_to);
        const endDate = new Date(givenDateTime.getTime() + lucy_hours_to * 3600000);
        function updateCountdown() {
            var now = new Date();
            var timeLeft = endDate - now;
            if (timeLeft > 0) {
                var days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                var hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                // Update your HTML elements with the countdown values
                $('.days-counter').text(days);
                $('.hours-counter').text(hours);
                $('.minuts-counter').text(minutes);
                $('.secs-counter').text(seconds);
                setTimeout(updateCountdown, 1000);
            } else {

                $(".giveawy-couter-container").hide();

            }
        }

        // Initial call to start the countdown
        updateCountdown();
    });
})(jQuery);