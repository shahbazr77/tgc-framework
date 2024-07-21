jQuery(document).ready(function($) {

    if ($(".my-membership-detail-user-membership-expires").length > 0) {

        var expiresElement = document.querySelector('.my-membership-detail-user-membership-expires td');
        if (expiresElement) {
            expiresElement.textContent = 'Renew';
        }

    }


    if($(".our-winners").length>0) {
        $('.all_time_winner').owlCarousel({
            loop:true,
            margin:10,
            nav:true,
            responsive:{
                320:{
                    items:1
                },
                800:{
                    items:2
                },
                1000:{
                    items:3
                }
            }
        })
    }
    if($(".upcoming-giveaway-section").length>0) {
        $('.upcoming-giveaway').owlCarousel({
            loop:true,
            margin:10,
            nav:true,
            responsive:{
                320:{
                    items:1
                },1000:{
                    items:2
                }
            }
        })
    }
    if (typeof (tgc_strings) !== 'undefined' && tgc_strings !== null) {
      // console.log(tgc_strings.checkout_url);
        if ($(".preloader-site").length) {
            $(window).on('load', function () {
                $('.preloader-site').fadeOut();
            });
        }
        if ($(".cf-off-canvas").length > 0) {
            $(".openNav").click(function () {
                $("#mySidenav").addClass("side-active");
                //$("#mySidenav").css("width", 500);
                $("#main-order").hide("slow");
            })
            $(".closebtn").click(function () {
                $("#mySidenav").removeClass("side-active");
               // $("#mySidenav").css("width", 0);
               // $("#mySidenav").css("width", "0vw !important");
                $("#main-order").show("slow");
            })
            $(".float-right").click(function () {
                $(".res-order-box").toggle(600);
            });
        }
        if ($(".cf-off-canvas").length > 0) {
            $(document).on('click', '.cf-off-canvas .quantity input', function () {
                var item_key = $(this).attr('name').replace(/cart\[([\w]+)\]\[qty\]/g, "$1");
                var item_quantity = $(this).val();
                var currentVal = parseFloat(item_quantity);
                $.ajax({
                    type: 'POST',
                    url: tgc_strings.ajax_url,
                    data: {
                        action: 'update_item_from_cart',
                        item_key: item_key,
                        quantity: currentVal
                    },
                    success: function (response) {
                        $(".sidenav").LoadingOverlay("show");
                        $('.cart-count').html(response.data.res_filter_data);
                        $(".sidenav").LoadingOverlay("hide");
                    }
                });

            });
        }
        $(document).on('click', '.counter .text-danger', function () {
            var product_id = $(this).attr("data-product_id");
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: tgc_strings.ajax_url,
                data: {
                    action: "product_remove",
                    product_id: product_id
                }, success: function (response) {
                    if(response.data.cart_status==true){
                        $(".cf-off-canvas").addClass("tgc-empty-cart-custom");
                    }
                    $(".sidenav").LoadingOverlay("show");
                    $('.cart-count').html(response.data.res_filter_data);
                    $(".sidenav").LoadingOverlay("hide");
                }
            });
            return false;
        })
        $(document).ready(function () {
            $(document).on('submit', '.counter .woocommerce-cart-form', function (e) {
                e.preventDefault();
                var code = $('input#coupon_code').val();
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: tgc_strings.ajax_url,
                    data: {
                        action: "spyr_coupon_redeem_handler",
                        coupon_code: code
                    }, success: function (response) {
                        if (true === response.success) {
                            var body = document.body;
                            body.classList.remove("tgc-empty-cart-custom");
                            $(".sidenav").LoadingOverlay("show");
                            Notiflix.Notify.Success(toest_token_success_applied);
                            $('.cart-count').html(response.data.res_filter_data);
                            $(".sidenav").LoadingOverlay("hide");
                        } else {
                            if (response.data.result === "isempty") {
                                $(".sidenav").LoadingOverlay("show");
                                Notiflix.Notify.Failure(toest_token_field_empty);
                                $(".sidenav").LoadingOverlay("hide");
                            } else if (response.data.result === "isinvalid") {
                                $(".sidenav").LoadingOverlay("show");
                                Notiflix.Notify.Failure(toest_token_invalid);
                                $(".sidenav").LoadingOverlay("hide");
                            }
                        }
                    }
                });
                return false;
            });
        });
        $(document).ready(function ($) {
            $('.single_add_to_cart_button').on('click', function (e) {
                e.preventDefault();
                //$("#mySidenav").css("width", 500);
                $("#mySidenav").addClass("side-active");
                $("#main-order").hide("slow");
                var thisbutton = $(this);
                var simple_id = thisbutton.val();
                var custom_action="";
                if(simple_id=="" || simple_id==undefined ){
                    var form = thisbutton.closest('form.cart');
                    var my_val = form.serialize();
                    var data = {
                        action: 'ql_woocommerce_ajax_add_to_cart', // Make sure this is a valid AJAX action
                        my_val: my_val // Corrected to include the key-value pair
                    };
                }else {
                    var form = thisbutton.closest('form.cart');
                    var my_quentity = form.serialize();
                    var data = {
                        action: 'simple_product_on_single_page', simple_id,my_quentity
                    }
                }

                $.ajax({
                    type: 'post',
                    url: tgc_strings.ajax_url,
                    data: data,
                    beforeSend: function () {
                        thisbutton.removeClass('added').addClass('loading');
                    },
                    complete: function () {
                        thisbutton.addClass('added').removeClass('loading');
                    },
                    success: function (response) {
                        if (response.error && response.product_url) {
                            window.location = response.product_url;
                        } else {
                            // Assuming that you want to update fragments and cart_hash
                            var body = document.body;
                            body.classList.remove("tgc-empty-cart-custom");
                            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, thisbutton]);
                        }
                    }
                });
            });

        });


        jQuery(document).on('click', '.cf-off-canvas .plus', function(e) {
            e.preventDefault();
            var closestInput = $(this).closest('div').find('input[type="number"]');
            var inputValue = closestInput.val();
            var currentVal = parseInt(inputValue);
            if (!isNaN(currentVal)) {
                closestInput.val(currentVal + 1);
                var item_key = $(closestInput).attr('name').replace(/cart\[([\w]+)\]\[qty\]/g, "$1");
                var item_quantity = closestInput.val();
                var currentVal = parseFloat(item_quantity);
                $.ajax({
                    type: 'POST',
                    url: tgc_strings.ajax_url,
                    data: {
                        action: 'update_item_from_cart',
                        item_key: item_key,
                        quantity: currentVal
                    },
                    success: function (response) {
                        $(".sidenav").LoadingOverlay("show");
                        $('.cart-count').html(response.data.res_filter_data);
                        $(".sidenav").LoadingOverlay("hide");
                    }
                });
            }
        });
        jQuery(document).on('click', '.cf-off-canvas .minus', function(e) {
                e.preventDefault();
               // var input = jQuery('.quantity .form-control').val();
                var closestInput = $(this).closest('div').find('input[type="number"]');
                var inputValue = closestInput.val();
                var currentVal = parseInt(inputValue);
                if (!isNaN(currentVal)) {
                    closestInput.val(currentVal - 1);
                    var item_key = $(closestInput).attr('name').replace(/cart\[([\w]+)\]\[qty\]/g, "$1");
                    var item_quantity =  closestInput.val();
                    var currentVal = parseFloat(item_quantity);
                    $.ajax({
                        type: 'POST',
                        url: tgc_strings.ajax_url,
                        data: {
                            action: 'update_item_from_cart',
                            item_key: item_key,
                            quantity: currentVal
                        },
                        success: function (response) {
                            $(".sidenav").LoadingOverlay("show");
                            $('.cart-count').html(response.data.res_filter_data);
                            $(".sidenav").LoadingOverlay("hide");
                        }
                    });

                }
        });

    }
});




