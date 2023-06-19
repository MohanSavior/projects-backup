jQuery(document).ready(function ($) {
    'use strict'
    function showAlert(icon, title) {
        Swal.fire({
            // position: 'top-end',
            icon: icon,
            text: title,
            showConfirmButton: true,
            // timer: 1500
        })
    }
    var dateFormat = "yy-mm-dd";
    var currentDate = new Date();
    var from = $("#custom_date_from").datepicker({
        // defaultDate: "+1w",
        changeMonth: true,
        maxDate: currentDate,
        dateFormat: dateFormat,
        numberOfMonths: 3
    }).on("change", function () {
        var selectedDate = getDate(this);
        to.datepicker("option", "minDate", selectedDate); // Set the minimum date for "to" datepicker

        // Additional validation to check if "from" date is greater than "to" date
        var toDate = getDate(to[0]);
        if (selectedDate && toDate && selectedDate > toDate) {
            to.datepicker("setDate", selectedDate); // Set "to" date to "from" date
        }
    });

    var to = $("#custom_date_to").datepicker({
        // defaultDate: "+1w",
        changeMonth: true,
        maxDate: currentDate,
        dateFormat: dateFormat,
        numberOfMonths: 3
    }).on("change", function () {
        var selectedDate = getDate(this);
        to.datepicker("option", "minDate", selectedDate); // Set the minimum date for "to" datepicker

        // Additional validation to check if "from" date is greater than "to" date
        var toDate = getDate(to[0]);
        if (selectedDate && toDate && selectedDate > toDate) {
            to.datepicker("setDate", selectedDate); // Set "to" date to "from" date
        }
    });

    function getDate(element) {
        var date;
        try {
            date = $.datepicker.parseDate(dateFormat, element.value);
        } catch (error) {
            date = null;
        }
        return date;
    }
    $('#registration-reports').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var registration_type = $('#registration_type').val();
        var registration_year = $('#registration_year').val();
        var custom_date_from = $('#custom_date_from').val();
        var custom_date_to = $('#custom_date_to').val();
        // if (!registration_type || !registration_year || !custom_date_from || !custom_date_to) {
        //     // showAlert('error', 'Please fill in all required fields');
        //     alert('Please fill at least one field');
        //     return;
        // }
        $.ajax({
            url: reports_obj.ajax_url,
            method: 'POST',
            data: formData,
            beforeSend: function () {
                $('body').find(`.registration-reports-container`).prepend('<div id="assistant-spinner"></div>');
            },
            success: function (response) {
                $('body').find(`.registration-reports-container`).find('#assistant-spinner').remove();
                if (response.success) {
                    jsonToCsv(response.data.data, response.data.filename);
                    // showAlert('success', 'Representative added successfully!');
                } else {
                    showAlert('error', 'No record found.');//'warning','error'
                }
            },
            error: function (xhr, status, error) {
                $('body').find(`.registration-reports-container`).find('#assistant-spinner').remove();
                // showAlert('error', 'Something went wrong please try again!');//'warning','error'
            }
        });
    });

    function jsonToCsv(jsonData, filename) {
        const jsonArray = jsonData;
        const headers = Object.keys(jsonData[0]);
        let csv = headers.join(',') + '\n';
      
        jsonArray.forEach((item) => {
            const values = headers.map((header) => escapeField(String(item[header])));
          csv += values.join(',') + '\n';
        });
      
        // Create a CSV Blob
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      
        // Create a temporary anchor element
        const link = document.createElement('a');
        if (link.download !== undefined) {
          // Set the download attribute with the filename
          link.setAttribute('href', URL.createObjectURL(blob));
          link.setAttribute('download', filename + '.csv');
          link.style.visibility = 'hidden';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        }
    }
    function escapeField(field) {
        if (typeof field === 'string' && field.includes(',')) {
          field = field.replace(',', '');
        } else if (field instanceof Date) {
          // Format the Date object to your desired format
          const formattedDate = formatDate(field);
          field = formattedDate;
        }
        return field;
      }
      
    function formatDate(date) {
        // Customize the date formatting according to your needs
        const formattedDate = date.toISOString().substring(0, 10);
        return formattedDate;
    }
});