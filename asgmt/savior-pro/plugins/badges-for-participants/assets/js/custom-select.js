(function ($) {
    'use strict';

    $('#badges .column-color select').each(function () {

        var $this = $(this), numberOfOptions = $(this).children('option').length;

        $this.addClass('select-hidden');
        $this.wrap('<div class="select"></div>');
        $this.after('<div class="select-styled"></div>');

        var selected = $(this).val();
        var $selected = $(this).find('option[value="'+selected+'"]');

        var $styledSelect = $this.next('div.select-styled');
        $styledSelect.html('<span class="color" style="background:'+$selected.val()+'"></span><span class="text">'+$selected.text()+'</span>');

        var $list = $('<ul />', {
            'class': 'select-options'
        }).insertAfter($styledSelect);

        for (var i = 0; i < numberOfOptions; i++) {
            $('<li rel="'+$this.children('option').eq(i).val()+'" '+($this.children('option').eq(i).val() === selected ? 'class="active-item"' : '')+'><span class="color" style="background:'+$this.children('option').eq(i).val()+'"></span><span class="text">'+$this.children('option').eq(i).text()+'</span>')
                .appendTo($list);
        }

        var $listItems = $list.children('li');

        $styledSelect.click(function (e) {
            e.stopPropagation();
            $('div.select-styled.active').not(this).each(function () {
                $(this).removeClass('active').next('ul.select-options').hide();
            });
            $(this).toggleClass('active').next('ul.select-options').toggle();
        });

        $listItems.click(function (e) {
            e.stopPropagation();

            var currentVal = $(this).attr('rel');

            $this.val(currentVal);
            $selected = $this.find('option[value="'+currentVal+'"]');

            $listItems.removeClass('active-item');

            $(this).addClass('active-item');

            $styledSelect.html('<span class="color" style="background:'+$selected.val()+'"></span><span class="text">'+$selected.text()+'</span>').removeClass('active');


            $list.hide();
            //console.log($this.val());
        });

        $(document).click(function () {
            $styledSelect.removeClass('active');
            $list.hide();
        });

    });

})(jQuery);