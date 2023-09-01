const swalWithLoading = Swal.mixin({
    title: 'Badges are printing...',
    // text: 'Please wait',
    // icon: 'info',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => {
      Swal.showLoading();
    }
});  
jQuery(document).ready(function ($) {
    'use strict';
    var buttonCommon = {
        exportOptions: {
            columns: [0, 1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            modifier: {
                search: 'applied'
            },
        }
    };
    var t = $("#member-listing").DataTable({
        title: 'Badges > ASGMT >' + new Date().getFullYear(),
        fixedHeader: true,
        dom: 'Blfrtip',
        pageLength: 25,
        processing: true,
        serverSide: true,
        ajax: {
            url: member_object.ajax_url, // Provide the URL to the server-side script
            type: 'POST',
            data: function (data) {
                data.custom_filter = $('#filter-checkbox').is(":checked") ? 1 : 0;
                data.action = "get_member_list";
            },
        },
        columns: [
            { 
                data: 'sn',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1; // Calculate the serial number
                }
            },
            { data: 'print_status' },
            { data: 'print_btn' },
            { data: 'first_name' },
            { data: 'last_name' },
            { data: 'customer_email' },
            { data: 'company' },
            { data: 'product_name' },
            { data: 'member_bm' },
            { data: 'member_cm' },
            { data: 'member_sp' },
            { data: 'member_ex' },
            { data: 'member_ceu' },
            { data: 'customer_id' },
        ],
        createdRow: function (row, data, index) {
            let user_flag={
                BM: $(data.member_bm).text(),
                CM: $(data.member_cm).text(),
                EX: $(data.member_ex).text(),
                SP: $(data.member_sp).text(),
                CEU: $(data.member_ceu).text(),
            };
            $(row).attr('data-user_flag', JSON.stringify(user_flag));
            $(row).attr('data-printed', $(data.print_status).text() =='Printed' ? 1 : 0);
        },
        aLengthMenu: [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, "All"]
        ],
        // order: [[4, 'asc']],
        scrollX: true,
        buttons: [
            {
                extend: 'collection',
                text: 'Export',
                buttons: [
                    $.extend(true, {}, buttonCommon, {
                        extend: 'pdfHtml5',
                        text: 'Export to PDF',
                        orientation: 'landscape',
                        pageSize: 'LEGAL',
                        customize: function (doc) {
                            // Set PDF content styles
                            doc.pageMargins = [20, 10, 20, 20];  // Top, right, bottom, left padding
                        }
                    }),
                    $.extend(true, {}, buttonCommon, {
                        extend: 'csvHtml5',
                        text: 'Export to CSV',
                    }),
                    $.extend(true, {}, buttonCommon, {
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                    }),
                ]
            },
            {
                text: 'Print All (Not Printed)',
                action: function (e, dt, node, config) {
                    swalWithLoading.fire();
                    var customer_ids = [];
                    var customer_flags = {};
                    let day_pass = {};
                    print_badges(customer_ids, customer_flags, false, day_pass);
                }
            },
            {
                text: 'Reset Print Status',
                action: function (e, dt, node, config)
                {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, reset it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            jQuery.ajax({
                                type: "POST",
                                url: member_object.ajax_url,
                                dataType: "json",
                                data: {
                                    action: "reset_print_status",
                                    nonce: member_object.ajax_nonce
                                },
                                beforeSend: function() {
                                    Swal.mixin({
                                        title: 'Print status are resetting...',
                                        text: 'Please wait...',
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        showConfirmButton: false,
                                        didOpen: () => {
                                          Swal.showLoading();
                                        }
                                    }).fire(); 
                                },
                                success: function (data) {
                                    if (!data.success) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: `Something went wrong. Please try again.`,
                                        });
                                    }
                                    Swal.close();
                                    jQuery("#member-listing").DataTable().ajax.reload( null, false );
                                },
                                error: function (textStatus, errorThrown, xhr) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: `Request failed: ${errorThrown}`,
                                    });
                                }
                            });
                        }
                    });
                }
            }
        ],
    });

    //Append the custom filters
    $(".dt-buttons").insertAfter($("#member-listing_length"));
    $(".filter-wrapper").insertAfter($("#member-listing_length"));

    $("body").on("change", ".filter-checkbox, #filter-checkbox", function (e) {
        // t.rows().invalidate().draw();
        if ($("#filter-checkbox").is(":checked")) {
            t.column(1).search('Not printed', true, false, true).draw();
        } else {
            t.search('').columns().search('').draw();
        }
    });
    //Print 
    $('#reports-pdf, #reports-csv, #reports-excel').on('click', function () {
        t.button(`.buttons-${$(this).data('id')}`).trigger();
    });

    //Save Print values
    $(document).on('click', 'button[id*="member_print_"]', function (e) {
        e.preventDefault(); // Prevent default button behavior if needed
        let customer_ids = $(this).data('customer_id');
        let customer_flag = {};
        let day_pass = {};
        day_pass[`user_day_pass_${customer_ids}`] = $(this).data('product_id') == 18784 ? true : false;
        customer_flag[`user_flags_${customer_ids}`] = $(this).parents('tr').data('user_flag');
        print_badges(customer_ids, customer_flag, true, day_pass);
    })
});
function showAlert(icon, title) {
    Swal.fire({
        // position: 'top-end',
        icon: icon,
        text: title,
        showConfirmButton: true,
        // timer: 1500
    })
}
const getColumnFlagName = (columnIndex) => {
    switch (columnIndex) {
        case 8:
            return 'BM';
        case 9:
            return 'CM';
        case 10:
            return 'SP';
        case 11:
            return 'EX';
        case 12:
            return 'CEU';
        case 'day_pass':
            return 'day_pass';
        default:
            return '';
    }
}

const print_badges = async (customer_ids, customer_flag, showRadioInput = true, day_pass=false) => {
    if (showRadioInput) {
        const inputOptions = new Promise((resolve) => {
            setTimeout(() => {
                resolve({
                    '1': 'Dymo Format',
                    '2': 'Letter Format'
                })
            }, 500)
        })

        const { value: color } = await Swal.fire({
            title: 'Print badges',
            input: 'radio',
            inputOptions: inputOptions,
            inputValue: '1',
            showCancelButton: true,
            confirmButtonText: 'Print',
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to choose something!'
                }
            },
            showLoaderOnConfirm: true,
            preConfirm: (format_type) => {
                jQuery.ajax({
                    type: "POST",
                    url: member_object.ajax_url,
                    dataType: "json",
                    data: {
                        action: "print_badges",
                        customer_ids: customer_ids,
                        format_type: format_type,
                        user_flag: customer_flag,
                        day_pass:day_pass
                    },
                    beforeSend: function() {
                        swalWithLoading.fire();
                    },
                    success: function (data) {
                        if (data.success) {
                            jQuery('#templates').attr('template', format_type);
                            jQuery('#templates').html(data.data);
                            setTimeout(() => {
                                badgePDF();
                            }, 500)
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.data.message,
                            });
                        }
                        // swal.hideLoading();
                    },
                    error: function (textStatus, errorThrown, xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: `Request failed: ${errorThrown}`,
                        });
                    }
                });
            },
            allowOutsideClick: false,
            didOpen: () => {
                swal.showLoading();
            },
        })

    } else {
        // The rest of the code for printing without asking
        jQuery.ajax({
            type: "POST",
            url: member_object.ajax_url,
            dataType: "json",
            data: {
                action: "print_badges",
                customer_ids: customer_ids,
                format_type: 2,
                user_flag: customer_flag,
                multi_user: true,
                day_pass: day_pass
            },
            beforeSend: function() {
                swalWithLoading.fire();
            },
            success: function (data) {
                if (data.success) {
                    jQuery('#templates').attr('template', 2);
                    jQuery('#templates').html(data.data);
                    setTimeout(() => {
                        badgePDF();
                    }, 500);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.data.message,
                    });
                }
                // swal.hideLoading();
            },
            error: function (textStatus, errorThrown, xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Request failed: ${errorThrown}`,
                });
            }
        });
    }

}

const badgePDF = async () => {
    // swal.showLoading();
    const $templates = jQuery('#templates');
    const $badges_items = $templates.find('.badge_1');
    var tpl = $templates.attr("template"),
        doc,
        topMargin,
        leftMargin,
        hBadge,
        wBadge;

    if (tpl === "1") {
        doc = new jsPDF({
            format: ['100', '57'],
            unit: "mm",
        });

        topMargin = 0;
        leftMargin = 0;
        hBadge = 58;
        wBadge = 100;
    } else {
        doc = new jsPDF({
            format: ['217', '281'],
            unit: "mm",
        });

        topMargin = 20;
        leftMargin = 6.5;
        hBadge = 77;
        wBadge = 102;
    }

    var items = $templates.find(".badge_1").length,
        counter = 0,
        images = [],
        itemOrder = 1,
        ids = [],
        members = [];

    $badges_items.each(function (index) {
        var $badge = jQuery(this);

        members.push($badge.attr("data-id"));

        domtoimage.toPng($badge.get(0))
            .then(async function (dataUrl) {
                var image = new Image();
                image.src = dataUrl;

                counter++;

                images.push([$badge.attr("data-id"), image]);

                ids.push($badge.attr("data-reg-id"));
                if (items === counter) {
                    var newImages = images.reduce(function (r, v) {
                        r[parseInt(v[0])] = v;
                        return r;
                    }, []);

                    if (tpl === '1') {
                        for (var i = 0; i < newImages.length; i++) {
                            if (newImages[i]) {
                                doc.addImage(newImages[i][1], 'PNG', leftMargin, topMargin, wBadge, hBadge);

                                if (i + 1 !== newImages.length) {
                                    doc.addPage();
                                }
                            } else {
                                console.log("Image data not found for index:", i);
                            }

                        }
                    } else {
                        for (var i = 0; i < newImages.length; i++) {
                            if (newImages[i]) {
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
                                        if(newImages.length - 1 !== i && itemOrder == 6)
                                        {
                                            doc.addPage();
                                        }
                                        itemOrder = 1;
                                        break;
                                }
                            }
                        }
                    }

                    doc.save("badges.pdf");
                    Swal.close();
                    const data_ids = {
                        action: 'action_printed_in_ids',
                        ids: ids
                    };
                    try {
                        const response = await jQuery.ajax({
                            url: member_object.ajax_url,
                            type: 'POST',
                            data: data_ids,
                            dataType: 'json'
                        });
                        
                        if (response.success) {
                            ids.map((userId) => {
                                let badgeElement = jQuery(`.user_badge_print_${userId}`);
                                badgeElement.html('<b style="color:green;">Printed</b>')
                                    .next('td').find('button').text('Printed')
                                    .end().parents('tr').attr('data-printed', '1');
                            });
                            // t.ajax.reload( null, false );
                        }     
                        jQuery("#member-listing").DataTable().ajax.reload( null, false );
                        Swal.close();           
                    } catch (error) {
                        showAlert('error', error);
                        Swal.close();
                    }
                }
            })
            .catch(function (error) {
                console.error("oops, something went wrong!", error);
                Swal.close();
            });
    });

    // jQuery('#templates').html('');

};

const instructor_member = async (elem, user_id) => {
    jQuery.ajax({
        url: member_object.ajax_url,
        type: 'POST',
        data: {
            action: 'update_speaker_role',
            status: elem.checked,
            member_id: user_id
        },
        dataType: 'json',
        success: function (data) { },
        error: function (error) { },
    });
}