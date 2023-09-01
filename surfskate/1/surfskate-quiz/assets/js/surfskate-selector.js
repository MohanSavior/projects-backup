const checkvalidation = (step, status) => {
    if (!status) {
        jQuery(`.container .step-fieldset:nth-child(${step}) .next`).attr('disabled', 'disabled');
        jQuery('.item-next').attr('disabled', 'disabled');
    } else {
        jQuery(`.container .step-fieldset:nth-child(${step}) .next`).removeAttr('disabled');
        jQuery('.item-next').removeAttr('disabled');
    }
}
(function ($) {
    "use strict";
    
    let ulLiSteps = 9;
    if( $("body").hasClass('page-template-surfsketquiz-template-v1') && surfsket_ajax_object.current_email )
    {
        $('#progressbar li:first-child').remove();
        $("#surf-step-01").remove();
        setTimeout(() => {
            $('#progressbar li').each(function(e){
                $(this).find('a').text(1+e);
            });
            $("a[data-id='surf-step-02']").trigger('click');
            $('#surf-step-02').show();
        }, 200);
        ulLiSteps = 8;
    }else if( $("body").hasClass('page-template-surfsketquiz-template-v2') && surfsket_ajax_object.current_email )
    {
        
        $('#progressbar li:nth-child(9)').remove();
        $("#surf-step-09").remove();
        setTimeout(() => {
            $('#progressbar li').each(function(e){
                $(this).find('a').text(1+e);
            });
            // $("a[data-id='surf-step-02']").trigger('click');
            $('#surf-step-08 .next').text('See Results');
        }, 200);
        ulLiSteps = 8;  
    }
    for (let i = 1; i <= ulLiSteps; i++) {
        checkvalidation(i, false);
    }
    const validateEmail = (email) => {
        return String(email)
            .toLowerCase()
            .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            );
    };
    const validate = (e) => {
        let step = $('#useremail').parents('fieldset').attr('id') == 'surf-step-09' ? 9 : 1;
        const email = $('#useremail').val();
        if (validateEmail(email)) {
            $('#useremail').css('border-color', 'green');
            checkvalidation(step, true);
        } else {
            checkvalidation(step, false);
            $('#useremail').css('border-color', 'red');
        }
        return false;
    }
    $('#firstname, #lastname, #useremail').on('change blur', function () {
        if( $('#firstname').val() !== '' && $('#lastname').val() !== '' && $('#useremail').val() !== '' ) {
            validateEmail($('#useremail').val());
        }        
    });
    $('#useremail').on('input', validate);
    if($('body').hasClass('page-template-surfsketquiz-template-v2')){
        // $('.surfskating-filtering').hide();
        $('.showMainContent').css('margin-bottom','50px');
        $('#msform').css('margin-bottom','50px');
    }
    jQuery('#stance_width, input[type="radio"]').on('change', function () {
        var index = $(this).parents('fieldset').index() + 1;
        if($('body').hasClass('page-template-surfsketquiz-template-v2')){
            checkvalidation(index, true);
        }else{
            ajaxForFilterSurfSkate(index, 0);
        }
    });
    jQuery('input[name="suitability[]"]').on('click', function () {
        var index = $(this).parents('fieldset').index() + 1;
        if($('body').hasClass('page-template-surfsketquiz-template-v2')){
            checkvalidation(index, true);
        }else{
            ajaxForFilterSurfSkate(index, 0);
        }
    });
    jQuery('#msform .previous').on('click', function () {
        $('.item-next').removeAttr('disabled');
        $(`#progressbar li:nth-child(${$(this).parents('fieldset').index()})`).addClass('showOne').siblings().removeClass('showOne');
    });
    jQuery('#msform .next').on('click', function () {
        let step = $(this).attr('type') == 'previous' ? $(this).parents('fieldset').index() : $(this).parents('fieldset').index() + 1;
        let checkEmail = surfsket_ajax_object.current_email ? 8 : 9;
        if ( step === checkEmail && jQuery('body').hasClass('page-template-surfsketquiz-template-v1') ) {
            ajaxForFilterSurfSkate(step, 1);
        }
        else if( step === checkEmail && jQuery('body').hasClass('page-template-surfsketquiz-template-v2') )
        {
            ajaxForFilterSurfSkate(step, 1);
            $(".final-results").css({'height':'auto !important'});
        }else if(step === 8 && jQuery('body').hasClass('page-template-surfsketquiz-template'))
        {
            ajaxForFilterSurfSkate(step, 1);
        }
        if (step > 1) {
            $('.item-previous').show();
        }
        $('.item-next').attr('disabled', 'disabled');
        $(`#progressbar li:nth-child(${step + 1})`).addClass('showOne').siblings().removeClass('showOne');
    });
    $('.surfskating-filtering .total-cout').text(`${surfsket_ajax_object.total_post.publish} models`);
    $(`#progressbar li:nth-child(0)`).addClass('showOne');
    function ajaxForFilterSurfSkate(step, final) {
        checkvalidation(step, true);

        let FormData = jQuery('#msform').serializeArray();
        var suitability_val = [];
        $('.suitability:checkbox:checked').each(function (i) {
            suitability_val[i] = $(this).val();
        });
        let FormDataObj = {};
        FormDataObj['action'] = 'filter_surfsket';
        FormData.forEach(function (res, index) {
            if (res.name !== 'suitability[]') {
                FormDataObj[res.name] = res.value;
            }
        });
        FormDataObj['suitability'] = suitability_val;
        FormDataObj['step'] = parseInt(step);
        FormDataObj['final'] = final;
        if (!final) {
            // $('html, body').animate({
            //     scrollTop: $(".surfskate-models").offset().top
            // });
        } else {
            //          $('.final-results').append($("#loader").clone());
            $("#loader").clone().insertAfter(".model-list");
            $('.step-fieldset.final-results').css("height", "auto");
        }
        if (step > 1) {
            $('.item-previous').show();
        }
        let showHide = (jQuery(window).width() < 786) ? 'none' : 'block';
        let btnDisabled = false;
        jQuery(`fieldset .next`).each(function () {
            btnDisabled = (!jQuery(this).attr('disabled')) ? true : false;
        });
        jQuery.ajax({
            url: surfsket_ajax_object.ajax_url,
            type: "POST",
            // dataType: 'JSON',
            data: FormDataObj,
            beforeSend: function () {
                $('body').find('#loader').removeClass('display-none');
                // checkvalidation(step, false);
            },
            success: function (response) {
                // checkvalidation(step, true);
                if (response.success) {
                    setTimeout(() => {
                        var $col = $('.surfskating-item > li');
                        var $maxHeight = 0;
                        $col.each(function () {
                            var $thisHeight = $(this).outerHeight();
                            if ($thisHeight > $maxHeight) {
                                $maxHeight = $thisHeight;
                            }
                        });
                        $col.height($maxHeight);
                    }, 100);
                    //=====================================================================================					
                    const postCount = response.data.post_count;
                    const colcount = Object.keys(response.data.data).length;
                    $('.loding-result').hide();
                    $('.surfskating-filters-btns-box').attr('data-col', 'brands-col-' + colcount);
                    $('.filtering-items-container').attr('data-col', 'items-col-' + colcount);
                    $('.container .total-cout').text(`${postCount} models`);
                    if (btnDisabled) { $("#progressbar li:last-child .daynamic-val").text(`${postCount} models`); }
                    const brands = (brandName, brandImage, PostCount, brandId) => {
                        return `<button id="${brandId}" data-count="${PostCount}" type="button" class="btn surfskating-filter-btn filtering-action" data-filtering="${brandName}">
                                <figure class="figure">
                                    <img src="${brandImage}" alt="${brandName}">
                                </figure>
                                <p>${brandName}</p>
                            </button>`;
                    };
                    let brandsHtml = '',
                        postHtml = '',
                        postHtmlli = '',
                        firstEl = false,
                        accordionTab = '';
                    for (const [brandId, value] of Object.entries(response.data.data)) {
                        // 							console.log(value.brand_id)
                        brandsHtml += brands(value.brand_name, value.brand_image, value.post_data.length, value.brand_id);
                        postHtmlli = value.post_data.map((pData) => {
                            var liEl = `<li id='data-modal-${pData.term_id}' type='button' data-data-modal='${JSON.stringify(pData)}' class='btn btn-modal' data-bs-toggle='modal' data-bs-target='#fullDetails'>`;
                            var lidiv = final ? liEl : '<li>';
                            return `${lidiv}
                                        <div class="cat-item-details">
                                            <figure class="figure">
                                                <img src="${pData.post_image}" alt="${pData.name}">
                                            </figure>
                                            <p>${pData.name}</p>
                                        </div>
                                    </li>`;
                        });
                        postHtml += `<ul style="display:${showHide}" data-id="${value.brand_id}" class="surfskating-item filtering-item">${postHtmlli.join('')}</ul>`;
                        // console.log(brandId)
                        firstEl = brandId == 0 ? 'true' : 'false';
                        accordionTab += `<div class="accordion-item">
												<h2 class="accordion-header" id="headingOne_${value.brand_id}">
												  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_${value.brand_id}" aria-expanded="false" aria-controls="collapse_${value.brand_id}">
													<figure class="figure">
														<img src="${value.brand_image}" alt="${value.brand_name}">
													</figure>
													<p>${value.brand_name}</p>
                                                    <span class="sket-count">${value.post_data.length}</span>
												  </button>
												</h2>
												<div id="collapse_${value.brand_id}" class="accordion-collapse collapse" aria-labelledby="headingOne_${value.brand_id}" data-bs-parent="#accordionSurfskat">
												  <div class="accordion-body">
													<ul data-id="${value.brand_id}" class="surfskating-item filtering-item">${postHtmlli.join('')}</ul>
												  </div>
												</div>
											  </div>`;
                    }
                    //=====================================================================================
                    $('.surfskating-content').html('');
                    $('.surfskating-filters-btns-box').html('');
                    $('.final-results .model-list').html('');
                    if (jQuery(window).width() < 786) {
                        let accordion = `<div class="accordion" id="accordionSurfskat">${accordionTab}</div>`;
                        $('.final-results .surfskating-filters-btns-box').html('').html(accordion);
                        $('.surfskating-filters-btns-box').html('').html(accordion);
                    } else {
                        if (final) {
                            $('#loader').hide();
                            window.scrollTo({ top: document.getElementById("msform").offsetTop, behavior: 'smooth' });
                            $('.final-results .surfskating-filters-btns-box').html('').html(brandsHtml);
                            $('.final-results .filtering-items-container').html('').html(postHtml);
                            if($('body').hasClass('page-template-surfsketquiz-template-v1')){
                                $('.surfskating-filtering').hide();
                            }
                        }
                        $('.surfskating-filters-btns-box').html(brandsHtml);
                        $('.surfskating-content').html(postHtml);
                    }
                } else {
                    $('.surfskating-content').html('<div class="no-surf-skate-found"><p>No records found!</p></div>');
                    $('.surfskating-filters-btns-box').html('');
                    $('.container .total-cout').text(`0 models`);
                }
                // 				surfskatHideShow();
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                $('#loader').addClass('display-none');
            },
            complete: function () {
                $('body').find('#loader').addClass('display-none');
            }
        });
    }
    $('body').on('click', 'li[id^="data-modal-"]', function (e) {
        let modelDataObj = $(this).data('data-modal');
        let modalHtml = $('#fullDetails');
        let imgEl = $('#prodSlideIndicator');
        imgEl.css({ 'display': 'none' });
        modalHtml.find('.prod-slider .prod-slider-item').html(`<img src='${modelDataObj.post_image}'>`);
        modalHtml.find('.prod-descp h2').text(`${modelDataObj.title}`);
        modalHtml.find('.brand-name').text(`${modelDataObj.brand}`);
        modalHtml.find('.brand-price').text(`$${modelDataObj.price}`);
        modalHtml.find('.prod-descp p').text(`${modelDataObj.content}`);
        var data = { 'Length': modelDataObj.length, 'Wheelbase': modelDataObj.wheelbase, 'Stance Width Range': modelDataObj.stance_width_range, 'Width': modelDataObj.width, 'Concave': modelDataObj.concave };
        var DetailsEl = '';
        Object.keys(data).forEach(function (key) {
            DetailsEl += `<li><span class="feat-name">${key}</span><span class="feat-value">${data[key]}</span>
        </li>`;
        });
        modalHtml.find('.prod-descp ul').html(`${DetailsEl}`);
    });
})(jQuery);
/***page script****/

jQuery(document).ready(function ($) {

    // Selectpicker
    $('select').selectpicker();

    // Radio
    var $radioButtons = $('input[type="radio"]');
    $radioButtons.click(function () {
        $radioButtons.each(function () {
            $(this).parent().toggleClass('isActive', this.checked);
        });
    });;

    // Checkbox
    $(function () {
        $('input[type="checkbox"]').on('change', function () {
            $(this).parent().toggleClass("isActive", this.checked)
        }).change();
    });

    // Step Form
    var current_fs, next_fs, previous_fs; //fieldsets
    var opacity;
    var current = 1;
    var steps = $("fieldset").length;

    setProgressBar(current);

    $(".next").click(function () {
        current_fs  = $(this).parent();
        next_fs     = $(this).parent().next();
        let $this   = $(this);
        if( $(this).attr('id') == 'information' && $("#entry_id").val() == '' )
        {
            const url  = surfsket_ajax_object.ajax_url;
            const data = {
                'action'    : 'information',
                'firstname' : $('#firstname').val(),
                'lastname'  : $('#lastname').val(),
                'useremail' : $('#useremail').val(),
                'entry_id'  : $("#entry_id").val(),
                'nonce'     : surfsket_ajax_object.ajax_nonce
            };
            $.ajax({
                url: url,
                type: 'post',
                data: data,
                beforeSend: function(){
                    jQuery('#information').html(`Next step <div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>`);
                    checkvalidation(1, false);
                },
                success: function( result ) {
                    if( result.success )
                    {
                        $("#entry_id").val(result.data.entry_id);
                        $("#progressbar li").eq($this.parent().next().index()).addClass("active");
                        nextClick( next_fs, current_fs );
                        $('.step-fieldset.final-results').css("height", "auto");
                        if($('body').hasClass('page-template-surfsketquiz-template-v1')){
                            $('.step-fieldset.final-results').css("height", "50px");
                        }
                    }else{
                        var response = Object.keys(result.data);
                        if( response.includes('1') )
                        {
                            $('#firstname').css('border-color','red');
                            $('#lastname').css('border-color','red');
                            $('#errors-meg').html(`<span style="color:red;font-size: 16px;">Please enter full name</span>`);
                        }else if( response.includes('3') )
                        {
                            $('#useremail').css('border-color','red');
                            $('#errors-meg').html(`<span style="color:red;font-size: 16px;">Please enter valid email</span>`);
                        }else{
                            $('#errors-meg').html(`<span style="color:red;font-size: 16px;">Please enter valid information</span>`);
                        }
                        setTimeout(() => {
                            $('#lastname').css('border-color','');
                            $('#firstname').css('border-color','');
                            $('#useremail').css('border-color','');
                            $('#errors-meg').html('');
                        }, 3000);
                    }
                    checkvalidation(1, true);
                    jQuery('#information').html('Next step');
                },
                error: function( error )
                {
                    checkvalidation(1, true);
                    $('#errors-meg').html('');
                    jQuery('#information').html('Next step');
                }
            })
        }else{
            $("#progressbar li").eq($(this).parent().next().index()).addClass("active");
            nextClick( next_fs, current_fs );
        }
        if( $('body').hasClass('page-template-surfsketquiz-template-v2') && next_fs.attr('id') == 'surf-step-01' ){
            jQuery('#msform .surfskating-filtering').show();
        }else if( $('body').hasClass('page-template-surfsketquiz-template-v2') ){
            jQuery('#msform .surfskating-filtering').hide();
        }else{
            jQuery('#msform .surfskating-filtering').show();
        }
    });

    const nextClick = ( next_fs, current_fs ) => {
        next_fs.show();
        current_fs.animate({
            opacity: 0
        }, {
            step: function (now) {
                // for making fielset appear animation
                opacity = 1 - now;
                current_fs.css({
                    'display': 'none',
                    'position': 'relative',
                    'visibility': 'hidden',
                    'left': '100px'
                });
                next_fs.css({
                    'opacity': opacity,
                    'visibility': 'visible',
                    'left': '0px'
                });
            },
            duration: 500
        });
        setProgressBar(++current);
    }

    $(".previous").click(function () {

        current_fs = $(this).parent();
        previous_fs = $(this).parent().prev();
        $("#progressbar li").eq($(this).parent().index()).removeClass("active");
        previous_fs.show();
        // hide the current fieldset with style
        current_fs.animate({
            opacity: 0
        }, {
            step: function (now) {
                // for making fielset appear animation
                opacity = 1 - now;

                current_fs.css({
                    'display': 'none',
                    'position': 'relative',
                    'visibility': 'hidden',
                    'left': '100px'
                });
                previous_fs.css({
                    'opacity': opacity,
                    'visibility': 'visible',
                    'left': '0px'
                });
            },
            duration: 500
        });
        setProgressBar(--current);
        if( $('body').hasClass('page-template-surfsketquiz-template-v2') && next_fs.attr('id') == 'surf-step-01' ){
            jQuery('#msform .surfskating-filtering').show();
        }else if( $('body').hasClass('page-template-surfsketquiz-template-v2') ){
            jQuery('#msform .surfskating-filtering').hide();
        }else{
            jQuery('#msform .surfskating-filtering').show();
        }
    });

    function setProgressBar(curStep) {
        var percent = parseFloat(100 / steps) * curStep;
        percent = percent.toFixed();
        $(".progress-bar").css("width", percent + "%")
        $('.progress-bar').find('.count').text(percent + '%')
    }

    $(".submit").click(function () {
        return false;
    })

    // Final results Height Auto
    $('.btn.btn-sbmt').click(function () {
        $('.step-fieldset.final-results').css("height", "auto");
    });

    // Modal
    var proModItem = '.final-results .model-list .model-item';

    $(proModItem).on('click', function () {
        $(proModItem).removeClass('isActive');
        $(this).addClass('isActive');
    });

    // Slider
    //$('.prod-modal .prod-item .prod-slider .prod-slider-item').zoom();
    // $('#prod-item-01').zoom();
    // $('#prod-item-02').zoom();
    // $('#prod-item-03').zoom();

    jQuery('.surfskate-models button.item-next, .surfskate-models button.item-previous').on('click', function () {
        if (jQuery(this).attr('name') == 'item-next') {
            jQuery('fieldset:visible').find('button.next').click();
            $(this).attr('disabled', 'disabled');
        } else {
            jQuery('fieldset:visible').find('button.previous').click();
            $('.item-next').removeAttr('disabled');
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    // Show Filter Div
    $('.showMainContent').on("click", function () {
        $(this).parents('.welcom-page').hide();

    });
    $('.showMainContent').on('click', function () {
        $('.welcom-page').hide();
    });

    $('.showMainContent').on('click', function () {
        $('#mainWizard').show();
        $("html").animate({ scrollTop: 0 }, "fast");
    });
    /*********** HEADER PROGRESS BAR SUB TITLE SHOW *************/
    jQuery('#stance_width, input[type="radio"]').on('change', function () {
        var index = jQuery(this).parents('fieldset').index();
        let subTitle = (index == 0) ? jQuery(this).val() : jQuery(this).data('subtitle');
        if( typeof subTitle == 'undefined')
        {
            subTitle = jQuery(this).val();
        }
        jQuery(`.daynamic-val:eq(${index})`).html(`${subTitle}`);
    });
    /*********** HEADER PROGRESS BAR SUB TITLE *************/
    jQuery('#progressbar a').on('click', function (e) {
        e.preventDefault();
        var liEl = jQuery(this).parent('li');
        var q = liEl.index();
        if (q <= 7) {
            if(!$('body').hasClass('page-template-surfsketquiz-template-v2')){
                jQuery('#msform .surfskating-filtering').show();
            }
            jQuery('.final-results:visible').hide();
        } else {
            jQuery('#msform .surfskating-filtering:visible').hide();
            jQuery('.final-results').show();
        }
        jQuery(`#progressbar li`).removeClass("active");
        jQuery('body').find('fieldset:visible').css({ 'display': 'none', 'position': 'relative', 'visibility': 'hidden', 'left': '100px' });
        let activeEl = null;
        for (let i = 0; i <= q; i++) {
            if (!jQuery(`fieldset:nth-child(${i}) .next`).attr('disabled')) {
                jQuery(`#progressbar li:eq(${i})`).addClass('active');
                activeEl = i;
            }
        }
        jQuery('body').find(`fieldset:nth-child(${activeEl + 1})`).css({ 'display': 'block', 'opacity': 1, 'visibility': 'visible', 'left': '0px' });
    });

    // Mobile Models Accordion

    if (jQuery(window).width() < 786) {
        jQuery('body').find(`.filtering-items-container ul`).hide();
        jQuery('body').find('.surfskating-filters-btns-box button').each(function () {
            jQuery(this).append(`<span>${jQuery(this).data('count')}</span>`);
        });
    }
    surfskatHideShow();
});
function surfskatHideShow() {
    if (jQuery(window).width() < 786) {
        jQuery('body').find(`.filtering-items-container ul`).hide();
        jQuery('body').on('click', '.surfskating-filters-btns-box button', function () {
            let DataId = jQuery(this).attr('id');
            jQuery('body').find(`.filtering-items-container ul`).hide();
            jQuery('body').find(`.filtering-items-container ul[data-id="${DataId}"]`).show();
        });

    }
}