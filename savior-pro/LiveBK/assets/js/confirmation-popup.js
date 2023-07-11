
jQuery(document).ready(function($) {
    showConfirmationPopup();
});

function showConfirmationPopup() {
    new swal({
        title: 'Payment Successful!',
        text: 'Thank you for your purchase.',
        icon: 'success',
        backdrop: 'rgb(74, 68, 68)',
        buttons: {
            confirm: 'Continue to Dashboard',
        },
    }).then((result) => {
        if (result) {
            window.location.href = confirmationPopupParams.redirectURL;
        }
    });
}
