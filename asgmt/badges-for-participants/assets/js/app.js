(function ($) {
    'use strict';

    var participants = function () {

        $(document).ready(function () {
            var $formShowOriginalName = $('#form-show-original-name');

            $formShowOriginalName.find('input[name="show_original_name"]').on('change', function(){
                $formShowOriginalName.css('pointer-events', 'none').submit();
            });

            $('select[name="template"]').on('change', function () {
                $(this).parents('form').submit();
            });

            $('.print-badge-show-error-message').on('click', function (e) {
                e.preventDefault();
                $(this).attr('disabled', 'disabled');
                $('.hide-print-badge-show-error-message').removeClass('hide-print-badge-show-error-message');
            });

            // AlignBadge1();
            if (typeof starting_value !== 'undefined') {
                // console.log(starting_value);
                var editor = new JSONEditor(document.getElementById('json_editor'), {
                    theme: 'bootstrap2',
                    schema: {
                        type: "object",
                        title: "",
                        properties: {
                            first_name: {
                                type: "string",
                                title: "First Name"
                            },
                            last_name: {
                                type: "string",
                                title: "Last Name"
                            },
                            friendly_name: {
                                type: "string",
                                title: "Friendly Name"
                            },
                            company: {
                                type: "string",
                                title: "Company"
                            },
                            email: {
                                type: "string",
                                title: "Email"
                            },
                            job: {
                                type: "string",
                                title: "Job Position"
                            },
                            country: {
                                type: "string",
                                title: "Country"
                            },
                            state: {
                                type: "string",
                                title: "State"
                            },
                            city: {
                                type: "string",
                                title: "City"
                            },
                            visitor_type: {
                                type: "string",
                                enum: ["Vendor", "Student","Day Pass"],
                                default: "Vendor",
                                title: "Visitor Type"
                            },
                            is_attendee: {
                                type: "string",
                                enum: ["Yes", "No"],
                                title: "Attendee"
                            },
                            is_gas_flow: {
                                type: "string",
                                enum: ["Yes", "No"],
                                title: "Gas Flow"
                            },
                            is_ceu: {
                                type: "string",
                                enum: ["Yes", "No"],
                                title: "CEU"
                            },
                            is_liquid: {
                                type: "string",
                                enum: ["Yes", "No"],
                                title: "Liquid"
                            },
                            role_speaker: {
                                type: "string",
                                enum: ["Yes", "No"],
                                title: "Speaker"
                            },
                            role_committee: {
                                type: "string",
                                enum: ["Yes", "No"],
                                title: "Committee Member"
                            },
                            role_board: {
                                type: "string",
                                enum: ["Yes", "No"],
                                title: "Board Member"
                            },
                            role_exhibitor: {
                                type: "string",
                                enum: ["Yes", "No"],
                                title: "Exhibitor"
                            },
                            is_qr_print: {
                                type: "string",
                                enum: ["Yes", "No"],
                                title: "Print QR"
                            },
                            phone_daytime: {
                                type: "string",
                                title: "Day Time Phone"
                            },
                            phone_cell: {
                                type: "string",
                                title: "Cell Phone"
                            },
                            addr_zip: {
                                type: "string",
                                title: "Mailing Address ZIP"
                            },
                            addr_country: {
                                type: "string",
                                title: "Mailing Address Country"
                            },
                            addr_state: {
                                type: "string",
                                title: "Mailing Address State"
                            },
                            addr_city: {
                                type: "string",
                                title: "Mailing Address City"
                            },
                            addr_addr_1: {
                                type: "string",
                                title: "Mailing Address Street"
                            },
                            addr_addr_2: {
                                type: "string",
                                title: "Mailing Address Street (second line)"
                            }
                        }
                    },
                    startval: starting_value,
                    no_additional_properties: true,
                    required_by_default: true,
                    disable_collapse: true,
                    disable_edit_json: true,
                    disable_properties: true
                });
            }
            var $badges = $('body').find('#badges'),
                $templates = $('body').find('#templates'),
                $progress = $('body').find('.progress'),
                $badges_items = $templates.find('.badge_1'),
                $memberProfile = $('.member-profile'),
                $download_pdf_by_date = $('input[name="download_pdf_by_date"]');


            if ($download_pdf_by_date.length > 0) {
                var $form_download_by_pdf = $('.download-pdf-by-date');
                var $selectDates = $form_download_by_pdf.find('select'),
                    ids = [];

                $selectDates.SumoSelect();

                $form_download_by_pdf.on('submit', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var ids = $selectDates.val(),
                        counter = 0,
                        $trigger = $(this).find('input[type="submit"]');

                    $trigger.addClass('loading-button');

                    if (ids.length > 0) {
                        var data = {
                            'action': 'export_attendance_data_by_date',
                            'reg_ids': ids
                        };

                        $.post(ajaxurl, data, function (response) {
                            console.log(response);

                            var res = JSON.parse(response);


                            if (res.success && res.data) {
                                var data = Object.keys(res.data).map(function (k) {
                                    return res.data[k]
                                });

                                var doc = new jsPDF({
                                    format: 'letter',
                                    unit: 'pt',
                                    orientation: 'l'
                                });

                                var pageWidth = doc.internal.pageSize.width,
                                    pageHeight = doc.internal.pageSize.height,
                                    h = 0,
                                    itemH = 0,
                                    pageCount = 0;

                                for (var d = 0; d < data.length; d++) {
                                    var byDay = data[d];


                                    if (byDay['items'].length > 0) {
                                        pageCount++;

                                        var itms = byDay['items'];

                                        doc.text(40, 30, 'Check in date: ' + byDay['date']);
                                        doc.text(40, 50, 'Total: ' + itms.length);

                                        var itemsArr = Object.keys(itms).map(function (t) {
                                            return itms[t]
                                        });

                                        if (itemsArr.length > 0) {

                                            doc.autoTable(['First Name', 'Last Name', 'Reg ID', 'Member ID', 'Phone', 'Email', 'Check in Time'], itms, {
                                                startY: 70,
                                                showHeader: 'firstPage'
                                            });
                                            counter++;
                                            $progress.show();
                                            $progress.attr('data-progress-percent', 100 / data.length * counter);
                                            moveProgressBar();
                                        }

                                        if (pageCount !== data.length) {
                                            doc.addPage();
                                            h = 0;
                                        }
                                    }
                                }


                                doc.save('attendance-data-by-day.pdf');

                                $trigger.removeClass('loading-button');

                            }
                        });
                    }
                });
            }

            $badges.on('click', '.enabled_field .go_change_display_name', function (e) {
                e.preventDefault();
                var $trigger = $(this);


                var display = $(this).closest('.display');

                if (display.find('.display_name').val() === '') {
                    return false;
                }

                $trigger.addClass('loading-button');

                var data = {
                    action: 'change_display_name',
                    display_name: display.find('.display_name').val(),
                    from_user_id: display.find('.from_user_id').val(),
                    congress_year: display.find('.congress_year').val(),
                    wp_item_id: display.find('.wp_item_id').val(),
                    cong_reg_id: display.find('.cong_reg_id').val(),
                    form_action: 'change_display_name'
                };

                jQuery.post(ajaxurl, data, function (response) {
                    $trigger.parents('.alternate').find('input[change_type="badge_title"]').val(display.find('.display_name').val());
                    $trigger.removeClass('loading-button');
                });


            });

            $badges.on('submit', '.submit-badges', function (e) {

                var selectSendEmails = $('select[name="action"]').val() == 'bulk-send-conf-email' || $('select[name="action2"]').val() == 'bulk-send-conf-email',
                    selectExport = $('select[name="action"]').val() == 'bulk-export' || $('select[name="action2"]').val() == 'bulk-export',
                    printBadges = $('select[name="action"]').val() == 'bulk-print' || $('select[name="action2"]').val() == 'bulk-print',
                    selectExportAttendance = $('select[name="action"]').val() == 'bulk-export-attendance' || $('select[name="action2"]').val() == 'bulk-export-attendance',
                    selectDelete = $('select[name="action"]').val() == 'bulk-delete' || $('select[name="action2"]').val() == 'bulk-delete';


                if (selectDelete === true) {
                    var all_good_delete = confirm('Are you sure you want to delete this record (these records)?');

                    if (!all_good_delete) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                }

                if (selectSendEmails === true) {
                    e.preventDefault();
                    e.stopPropagation();

                    var all_good = confirm('Resend registration notification to members');

                    if (!all_good) {
                        return false;
                    }

                    if ($('#the-list .check-column input[type="checkbox"]:checked').length > 0) {
                        var $cont = $('.send-notifications-block');
                        $cont.empty();
                        $('#the-list .check-column input[type="checkbox"]:checked').each(function () {
                            var $trig = $(this);
                            var val = $trig.val(),
                                $button = $trig.parents('tr').find('.resend_notif');


                            var name = $button.data('reg-name');

                            $cont.append('<div class="send-notification-to" data-id="' + val + '">Sending notification to: ' + name + '. <div class="st-not-cont">Status: <span class="st-not">Sending</span></div></div>')

                            var data = {
                                'action': 'resend_reg_notif',//export_all_members
                                'reg_id': val
                            };

                            var request = $.post(ajaxurl, data, function (response) {
                                if (response) {
                                    $('.send-notification-to[data-id="' + val + '"] span.st-not').addClass('Sending');
                                    $('.send-notification-to[data-id="' + val + '"] span.st-not').text('Sent');
                                } else {
                                    $('.send-notification-to[data-id="' + val + '"] span.st-not').text('We could not send the email, please try again later.');
                                }
                            });
                        });

                    }
                }

                if (selectExport === true) {
                    e.preventDefault();
                    e.stopPropagation();
                    var ids = [],
                        counter = 0;
                    $('#the-list .check-column input[type="checkbox"]:checked').each(function (index) {
                        var $trig = $(this);
                        var val = $trig.val();
                        ids[index] = val;
                    });

                    var type = $(this).attr('type');


                    if (ids.length > 0) {

                        $progress.show();
                        $progress.css('left', '0px');
                        var data = {
                            'action': type,
                            'sessions': ids
                        };

                        $.post(ajaxurl, data, function (response) {

                            var res = JSON.parse(response);
                            if (res.success && res.data) {
                                var data = Object.keys(res.data).map(function (k) {
                                    return res.data[k]
                                });

                                var doc = new jsPDF({
                                    format: 'letter',
                                    unit: 'pt'
                                });

                                var pageWidth = doc.internal.pageSize.width;

                                for (var i = 0; i < data.length; i++) {
                                    var ss = data[i];

                                    var items = ss['items'];
                                    var itemsArr = Object.keys(items).map(function (t) {
                                        return items[t]
                                    });

                                    if (itemsArr.length > 0) {

                                        if (i !== 0) {
                                            doc.addPage();
                                        }

                                        var lines = doc.splitTextToSize(ss['name'].toString(), pageWidth - 80);
                                        doc.text(40, 40, lines);

                                        doc.text(40, 80, 'Total - ' + ss['total']);

                                        doc.autoTable(['First Name', 'Last Name', 'Company'], itemsArr, {
                                            startY: 90,
                                            showHeader: 'firstPage'
                                        });
                                    }
                                    counter++;
                                    $progress.show();
                                    $progress.attr('data-progress-percent', 100 / data.length * counter);
                                    moveProgressBar();
                                }
                                doc.save(type + '.pdf');

                            }
                        });
                    }
                }

                if (selectExportAttendance === true) {
                    e.preventDefault();
                    e.stopPropagation();
                    var ids = [],
                        counter = 0;
                    $('#the-list .check-column input[type="checkbox"]:checked').each(function (index) {

                        var $trig = $(this);
                        var val = $trig.val();
                        ids[index] = val;
                    });
                    $progress.css('left', '0px');
                    $progress.show();

                    if (ids.length > 0) {
                        var data = {
                            'action': 'export_attendance_data',
                            'reg_ids': ids
                        };

                        $.post(ajaxurl, data, function (response) {
                            var res = JSON.parse(response);

                            if (res.success && res.data) {
                                var data = Object.keys(res.data).map(function (k) {
                                    return res.data[k]
                                });

                                var doc = new jsPDF({
                                    format: 'letter',
                                    unit: 'pt'
                                });

                                var pageWidth = doc.internal.pageSize.width,
                                    pageHeight = doc.internal.pageSize.height,
                                    h = 0,
                                    itemH = 0;

                                for (var i = 0; i < data.length; i++) {
                                    var ss = data[i];
                                    var itemsArr = Object.keys(data).map(function (t) {
                                        return data[t]
                                    });

                                    if (itemsArr.length > 0) {

                                        var fullName = ss['first_name'] + ' ' + ss['last_name'],
                                            lines = doc.splitTextToSize(fullName.toString(), pageWidth - 80);

                                        if ((h + (undefined !== ss['sessions'] && ss['sessions'].length > 0 ? 30 * ss['sessions'].length : 0) + (undefined !== ss['social_events'] && ss['social_events'].length > 0 ? 30 * ss['social_events'].length : 0) + 75) > pageHeight) {
                                            doc.addPage();

                                            doc.text(40, 30, lines);

                                            if (undefined !== ss['sessions'] && ss['sessions'].length > 0) {
                                                var sessions = Object.keys(ss['sessions']).map(function (st) {
                                                    return ss['sessions'][st]
                                                });

                                                doc.autoTable([{title: "Sessions:", dataKey: "name"}], sessions, {
                                                    startY: 50,
                                                    showHeader: 'firstPage'
                                                });
                                            }

                                            if (undefined !== ss['social_events'] && ss['social_events'].length > 0) {

                                                var social_events = Object.keys(ss['social_events']).map(function (set) {
                                                    return ss['social_events'][set]
                                                });

                                                doc.autoTable([{
                                                    title: "Social Events:",
                                                    dataKey: "name"
                                                }], social_events, {
                                                    startY: (undefined !== ss['sessions'] && ss['sessions'].length > 0 ? doc.autoTable.previous.finalY : 50),
                                                    showHeader: 'firstPage'
                                                });
                                            }

                                            h = 0;
                                        } else {
                                            doc.text(40, (i !== 0 ? doc.autoTable.previous.finalY + 30 : 30), lines);


                                            if (undefined !== ss['sessions'] && ss['sessions'].length > 0) {
                                                var sessions = Object.keys(ss['sessions']).map(function (st) {
                                                    return ss['sessions'][st]
                                                });

                                                doc.autoTable([{title: "Sessions:", dataKey: "name"}], sessions, {
                                                    startY: (i !== 0 ? doc.autoTable.previous.finalY + 50 : 50),
                                                    showHeader: 'firstPage'
                                                });
                                            }

                                            if (undefined !== ss['social_events'] && ss['social_events'].length > 0) {

                                                var social_events = Object.keys(ss['social_events']).map(function (set) {
                                                    return ss['social_events'][set]
                                                });

                                                doc.autoTable([{
                                                    title: "Social Events:",
                                                    dataKey: "name"
                                                }], social_events, {
                                                    startY: (undefined !== sessions && sessions.length > 0 && i === 0 ? doc.autoTable.previous.finalY : (i !== 0 ? (undefined !== sessions && sessions.length > 0 ? doc.autoTable.previous.finalY : doc.autoTable.previous.finalY + 50) : 50)),
                                                    showHeader: 'firstPage'
                                                });
                                            }

                                            h = doc.autoTable.previous.finalY;
                                        }
                                        counter++;
                                        $progress.show();
                                        $progress.attr('data-progress-percent', 100 / data.length * counter);
                                        moveProgressBar();
                                    }

                                }
                                doc.save('attendance-data.pdf');

                            }
                        });
                    }
                }
            });

            $badges.on('click', '.enabled_field .go_change_color', function (e) {
                e.preventDefault();
                var $trigger = $(this);

                var colors = $(this).closest('.colors');

                if (colors.find('.select_color').val() === '') {
                    return false;
                }

                $trigger.addClass('loading-button');

                var data = {
                    action: 'change_color',
                    cc_color: colors.find('.select_color').val(),
                    cc_from_user_id: colors.find('.cc_from_user_id').val(),
                    cc_congress_year: colors.find('.cc_congress_year').val(),
                    cc_wp_item_id: colors.find('.cc_wp_item_id').val(),
                    cc_cong_reg_id: colors.find('.cc_cong_reg_id').val(),
                    cc_form_action: 'change_color'
                };

                jQuery.post(ajaxurl, data, function (response) {
                    $trigger.parents('.alternate').find('input[change_type="badge_color"]').val(colors.find('.select_color').val());
                    $trigger.removeClass('loading-button');
                });
            });

            $badges.on('click', '.enabled_field .go_change_printed_count', function (e) {
                e.preventDefault();
                var $trigger = $(this);

                var printed = $(this).closest('.printed');

                if (printed.find('.prtd_count').val() === '') {
                    return false;
                }

                $trigger.addClass('loading-button');

                var data = {
                    action: 'change_printed_count',
                    prtd_printed_count: printed.find('.prtd_count').val(),
                    prtd_from_user_id: printed.find('.prtd_from_user_id').val(),
                    prtd_congress_year: printed.find('.prtd_congress_year').val(),
                    prtd_wp_item_id: printed.find('.prtd_wp_item_id').val(),
                    prtd_cong_reg_id: printed.find('.prtd_cong_reg_id').val(),
                    prtd_form_action: 'change_printed_count'
                };

                jQuery.post(ajaxurl, data, function (response) {
                    $trigger.removeClass('loading-button');
                });
            });

            $badges.on('click', '.action-print', function () {
                $('.check-column input[type="checkbox"]').prop("checked", false);
                $(this).parents('tr').find('.check-column input[type="checkbox"]').prop("checked", true);

                $(this).parents('tr').parents('.alternate').find('input[change_type="badge_color"]').val($(this).parents('tr').find('.select_color').val());
                $(this).parents('tr').parents('.alternate').find('input[change_type="badge_title"]').val($(this).parents('tr').find('.display_name').val());

                $('#bulk-action-selector-top').val('bulk-print');
                $('.search-box').parent().submit();
            });

            $badges.on('click', '.print-qr-code-session', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var $trig = $(this),
                    items = [],
                    $selectSessions = $('select[name="session_id"]'),
                    $itemsContainer = $('.qr-code-items');

                $itemsContainer.empty();
                $itemsContainer.removeClass('single all');

                if ($trig.hasClass('single')) {
                    items.push([$selectSessions.val(), $selectSessions.find('option:selected').text()]);
                }

                if ($trig.hasClass('all')) {
                    var allItems = $selectSessions.find('option');
                    for (var t = 0; t < allItems.length; t++) {
                        var option = allItems[t];
                        items.push($(option).val());
                    }
                }

                window.location = location.protocol + '//' + location.hostname + '/wp-admin/?page=print_items&type=' + $trig.attr('type') + '&ids=' + items.join(',');

            });

            $badges.on('click', '.action_check_in', function () {
                var $trigger = $(this),
                    is_checked = $trigger.parent().hasClass('is_checked');
                $trigger.addClass('loading-button');

                var $res_check_in = (is_checked ? $(this).parents('tr').find('.column-is_checked span'): $(this).parents('tr').find('.column-is_printed span'));
                var resText = $res_check_in.text() !== 'Yes',
                    id = $(this).parents('tr').find('.reg-id').attr('reg_id');

                var data = {
                    action: (is_checked ? 'action_check_in' : 'action_printed_in'),
                    id: id,
                    congress_year: $('.members-year').val(),
                    type: resText
                };

                var student = $(this).parents('tr').find('.visitor_type').text() === 'Student' && $(this).parents('tr').find('.is_additional').text() === 'No';

                jQuery.post(ajaxurl, data, function (res) {
                    $trigger.removeClass('loading-button');
                    if (res === 'success') {
                        if (resText !== false ) {
                            $res_check_in.text('Yes');
                            if (!is_checked && student) {
                                var html = '<div class="red_box" data-id="'+id+'" title="Is not student"></div>';
                                $trigger.parents('tr').find('.check-column').html(html);
                            }
                        } else {
                            $res_check_in.text('No');
                            if (!is_checked && student) {
                                var html = '<input id="bulk-print-'+id+'" type="checkbox" class="id_input" name="bulk-print['+id+']" value="'+id+'">';
                                $trigger.parents('tr').find('.check-column').html(html);
                            }
                        }
                    }
                });
            });

            $('.disabled-edit-field').each(function () {
                var $this = $(this);

                $this.find('.toggle-action-save-edit').on('click', function (e) {
                    if ($this.hasClass('disabled-edit-field')) {
                        console.log('has class disabled-edit-field');
                        e.preventDefault();
                    }
                    setTimeout(function () {
                        $this.toggleClass('enabled_field');
                        $this.toggleClass('disabled-edit-field');
                    });
                });
            });

            if ($templates.length > 0) {

                var $elemsFSize = $('.full-width');

                $elemsFSize.each(function (index, el) {
                    var $trig = $(this),
                        $parent = $trig.parent(),
                        $el = $parent.parents('.badge_1');

                    if ($trig.width() > $parent.width()) {
                        var fSize = parseInt($parent.css('font-size')),
                            intunic = setInterval(function () {
                                if ($trig.width() > $parent.width()) {
                                    fSize--;

                                    if (fSize === 9) {
                                        $parent.addClass('small-strings');
                                        $el.addClass('ready');
                                        clearInterval(intunic);
                                    }
                                    $parent.css('font-size', fSize + 'px');
                                } else {
                                    $el.addClass('ready');
                                    clearInterval(intunic);
                                }
                            }, 1);
                    }

                });

                $badges.on('click', '.action-download-pdf', function () {

                    var tpl = $templates.attr('template'), doc, topMargin, leftMargin, hBadge, wBadge;

                    if (tpl === '1') {
                        doc = new jsPDF({
                            // format: ['100', '57'],
                            // unit: 'mm'
                            orientation: 'L', // 'p' for portrait, 'l' for landscape
                            unit: 'mm',
                            format: [101.6, 76.2], // 4" x 3" in millimeters                            
                        });

                        topMargin = 0;
                        leftMargin = 0;
                        hBadge = 58;
                        wBadge = 100;

                    } else {
                        doc = new jsPDF({
                            format: ['217', '281'],
                            unit: 'mm'
                        });

                        topMargin = 24;
                        leftMargin = 6.5;
                        hBadge = 77;
                        wBadge = 102;
                    }

                    var items = $templates.find('.badge_1').length,
                        counter = 0,
                        images = [],
                        itemOrder = 1,
                        ids = [],
                        members = [],
                        guests = [];

                    var $trigger = $(this);

                    $trigger.attr('disabled', 'disabled');

                    $badges_items.each(function (index) {
                        var $badge = $(this);

                        members.push($badge.attr('data-id'));

                        domtoimage.toPng($badge.get(0))
                            .then(function (dataUrl) {
                                var image = new Image();
                                image.src = dataUrl;

                                counter++;

                                $progress.show();
                                $progress.attr('data-progress-percent', 100 / $badges_items.length * counter);

                                moveProgressBar();
                                images.push([$badge.attr('data-id'), image]);

                                ids.push($badge.attr('data-reg-id'));

                                if (items === counter) {
                                    var newImages = images.reduce(function (r, v) {
                                        r[parseInt(v[0])] = v
                                        return r;
                                    }, []);


                                    if (tpl === '1') {
                                        for (var i = 0; i < newImages.length; i++) {
                                            doc.addImage(newImages[i][1], 'PNG', leftMargin, topMargin, wBadge, hBadge);

                                            if (i + 1 !== newImages.length) {
                                                doc.addPage();
                                            }

                                        }
                                    } else {
                                        for (var i = 0; i < newImages.length; i++) {
                                            switch (itemOrder) {
                                                case 1:
                                                    doc.addImage(newImages[i][1], 'PNG', leftMargin, topMargin, wBadge, hBadge);
                                                    itemOrder = 2;
                                                    break;
                                                case 2:
                                                    doc.addImage(newImages[i][1], 'PNG', leftMargin + wBadge, topMargin, wBadge, hBadge);
                                                    itemOrder = 3;
                                                    break;
                                                case 3:
                                                    doc.addImage(newImages[i][1], 'PNG', leftMargin, topMargin + hBadge, wBadge, hBadge);
                                                    itemOrder = 4;
                                                    break;
                                                case 4:
                                                    doc.addImage(newImages[i][1], 'PNG', leftMargin + wBadge, topMargin + hBadge, wBadge, hBadge);
                                                    itemOrder = 5;
                                                    break;
                                                case 5:
                                                    doc.addImage(newImages[i][1], 'PNG', leftMargin, topMargin + hBadge + hBadge, wBadge, hBadge);
                                                    itemOrder = 6;
                                                    break;
                                                case 6:
                                                    doc.addImage(newImages[i][1], 'PNG', leftMargin + wBadge, topMargin + hBadge + hBadge, wBadge, hBadge);
                                                    itemOrder = 1;
                                                    doc.addPage();
                                                    break;
                                            }
                                        }
                                    }

                                    doc.save('badges.pdf');

                                    var data = {
                                        action: 'action_printed_in_ids',
                                        ids: ids
                                    };

                                    jQuery.post(ajaxurl, data, function (res) {
                                        console.log(ids);
                                        console.log(res);
                                    });

                                    $trigger.removeAttr('disabled');

                                }
                            })
                            .catch(function (error) {
                                console.error('oops, something went wrong!', error);
                            });
                    });

                });
            }

        });

        function moveProgressBar() {
            var getPercent = ($('.progress-wrap').attr('data-progress-percent') / 100);
            var getProgressWrapWidth = $('.progress-wrap').width();
            var progressTotal = getPercent * getProgressWrapWidth;

            $('.progress-bar').css('left', progressTotal + 'px');
        }

        function getCurrentDate() {
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth() + 1; //January is 0!
            var yyyy = today.getFullYear();

            if (dd < 10) {
                dd = '0' + dd
            }

            if (mm < 10) {
                mm = '0' + mm
            }

            today = mm + '/' + dd + '/' + yyyy;

            return today;
        }

        function getCurrentTime() {
            var now = new Date();
            var hour = now.getHours();
            var minute = now.getMinutes();

            if (hour.toString().length == 1) {
                hour = '0' + hour;
            }
            if (minute.toString().length == 1) {
                minute = '0' + minute;
            }

            var time = hour + ':' + minute;
            return time;
        }

    }

    participants();

})(jQuery);