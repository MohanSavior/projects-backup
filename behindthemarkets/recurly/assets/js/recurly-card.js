/* global wc_recurly_params */

jQuery(function($) {
    'use strict';

    recurly.configure(wc_recurly_params.key);
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

    var cardNumberElement,
        cardMonthElement,
        cardYearElement,
        cardCvvElement;

    /**
     * Object to handle Stripe elements payment form.
     */
    var wc_recurly_form = {
        /**
         * Get WC AJAX endpoint URL.
         *
         * @param  {String} endpoint Endpoint.
         * @return {String}
         */
        getAjaxURL: function(endpoint) {
            return wc_recurly_params.ajaxurl
                .toString()
                .replace('%%endpoint%%', 'wc_recurly_' + endpoint);
        },

        /**
         * Unmounts all Stripe elements when the checkout page is being updated.
         */
        unmountElements: function() {
            if ('yes' === wc_recurly_params.inline_cc_form) {
                cardElement.remove('#recurly-elements');
            }
        },

        /**
         * Mounts all elements to their DOM nodes on initial loads and updates.
         */
        mountElements: function() {
            $('input[name^="billing_"]').each(function() {
                var name = $(this).attr('name');
                name = name.replace("billing_", "");
                if (name == 'address_1') {
                    name = 'address1';
                }
                if (name == 'address_2') {
                    name = 'address2';
                }
                if (name == 'postcode') {
                    name = 'post_code';
                }
                $(this).attr('data-recurly', name);
            });
            if (!$('#recurly-elements').length) {
                return;
            }
            if ('yes' === wc_recurly_params.inline_cc_form) {
                // console.log('mountElement cre')
                cardElement.attach('#recurly-elements');
                return;
            }
        },

        /**
         * Creates all Stripe elements that will be used to enter cards or IBANs.
         */
        createElements: function() {
            // cardElement.attach('#recurly-elements');
            // console.log('createElements');
            if ('yes' === wc_recurly_params.inline_cc_form) {

            }

            /**
             * Only in checkout page we need to delay the mounting of the
             * card as some AJAX process needs to happen before we do.
             */
            if ('yes' === wc_recurly_params.is_checkout) {
                $(document.body).on('updated_checkout', function() {
                    // Don't re-mount if already mounted in DOM.
                    if ($('#recurly-elements').children().length) {
                        return;
                    }

                    // Unmount prior to re-mounting.
                    if (cardNumberElement) {
                        wc_recurly_form.unmountElements();
                    }

                    wc_recurly_form.mountElements();
                });
            } else if ($('form#add_payment_method').length || $('form#order_review').length) {
                wc_recurly_form.mountElements();
            }
        },

        /**
         * Updates the card brand logo with non-inline CC forms.
         *
         * @param {string} brand The identifier of the chosen brand.
         */
        updateCardBrand: function(brand) {
            var brandClass = {
                'visa': 'recurly-visa-brand',
                'mastercard': 'recurly-mastercard-brand',
                'amex': 'recurly-amex-brand',
                'discover': 'recurly-discover-brand',
                'diners': 'recurly-diners-brand',
                'jcb': 'recurly-jcb-brand',
                'unknown': 'recurly-credit-card-brand'
            };

            var imageElement = $('.recurly-card-brand'),
                imageClass = 'recurly-credit-card-brand';

            if (brand in brandClass) {
                imageClass = brandClass[brand];
            }

            // Remove existing card brand class.
            $.each(brandClass, function(index, el) {
                imageElement.removeClass(el);
            });

            imageElement.addClass(imageClass);
        },

        /**
         * Initialize event handlers and UI state.
         */
        init: function() {
            // Initialize tokenization script if on change payment method page and pay for order page.
            if ('yes' === wc_recurly_params.is_change_payment_page || 'yes' === wc_recurly_params.is_pay_for_order_page) {
                $(document.body).trigger('wc-credit-card-form-init');
            }

            // checkout page
            if ($('form.woocommerce-checkout').length) {
                this.form = $('form.woocommerce-checkout');
            }

            $('form.woocommerce-checkout')
                .on(
                    'checkout_place_order_recurly',
                    this.onSubmit
                );

            // pay order page
            if ($('form#order_review').length) {
                this.form = $('form#order_review');
            }

            $('form#order_review, form#add_payment_method')
                .on(
                    'submit',
                    this.onSubmit
                );

            // add payment method page
            if ($('form#add_payment_method').length) {
                this.form = $('form#add_payment_method');
            }

            $('form.woocommerce-checkout')
                .on(
                    'change',
                    this.reset
                );

            $(document)
                .on(
                    'recurlyError',
                    this.onError
                )
                .on(
                    'checkout_error',
                    this.reset
                );

            // Subscription early renewals modal.
            if ($('#early_renewal_modal_submit[data-payment-method]').length) {
                $('#early_renewal_modal_submit[data-payment-method=recurly]').on('click', this.onEarlyRenewalSubmit);
            } else {
                $('#early_renewal_modal_submit').on('click', this.onEarlyRenewalSubmit);
            }

            wc_recurly_form.createElements();

            // Listen for hash changes in order to handle payment intents
            window.addEventListener('hashchange', wc_recurly_form.onHashChange);
            wc_recurly_form.maybeConfirmIntent();
        },

        /**
         * Check to see if Stripe in general is being used for checkout.
         *
         * @return {boolean}
         */
        isStripeChosen: function() {
            return $('#payment_method_recurly').is(':checked') || ($('#payment_method_recurly').is(':checked') && 'new' === $('input[name="wc-recurly-payment-token"]:checked').val());
        },

        /**
         * Currently only support saved cards via credit cards and SEPA. No other payment method.
         *
         * @return {boolean}
         */
        isStripeSaveCardChosen: function() {
            return (
                $('#payment_method_recurly').is(':checked') &&
                $('input[name="wc-recurly-payment-token"]').is(':checked') &&
                'new' !== $('input[name="wc-recurly-payment-token"]:checked').val()
            );
        },

        /**
         * Check if Stripe credit card is being used used.
         *
         * @return {boolean}
         */
        isStripeCardChosen: function() {
            return $('#payment_method_recurly').is(':checked');
        },

        /**
         * Checks if a source ID is present as a hidden input.
         *
         * @return {boolean}
         */
        hasSource: function() {
            return 0 < $('input.recurly-token').length;
        },

        /**
         * Check whether a mobile device is being used.
         *
         * @return {boolean}
         */
        isMobile: function() {
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                return true;
            }

            return false;
        },

        /**
         * Blocks payment forms with an overlay while being submitted.
         */
        block: function() {
            if (!wc_recurly_form.isMobile()) {
                wc_recurly_form.form.block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
            }
        },

        /**
         * Removes overlays from payment forms.
         */
        unblock: function() {
            wc_recurly_form.form && wc_recurly_form.form.unblock();
        },

        /**
         * Returns the selected payment method HTML element.
         *
         * @return {HTMLElement}
         */
        getSelectedPaymentElement: function() {
            return $('.payment_methods input[name="payment_method"]:checked');
        },

        /**
         * Retrieves "owner" data from either the billing fields in a form or preset settings.
         *
         * @return {Object}
         */
        getOwnerDetails: function() {
            var first_name = $('#billing_first_name').length ? $('#billing_first_name').val() : wc_recurly_params.billing_first_name,
                last_name = $('#billing_last_name').length ? $('#billing_last_name').val() : wc_recurly_params.billing_last_name,
                owner = { name: '', address: {}, email: '', phone: '' };

            owner.name = first_name;

            if (first_name && last_name) {
                owner.name = first_name + ' ' + last_name;
            } else {
                owner.name = $('#recurly-payment-data').data('full-name');
            }

            owner.email = $('#billing_email').val();
            owner.phone = $('#billing_phone').val();

            /* Stripe does not like empty string values so
             * we need to remove the parameter if we're not
             * passing any value.
             */
            if (typeof owner.phone === 'undefined' || 0 >= owner.phone.length) {
                delete owner.phone;
            }

            if (typeof owner.email === 'undefined' || 0 >= owner.email.length) {
                if ($('#billing_email').length) {
                    owner.email = $('#billing_email').val();
                } else {
                    delete owner.email;
                }
            }

            if (typeof owner.name === 'undefined' || 0 >= owner.name.length) {
                delete owner.name;
            }

            owner.address.first_name = first_name;
            owner.address.last_name = last_name;
            owner.address.address1 = $('#billing_address_1').val() || wc_recurly_params.billing_address_1;
            owner.address.address2 = $('#billing_address_2').val() || wc_recurly_params.billing_address_2;
            owner.address.city = $('#billing_city').val() || wc_recurly_params.billing_city;
            owner.address.state = $('#billing_state').val() || wc_recurly_params.billing_state;
            owner.address.postal_code = $('#billing_postcode').val() || wc_recurly_params.billing_postcode;
            owner.address.country = $('#billing_country').val() || wc_recurly_params.billing_country;

            return {
                owner: owner,
            };
        },

        /**
         * Initiates the creation of a Source object.
         *
         * Currently this is only used for credit cards and SEPA Direct Debit,
         * all other payment methods work with redirects to create sources.
         */
        createSource: function() {
            var extra_details = wc_recurly_form.getOwnerDetails().owner.address;
            return recurly.token(elements, extra_details, (err, token) => {
                if (err) {
                    $(document.body).trigger('recurlyError', err);
                    return;
                } else {
                    // console.log(token)
                    wc_recurly_form.reset();

                    wc_recurly_form.form.append(
                        $('<input type="hidden" />')
                        .addClass('recurly-token')
                        .attr('name', 'recurly-token')
                        .attr('data-recurly', 'token')
                        .val(token.id)
                    );

                    if ($('form#add_payment_method').length || $('#wc-recurly-change-payment-method').length) {
                        wc_recurly_form.sourceSetup(token);
                        return;
                    }
                    if ($('#payment_method_recurly').hasClass('supports-payment-method-changes')) {
                        wc_recurly_form.sourceSetup(token);
                        return;
                    }
                    wc_recurly_form.form.trigger('submit');

                }
            });
        },

        /**
         * Authenticate Source if necessary by creating and confirming a SetupIntent.
         *
         * @param {Object} response The `recurly.createSource` response.
         */
        sourceSetup: function(response) {
            $.post({
                url: wc_recurly_form.getAjaxURL('create_setup_intent'),
                dataType: 'json',
                data: {
                    recurly_source_id: response.id,
                    nonce: wc_recurly_params.add_card_nonce,
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert('Please reload this page and try again.');
                }
            }).done(function(serverResponse) {
                if ('success' === serverResponse.status) {
                    if ($('form#add_payment_method').length) {
                        // $( wc_recurly_form.form ).off( 'submit', wc_recurly_form.form.onSubmit );
                    }
                    wc_recurly_form.unmountElements();
                    wc_recurly_form.mountElements();
                    wc_recurly_form.unblock();
                    $('#place_order').val('Payment method changed successfully');
                    setTimeout(function() {
                        window.location.href = wc_recurly_params.get_view_order_url;
                    }, 1000);
                    return;
                } else if ('error' == serverResponse.status) {
                    var errorContainer = $('div.recurly-source-errors');
                    var message = serverResponse.error.message;
                    $(errorContainer).html('<ul class="woocommerce_error woocommerce-error wc-recurly-error"><li /></ul>');
                    $(errorContainer).find('li').html(message); // Prevent XSS
                    wc_recurly_form.unblock();
                    return;
                }
            });

        },

        /**
         * Performs payment-related actions when a checkout/payment form is being submitted.
         *
         * @return {boolean} An indicator whether the submission should proceed.
         * WooCommerce's checkout.js stops only on `false`, so this needs to be explicit.
         */
        onSubmit: function() {
            if (!wc_recurly_form.isStripeChosen()) {
                return true;
            }

            // If a source is already in place, submit the form as usual.
            if (wc_recurly_form.isStripeSaveCardChosen() || wc_recurly_form.hasSource()) {
                return true;
            }
            wc_recurly_form.block();
            wc_recurly_form.createSource();

            return false;
        },

        /**
         * If a new credit card is entered, reset sources.
         */
        onCCFormChange: function() {
            wc_recurly_form.reset();
        },

        /**
         * Removes all Stripe errors and hidden fields with IDs from the form.
         */
        reset: function() {
            $('.wc-recurly-error, .recurly-token').remove();
        },

        /**
         * Displays recurly-related errors.
         *
         * @param {Event}  e      The jQuery event.
         * @param {Object} result The result of Stripe call.
         */
        onError: function(e, result) {
            // console.log(e)
            var errorContainer = '',
                message = '';
            for (let i = 0; i < result.fields.length; i++) {
                // console.log(result.fields[i]);
                // result.fields.forEach((e) => {
                errorContainer = $('div.woocommerce-notices-wrapper').first();

                // return;
                if (result.fields[i] == 'number' || result.fields[i] == 'month' || result.fields[i] == 'year' || result.fields[i] == 'cvv') {
                    errorContainer = $('div.recurly-source-errors');
                    message = '<li>Your card details is invalid</li>';
                    break;
                } else {
                    if (result.fields[i] == 'first_name') {
                        message += '<li><strong>Billing First name</strong> is a required field.</li>';
                    }
                    if (result.fields[i] == 'last_name') {
                        message += '<li><strong>Billing Last name</strong> is a required field.</li>';
                    }
                    if (result.fields[i] == 'address1') {
                        message += '<li><strong>Billing Street address</strong> is a required field.</li>';
                    }
                    if (result.fields[i] == 'city') {
                        message += '<li><strong>Billing Town / City</strong> is a required field.</li>';
                    }
                    if (result.fields[i] == 'postal_code') {
                        message += '<li><strong>Billing Postcode</strong> is a required field.</li>';
                    }
                    if (result.fields[i] == 'state') {
                        message += '<li><strong>Country / Region</strong> is a required field.</li>';
                    }
                    if (result.fields[i] == 'country') {
                        message += '<li><strong>Country / Region</strong> is a required field.</li>';
                    }
                    // break;
                }
            }
            var selectedMethodElement = wc_recurly_form.getSelectedPaymentElement().closest('li');
            var savedTokens = selectedMethodElement.find('.woocommerce-SavedPaymentMethods-tokenInput');

            wc_recurly_form.reset();
            $('.woocommerce-NoticeGroup-checkout').remove();
            $(errorContainer).html('<ul class="woocommerce_error woocommerce-error wc-recurly-error"><li /></ul>');
            $(errorContainer).find('li').html(message); // Prevent XSS
            // console.log(errorContainer); // Leave for troubleshooting.

            if ($('.wc-recurly-error').length) {
                $('html, body').animate({
                    scrollTop: ($('.wc-recurly-error').offset().top - 200)
                }, 200);
            }
            wc_recurly_form.unblock();
            $.unblockUI(); // If arriving via Payment Request Button.
        },

        /**
         * Displays an error message in the beginning of the form and scrolls to it.
         *
         * @param {Object} error_message An error message jQuery object.
         */
        submitError: function(error_message) {
            $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
            wc_recurly_form.form.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>');
            wc_recurly_form.form.removeClass('processing').unblock();
            wc_recurly_form.form.find('.input-text, select, input:checkbox').trigger('blur');

            var selector = '';

            if ($('#add_payment_method').length) {
                selector = $('#add_payment_method');
            }

            if ($('#order_review').length) {
                selector = $('#order_review');
            }

            if ($('form.checkout').length) {
                selector = $('form.checkout');
            }

            if (selector.length) {
                $('html, body').animate({
                    scrollTop: (selector.offset().top - 100)
                }, 500);
            }

            $(document.body).trigger('checkout_error');
            wc_recurly_form.unblock();
        },

        /**
         * Handles changes in the hash in order to show a modal for PaymentIntent/SetupIntent confirmations.
         *
         * Listens for `hashchange` events and checks for a hash in the following format:
         * #confirm-pi-<intentClientSecret>:<successRedirectURL>
         *
         * If such a hash appears, the partials will be used to call `recurly.handleCardPayment`
         * in order to allow customers to confirm an 3DS/SCA authorization, or recurly.handleCardSetup if
         * what needs to be confirmed is a SetupIntent.
         *
         * Those redirects/hashes are generated in `WC_Gateway_Stripe::process_payment`.
         */
        onHashChange: function() {
            var partials = window.location.hash.match(/^#?confirm-(pi|si)-([^:]+):(.+)$/);

            if (!partials || 4 > partials.length) {
                return;
            }

            var type = partials[1];
            var intentClientSecret = partials[2];
            var redirectURL = decodeURIComponent(partials[3]);

            // Cleanup the URL
            window.location.hash = '';

            wc_recurly_form.openIntentModal(intentClientSecret, redirectURL, false, 'si' === type);
        },

        maybeConfirmIntent: function() {
            if (!$('#recurly-intent-id').length || !$('#recurly-intent-return').length) {
                return;
            }

            var intentSecret = $('#recurly-intent-id').val();
            var returnURL = $('#recurly-intent-return').val();

            wc_recurly_form.openIntentModal(intentSecret, returnURL, true, false);
        },

        /**
         * Opens the modal for PaymentIntent authorizations.
         *
         * @param {string}  intentClientSecret The client secret of the intent.
         * @param {string}  redirectURL        The URL to ping on fail or redirect to on success.
         * @param {boolean} alwaysRedirect     If set to true, an immediate redirect will happen no matter the result.
         *                                     If not, an error will be displayed on failure.
         * @param {boolean} isSetupIntent      If set to true, ameans that the flow is handling a Setup Intent.
         *                                     If false, it's a Payment Intent.
         */
        openIntentModal: function(intentClientSecret, redirectURL, alwaysRedirect, isSetupIntent) {
            recurly[isSetupIntent ? 'handleCardSetup' : 'handleCardPayment'](intentClientSecret)
                .then(function(response) {
                    if (response.error) {
                        throw response.error;
                    }

                    var intent = response[isSetupIntent ? 'setupIntent' : 'paymentIntent'];
                    if ('requires_capture' !== intent.status && 'succeeded' !== intent.status) {
                        return;
                    }

                    window.location = redirectURL;
                })
                .catch(function(error) {
                    if (alwaysRedirect) {
                        window.location = redirectURL;
                        return;
                    }

                    $(document.body).trigger('recurlyError', { error: error });
                    wc_recurly_form.form && wc_recurly_form.form.removeClass('processing');

                    // Report back to the server.
                    $.get(redirectURL + '&is_ajax');
                });
        },

        /**
         * Prevents the standard behavior of the "Renew Now" button in the
         * early renewals modal by using AJAX instead of a simple redirect.
         *
         * @param {Event} e The event that occured.
         */
        onEarlyRenewalSubmit: function(e) {
            e.preventDefault();

            $.ajax({
                url: $('#early_renewal_modal_submit').attr('href'),
                method: 'get',
                success: function(html) {
                    var response = JSON.parse(html);

                    if (response.recurly_sca_required) {
                        wc_recurly_form.openIntentModal(response.intent_secret, response.redirect_url, true, false);
                    } else {
                        window.location = response.redirect_url;
                    }
                },
            });

            return false;
        },
    };

    wc_recurly_form.init();
});