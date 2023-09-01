/* global wc_stripe_params */

jQuery(function($) {
    'use strict';

    // try {
    //     var stripe = Stripe(wc_stripe_params.key, {
    //         locale: wc_stripe_params.stripe_locale || 'auto',
    //     });
    // } catch (error) {
    //     console.log(error);
    //     return;
    // }
    recurly.configure(wc_recurly_params.Key + '112544555');
    const elements = recurly.Elements();

    const cardElement = elements.CardElement({
        inputType: 'mobileSelect',
        style: {
            fontSize: '1em',
            placeholder: {
                color: 'gray !important',
                fontWeight: 'bold',
                content: {
                    number: 'Card number',
                    cvv: 'CVC'
                }
            },
            invalid: {
                fontColor: 'red'
            }
        }
    });
    jQuery('body').on('updated_checkout', function() {
        cardElement.attach('#recurly-elements');
    });
    $('form.checkout.woocommerce-checkout').on('submit', function(event) {
        // alert();
        event.preventDefault();
        const form = $('body').find('.checkout.woocommerce-checkout');
        recurly.token(elements, form, function(err, token) {
            if (err) {
                console.log(err)
                console.log(form)
                    // handle error using err.code and err.fields
            } else {
                console.log(token)
                    // recurly.js has filled in the 'token' field, so now we can submit the
                    // form to your server
                    // form.submit();
            }
        });
    });
});