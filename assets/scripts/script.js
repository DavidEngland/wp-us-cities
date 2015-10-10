(function(){
    'use strict';

    var imageLoader = new Image();

    var keys = {
        TAB: 9,
        ENTER: 13,
        SHIFT: 16,
        CTRL: 17,
        ALT: 18,
        CAPS: 20,
        ESC: 27,
        END: 35,
        HOME: 36,
        LEFT: 37,
        UP: 38,
        RIGHT: 39,
        DOWN: 40
    };

    function renderData(data, element) {
        var eraseElementSlug = true;

        element.parent().children('div.wp-city-output').html('');

        if(data && data.cities && data.cities.length > 0){
            for(var i in data.cities){
                var $city = jQuery('<div class="wp-city-item"/>');
                $city.text(data.cities[i].city);
                $city.attr('slug', data.cities[i].slug);
                $city.attr('hovered', 'false');

                if(element.val() === data.cities[i].city){
                    element.attr('slug', data.cities[i].slug);
                    eraseElementSlug = false;
                }

                $city.click(function(e){
                    var field = jQuery(this).parent().parent().children('input[type="text"].wp-city-term');
                    field.attr('slug', jQuery(this).attr('slug'));
                    field.val(jQuery(this).text());
                    field.focus();

                    jQuery(this).parent().hide();
                    jQuery(this).parent().html('');
                });

                element.parent().children('div.wp-city-output').append($city);
            }
        } else {
            var $notFound = jQuery('<div class="wp-city-notfound"/>');
            $notFound.text('City not found.');
            element.parent().children('div.wp-city-output').append($notFound);
        }

        if(eraseElementSlug === true){
            element.attr('slug','');
        }
    }

    function fixPositionOutput(){
        jQuery('div.wp-city-output').each(function (){
            var container = jQuery(this).parent();
            var field = jQuery(this).parent().children('input[type="text"].wp-city-term');

            jQuery(this).width(field.outerWidth());
//            jQuery(this).css('top', container.offset().top + field.outerHeight());
//            jQuery(this).css('left', container.offset().left);
            
            console.log(container.offset().top + ' + ' + field.outerHeight());
            console.log(container.offset().left);
        });
    }

    function isValidKey(code){
        if(code === keys.TAB ||  code === keys.ENTER || code === keys.SHIFT ||
                code === keys.CTRL || code === keys.ALT || code === keys.CAPS ||
                code === keys.ESC || code === keys.END || code === keys.HOME ||
                code === keys.LEFT || code === keys.UP || code === keys.RIGHT ||
                code === keys.DOWN) {
            return false;
        }

        return true;
    }

    window.onload = function() {
        jQuery('input[type="text"].wp-city-term').keyup(function(e){
            
            var code = e.keyCode || e.which;
            var field = jQuery(this);
            var accessSlug = false;
            var slug = field.attr('slug');

            if(code === 13){
                if(typeof slug !== typeof undefined && slug !== false && slug !== ''){
                    accessSlug = true;
                } else {
                    var itemHovered = field.parent().find('div.wp-city-item[hovered="true"]');

                    if(itemHovered.length > 0){
                        slug = itemHovered.attr('slug');
                        accessSlug = true;

                        field.val(itemHovered.text());
                        field.attr('slug', slug);

                        field.parent().children('div.wp-city-output').html('');

                        field.parent().children('div.wp-city-output').hide();
                    }
                }
            }

            if(accessSlug === true){
                window.open('/' + slug, '_self');
            } else if(code === keys.DOWN || code === keys.UP){
                var cityItems = field.parent().find('div.wp-city-item');

                if(cityItems.length > 0){
                    var itemHovered = field.parent().find('div.wp-city-item[hovered="true"]');

                    if(itemHovered.length > 0){
                        if(code === keys.DOWN){
                            if(itemHovered.is(':last-child') === false){
                                itemHovered.next().attr('hovered','true');
                                itemHovered.first().attr('hovered','false');
                            }
                        } else {
                            if(itemHovered.prev().length > 0){
                                itemHovered.prev().attr('hovered','true');
                            }

                            itemHovered.last().attr('hovered','false');
                        }
                    } else if(code === keys.DOWN) {
                        cityItems.first().attr('hovered','true');
                    }
                }
            } else if(isValidKey(code)) {
                var term = field.val();

                if(term.length >= 3){
                    field.parent().children('div.wp-city-output').html('');

                    field.parent().children('div.wp-city-output').append(imageLoader);

                    field.parent().children('div.wp-city-output').show();

                    jQuery.getJSON('/search-city/' + term, function(data) {
                        renderData(data, field);
                    });
                } else {
                    field.parent().children('div.wp-city-output').html('');

                    field.parent().children('div.wp-city-output').hide();
                }
            }
        });

        imageLoader.src = jQuery('input[type="text"].wp-city-term').attr('path') + '/assets/images/loader.gif';

        fixPositionOutput();
    };

    window.onresize = function(){
        fixPositionOutput();
    };
})();
