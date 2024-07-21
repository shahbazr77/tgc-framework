jQuery(document).ready(function($) {
    if (typeof (tgc_admin_strings) !== 'undefined' && tgc_admin_strings !== null) {
        if($(".wrap-table-1").length>0) {
            document.getElementById('selectRandomRow').addEventListener('click', function () {
                jQuery(".tgcframe_loader").show();
                var rows = document.querySelectorAll('.tgc-tab1-admin-entries tbody tr');
                var randomRowIndex = Math.floor(Math.random() * rows.length);
                var selectedUserId = rows[randomRowIndex].querySelector('td:nth-child(2)').textContent;
                var gift_product_id=$("#givewaway-gift-id").val();
                $.ajax({
                    type: 'POST',
                    url: tgc_admin_strings.ajax_admin_url,
                    data: {
                        action: 'save_lucky_winners',
                        user_id: selectedUserId,
                        gift_id: gift_product_id,
                    },
                    success: function (response) {
                        if (true === response.success) {
                            if (response.data.old_user_status == 1) {
                                jQuery(".tgcframe_loader").hide();
                                $('.response-html').show();
                                $('.response-html .respons-heading').html(response.data.luckywinner + " " + response.data.staut_msg);
                                $('.response-html').fadeOut(2500);
                            } else {
                                jQuery(".tgcframe_loader").hide();
                                $('.response-html').show();
                                $('.response-html .respons-heading').html(response.data.luckywinner + " " + response.data.staut_msg);
                                $('.response-html').fadeOut(2500);
                            }
                        } else {
                            jQuery(".tgcframe_loader").hide();
                            $('.response-html').show();
                            $('.response-html .respons-heading').html(response.data.luckywinner + " " + response.data.staut_msg);
                            $('.response-html').fadeOut(2500);
                            //alert("something went wrong");
                        }

                    }
                });
            });
            document.getElementById('resetentryuser').addEventListener('click', function () {
                jQuery(".tgcframe_loader").show();
                var rows = document.querySelectorAll('.tgc-tab1-admin-entries tbody tr');
                if (rows.length > 0) {
                    $.ajax({
                        type: 'POST',
                        url: tgc_admin_strings.ajax_admin_url,
                        data: {
                            action: 'resetentryusers',
                        },
                        success: function (response) {
                            if (true === response.success) {
                                jQuery(".tgcframe_loader").hide();
                                $('.response-html').show();
                                $('.response-html .respons-heading').html(response.data.staut_msg);
                                $('.response-html').fadeOut(2500);
                                location.reload();
                            } else {
                                jQuery(".tgcframe_loader").hide();
                                $('.response-html').show();
                                $('.response-html .respons-heading').html(response.data.staut_msg);
                                $('.response-html').fadeOut(2500);
                                location.reload();
                            }

                        }
                    });
                }else {
                    alert("no Record found");
                }

            });
            document.getElementById('exportdata').addEventListener('click', function () {
                jQuery(".tgcframe_loader").show();
                var rows = document.querySelectorAll('.tgc-tab1-admin-entries tbody tr');
                if (rows.length > 0) {
                    $.ajax({
                        type: 'POST',
                        url: tgc_admin_strings.ajax_admin_url,
                        data: {
                            action: 'export_entries_data',
                        },
                        success: function (response) {
                            if (response.success) {
                                jQuery(".tgcframe_loader").hide();
                                var csvData = response.data.csv;
                                var decodedCsvData = atob(csvData);
                                var blob = new Blob([decodedCsvData], { type: 'text/csv;charset=utf-8;' });
                                var link = document.createElement("a");
                                if (link.download !== undefined) { // feature detection
                                    // Browsers that support HTML5 download attribute
                                    var url = URL.createObjectURL(blob);
                                    link.setAttribute("href", url);
                                    link.setAttribute("download", "lucky_draw_participants.csv");
                                    link.style.visibility = 'hidden';
                                    document.body.appendChild(link);
                                    link.click();
                                    document.body.removeChild(link);
                                }
                            } else {
                                alert("Failed to generate CSV file.");
                            }
                        },
                        error: function () {
                            alert("An error occurred while generating the CSV file.");
                        }
                    });
                } else {
                    alert("No record found");
                }
            });
        }
        if($(".sync-button").length>0) {
            document.getElementById('syncentries').addEventListener('click', function () {
                jQuery(".tgcframe_loader").show();
                $.ajax({
                    type: 'POST',
                    url: tgc_admin_strings.ajax_admin_url,
                    data: {
                        action: 'sync_entries_data',
                    },
                    success: function (response) {
                        if (true === response.success) {
                            jQuery(".tgcframe_loader").hide();
                            location.reload();
                        } else {
                            jQuery(".tgcframe_loader").hide();
                            location.reload();
                        }

                    }
                });


            });
        }
        if($(".wrap-table-2").length>0) {
            document.getElementById('resetluckydraw').addEventListener('click', function () {
                jQuery(".tgcframe_loader").show();
                var rows = document.querySelectorAll('.tgc-tab2-admin-entries tbody tr');
                if (rows.length > 0) {
                    $.ajax({
                        type: 'POST',
                        url: tgc_admin_strings.ajax_admin_url,
                        data: {
                            action: 'reset_luckydraw_winners'
                        },
                        success: function (response) {
                            if (true === response.success) {
                                jQuery(".tgcframe_loader").hide();
                                $('.response-html').show();
                                $('.response-html .respons-heading').html(response.data.staut_msg);
                                $('.response-html').fadeOut(2500);
                                location.reload();
                            } else {
                                jQuery(".tgcframe_loader").hide();
                                $('.response-html').show();
                                $('.response-html .respons-heading').html(response.data.staut_msg);
                                $('.response-html').fadeOut(2500);
                                location.reload();
                            }

                        }
                    });
                } else {
                    alert("no Record found");
                }
            });
        }
        if($(".wrap-table-3").length>0) {
                document.getElementById('resetalltimewinner').addEventListener('click', function () {
                    var userConfirmed = window.confirm("Are you sure you want to delete all Records?");
                    if(userConfirmed) {
                        jQuery(".tgcframe_loader").show();
                        var rows = document.querySelectorAll('.tgc-tab3-admin-entries tbody tr');
                        if (rows.length > 0) {
                            $.ajax({
                                type: 'POST',
                                url: tgc_admin_strings.ajax_admin_url,
                                data: {
                                    action: 'reset_alltime_winners'
                                },
                                success: function (response) {
                                    if (true === response.success) {
                                        jQuery(".tgcframe_loader").hide();
                                        $('.response-html').show();
                                        $('.response-html .respons-heading').html(response.data.staut_msg);
                                        $('.response-html').fadeOut(2500);
                                        location.reload();
                                    } else {
                                        jQuery(".tgcframe_loader").hide();
                                        $('.response-html').show();
                                        $('.response-html .respons-heading').html(response.data.staut_msg);
                                        $('.response-html').fadeOut(2500);
                                        location.reload();
                                    }

                                }
                            });
                        } else {
                            alert("no Record found");
                        }
                    }
                });

        }
    }
});