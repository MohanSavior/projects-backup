//exhibitor-management-datatable.js
function showAlert(icon, title) {
    Swal.fire({
        // position: 'top-end',
        icon: icon,
        text: title,
        showConfirmButton: true,
        // timer: 1500
    })
}
// Initialize DataTables
jQuery(document).ready(function ($) {
    //Check email is already exists.
    $("#input_18_13").blur(function(){
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'check_email_exist',
                email: $(this).val()
            },
            beforeSend: function () {
                $('body').find(`#gravity-form-container`).prepend('<div id="assistant-spinner"></div>');
            },
            success: function (response) {
                $('body').find(`#gravity-form-container #assistant-spinner`).remove();
                if (response.success) {
                    $('body').find('.primary-exhibitors-email-exist').remove();
                    $('.primary-exhibitors-contact').append(`<em class="primary-exhibitors-email-exist"><span class="dashicons-before dashicons-yes"></span>${response.data.message}</em>`);
                }
            },
            error: function (xhr, status, error) {
                $('body').find(`#gravity-form-container #assistant-spinner`).remove();
                showAlert('error', xhr.responseText);
            }
        });
    }); 
    //Exhibitor Status
    $('#gform_19').before($('#exhibitor-profile .exhibitor-status'));
    $(document).on('change', '.status-select', function () {
        var selectedValue = $(this);
        var statusUpdate = true;
    
        if ((selectedValue.data('assign_booth_number') == 0 || selectedValue.data('assign_booth_number') =='Not Assigned') && $(this).val() == 'complete') {
            Swal.fire({
                title: 'Exhibitor\'s have not been assigned to any Booth as yet',
                text: "Do you want to set the status to complete?",
                icon: 'warning',
                width: 'auto',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, complete it!'
            }).then((result) => {
                statusUpdate = result.isConfirmed ? true : false;
    
                if (statusUpdate) {
                    exhibitor_update_status(selectedValue);
                }
            });
        } else {
            exhibitor_update_status(selectedValue);
        }
    });
    
    function exhibitor_update_status(selectedValue) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'update_user_status',
                user_id: selectedValue.data('row'),
                new_status: selectedValue.val()
            },
            beforeSend: function () {
                $('body').find(`#exhibitor-members-list_wrapper`).prepend('<div id="assistant-spinner"></div>');
            },
            success: function (response) {
                $('body').find(`#exhibitor-members-list_wrapper #assistant-spinner`).remove();
                if (response.success) {
                    showAlert('success', response.data); //'warning','error'
                } else {
                    showAlert('error', response.data); //'warning','error'
                }
            },
            error: function (xhr, status, error) {
                $('body').find(`#exhibitor-members-list_wrapper #assistant-spinner`).remove();
                showAlert('error', xhr.responseText);
            }
        });
    }
    if (typeof exhibitor_object !== "undefined" && exhibitor_object?.current_screen == "toplevel_page_exhibitor-management") {
        //Plan to Exhibitor
        $(document).on('change', '.plane-to-exhibitor', function () {
            var selectedValue = $(this);
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'update_plane_to_exhibitor_status',
                    user_id: selectedValue.data('row'),
                    new_status: $(this).val()
                },
                beforeSend: function () {
                    $('body').find(`#exhibitor-members-list_wrapper`).prepend('<div id="assistant-spinner"></div>');
                },
                success: function (response) {
                    $('body').find(`#exhibitor-members-list_wrapper #assistant-spinner`).remove();
                    showAlert('success', 'Successfully updated!');
                },
                error: function (xhr, status, error) {
                    $('body').find(`#exhibitor-members-list_wrapper #assistant-spinner`).remove();
                    showAlert('error', xhr.responseText);
                }
            });
        });
        //DataTable

        let exhibitor_profile = exhibitor_object?.booth_admin;
        var t = $("#exhibitor-members-list").DataTable({
            // dom: '<"top"i>rt<"bottom"flp><"clear">',
            dom: 'Blfrtip',
            "ajax": {
                "url": exhibitor_object.ajax_url,
                "type": "POST",
                "data": {
                    "action": "get_exhibitor_members"
                }
            },
            "columns": [
                { "data": "no" },
                {
                    "data": "status",
                    render: function (data, type, row, meta) {
                        var keyValueArray = [
                            { key: '', value: 'Select status' },
                            { key: 'confirm_contact', value: 'Confirm Contact' },
                            { key: 'account_pending', value: 'Account Pending' },
                            { key: 'account_activated', value: 'Account Activated' },
                            { key: 'booth_pending', value: 'Booth Pending' },
                            { key: 'pending_payment', value: 'Pending Payment' },
                            { key: 'payment_complete', value: 'Payment Complete' },
                            { key: 'complete', value: 'Complete' }
                        ];

                        // Selected key
                        var selectedKey = data; // Replace with the key you want to pre-select
                        var dropdownHtml = `<select data-row="${row.id}" data-assign_booth_number="${row.exhibit_booth_number}" class="status-select">`;

                        keyValueArray.forEach(function (item, index) {
                            var selected = (item.key == selectedKey) || (item.key == '') ? 'selected disabled' : '';
                            // var disabled = (index > 0) ? 'disabled' : '';
                            dropdownHtml += '<option value="' + item.key + '" ' + selected + '>' + item.value + '</option>';
                        });

                        dropdownHtml += '</select>';

                        return dropdownHtml;
                    }
                },
                {
                    "data": "company_name",
                    render: function (data, type, row, meta) {
                        return `<a href="${exhibitor_profile}&exhibitor_id=${row.id}">${data}</a>`;
                    }
                },
                {
                    "data": "plan_to_exhibit",
                    render: function (data, type, row, meta) {
                        // Array with key-value pairs
                        var keyValueArray = [
                            { key: '-', value: 'Select plan to exhibit' },
                            { key: 'yes', value: 'Yes' },
                            { key: 'no', value: 'No' },
                            { key: 'no_reply', value: 'No Reply' },
                            { key: 'maybe', value: 'Maybe' },
                        ];

                        // Selected key
                        var selectedKey = data; // Replace with the key you want to pre-select
                        var plan_to_exhibit = `<select data-row="${row.id}" class="plane-to-exhibitor">`;

                        keyValueArray.forEach(function (item, index) {
                            var selected = (item.key === selectedKey) || (item.key === '-') ? 'selected disabled' : '';
                            // var disabled = (index > 0) ? 'disabled' : '';
                            plan_to_exhibit += '<option value="' + item.key + '" ' + selected + '>' + item.value + '</option>';
                        });

                        plan_to_exhibit += '</select>';

                        return plan_to_exhibit;
                    }
                },
                { "data": "first_name" },
                { "data": "last_name" },
                { "data": "email" },
                { "data": "booth_count" },
                { "data": "exhibit_booth_number" },
                { "data": "exhibit_rep_first_name" },
                { "data": "exhibit_rep_last_name" },
                { "data": "particepating_year" },
                { "data": "id" },
                { "data": "date_of_registration" },
                { "data": "active_status" },
                { "data": "active_plan_to_exhibit" },

                // Add more columns if needed
            ],
            pageLength: 25,
            aLengthMenu: [
                [25, 50, 100, 200, -1],
                [25, 50, 100, 200, "All"]
            ],
            buttons: [
                // {extend: 'pdf'},
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    split: ['csv', 'pdf', 'excel'],
                    text: 'Export to CSV',
                    filename: 'exhibitor-members-', // Rename the downloaded CSV file
                    exportOptions: {
                        columns: ':not(:last-child)',
                        modifier: {
                            search: 'applied'
                        }
                    }
                },
                // {extend: 'excel'} 
            ],
            columnDefs: [
                {
                    searchable: false,
                    // orderable: true,
                    // targets: 0,
                    targets: [1,3], 
                    orderable: false
                },
            ],
            order: [[2, 'asc']],
            "processing": true,
            responsive: true
        });
        $(".dt-buttons").prepend($(".year-filter"));
        $(".dataTables_filter").prepend($(".filter-by-status"));
        $(".dataTables_filter").prepend($(".filter_plan_to_exhibit"));
        $(".dataTables_filter").append($(".reset-filter"));
        
        t.on('order.dt search.dt', function () {
            let i = 1;
            t.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
                this.data(i++);
            });
        }).draw();
        //Filter By Year
        var categoryIndex = 0;
        $("#exhibitor-members-list th").each(function (i) {
            if ($(this).html() == "Date of registration") {
                categoryIndex = i; return false;
            }
        });
        $.fn.dataTable.ext.search.push(
            function (settings, data, dataIndex) {
                var selectedItem = $('#year-filter').val();
                var category = data[categoryIndex];
                if (selectedItem === "" || category.includes(selectedItem)) {
                    return true;
                }
                return false;
            }
        );
        $("#year-filter").change(function (e) {
            t.draw();
        });
        //End Filter By Year
        //Filter By Current Status"
        var StatusIndex = 0;
        $("#exhibitor-members-list th").each(function (i) {
            if ($(this).html() == "Active status") {
                StatusIndex = i; return false;
            }
        });
        $.fn.dataTable.ext.search.push(
            function (settings, data, dataIndex) {
                var currentStatus = $('#filter-by-status').val();
                var category = data[StatusIndex];
                if (currentStatus === "" || category === currentStatus) {
                    return true;
                }
                return false;
            }
        );
        $("#filter-by-status").change(function (e) {
            t.draw();
        });
        //End Filter by Current Status
        //Filter by plan to exhibit
        var PlanToIndex = 0;
        $("#exhibitor-members-list th").each(function (i) {
            if ($(this).html() == "Active plan to exhibit") {
                PlanToIndex = i; return false;
            }
        });
        $.fn.dataTable.ext.search.push(
            function (settings, data, dataIndex) {
                var currentPlanTo = $('#filter_plan_to_exhibit').val();
                var category = data[PlanToIndex];
                if (currentPlanTo === "" || category === currentPlanTo) {
                    return true;
                }
                return false;
            }
        );
        $("#filter_plan_to_exhibit").change(function (e) {
            t.draw();
        });
        //End Filter by plan to exhibit
        //Reset All Filters
        // Clear all filters
        $('.reset-filter').on('click', function () {
            t.search('').columns().search('').draw();
            $('#year-filter').val('').trigger('change');
            $('#filter-by-status').val('').trigger('change');
            $('#filter_plan_to_exhibit').val('').trigger('change');
        });
        //End Reset All Filters
        t.draw();
    }
    //Booth Admin other pages
    if (typeof exhibitor_object !== "undefined" && exhibitor_object?.current_screen == "asgmt-exhibits_page_edit-exhibitor-profile") {
        //calculate price
        $('#calculatePrice').on('change', function () {
            var quantity = parseInt($(this).val());
            quantity = isNaN(quantity) ? 0 : quantity;
            var price = quantity * parseInt($(this).data('price'));
            $('#totalValue').text(price);
        });
        $('#send-invoice-assign-booth').on('click', function (event) {
            event.preventDefault();
            var productsValues = jQuery('#calculatePrice').val();
            productsValues = isNaN(productsValues) ? 0 : productsValues;
            if (productsValues > 0) {
                $.ajax({
                    url: ajax_object.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'assign_booth_products',
                        products_ids: jQuery('#calculatePrice').data('product_id'),
                        qty: productsValues,
                        customer_id: $('input[name="customer_id"]').val()
                    },
                    beforeSend: function () {
                        $('body').find(`.assign-booth-products`).prepend('<div id="assistant-spinner"></div>');
                    },
                    success: function (response) {
                        $('body').find(`.assign-booth-products`).find('#assistant-spinner').remove();
                        if (response.success) {
                            showAlert('success', 'Sent Invoice successfully!');//'warning','error'
                        } else {
                            showAlert('error', 'Something went wrong please try again!');//'warning','error'
                        }
                    },
                    error: function (xhr, status, error) {
                        $('body').find(`.assign-booth-products`).find('#assistant-spinner').remove();
                        showAlert('error', 'Something went wrong please try again!');//'warning','error'
                    }
                });
            } else {
                showAlert('error', 'Quantity should be greater than zero');//'warning','error'                      
            }
        });
        //Save booth count
        $('#save-count-booth').on('click', function (event) {
            event.preventDefault();
            var productsValues = jQuery('#calculatePrice').val();
            productsValues = isNaN(productsValues) ? 0 : productsValues;
            $.ajax({
                url: ajax_object.ajax_url,
                method: 'POST',
                data: {
                    action: 'add_booth_count',
                    customer_id: $('input[name="customer_id"]').val(),
                    count: productsValues
                },
                beforeSend: function () {
                    $('body').find(`.assign-booth-products`).prepend('<div id="assistant-spinner"></div>');
                },
                success: function (response) {
                    $('body').find(`.assign-booth-products`).find('#assistant-spinner').remove();
                    if (response.success) {
                        showAlert('success', `${productsValues} ${ productsValues > 1 ? 'Booths' : 'Booth'} Added Succsessfully`);//'warning','error'
                        $('body').find('.booth-number-log').html(response.data.data);
                    } else {
                        showAlert('error', 'Something went wrong please try again!');//'warning','error'
                    }
                },
                error: function (xhr, status, error) {
                    $('body').find(`.assign-booth-products`).find('#assistant-spinner').remove();
                    showAlert('error', 'Something went wrong please try again!');//'warning','error'
                }
            });
        });
        //Add new assistant 
        $('#add-new-assistant').on('click', function () {
            $('.add-assistant-form').slideDown();
            $('#hide-new-assistant').show();
        });
        $('#hide-new-assistant').on('click', function () {
            $('.add-assistant-form').slideUp();
            $(this).hide();
        });
        $( "#tabs" ).tabs();
        $( "#booth-admin-tabs" ).tabs();
        //Add Representative
        $('#representative-form').on('submit', function(e) {
          e.preventDefault();
          var form = $(this);
          var firstName = form.find('input[name="representative_first_name"]').val();
          var lastName = form.find('input[name="representative_last_name"]').val();
          var email = form.find('input[name="representative_email"]').val();
          var contact = form.find('input[name="representative_contact"]').val();

          if (!firstName || !lastName || !email || !contact) {
            showAlert('error', 'Please fill in all required fields');
            return;
          }
          var formData = $(`#representative-form`).serialize();
          $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            data: formData,
            beforeSend: function(){
                $('body').find(`#booth-admin-tabs-representative`).prepend('<div id="assistant-spinner"></div>');
            },
            success: function(response) {
                if(response.success){
                  $('body').find(`#booth-admin-tabs-representative`).find('#assistant-spinner').remove();
                  showAlert('success', 'Representative added successfully!');
                }else{
                  showAlert('error', 'Something went wrong please try again!');//'warning','error'
                }
            },
            error: function(xhr, status, error) {
                $('body').find(`#booth-admin-tabs-representative`).find('#assistant-spinner').remove();
                showAlert('error', 'Something went wrong please try again!');//'warning','error'
            }
          });          
        });     
        //notes-save
        $('#notes-form').on('submit', function(e) {
          e.preventDefault();
          var form = $(this);
          var notes = form.find('textarea[name="notes"]').val();
          var formData = $(`#notes-form`).serialize();
          $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            data: formData,
            beforeSend: function(){
                $('body').find(`#booth-admin-tabs-notes`).prepend('<div id="assistant-spinner"></div>');
            },
            success: function(response) {
                if(response.success){
                  $('body').find(`#booth-admin-tabs-notes`).find('#assistant-spinner').remove();
                  showAlert('success', 'Notes updated successfully!');
                }else{
                  showAlert('error', 'Something went wrong please try again!');//'warning','error'
                }
            },
            error: function(xhr, status, error) {
                $('body').find(`#booth-admin-tabs-notes`).find('#assistant-spinner').remove();
                showAlert('error', 'Something went wrong please try again!');//'warning','error'
            }
          });          
        });
    }
});