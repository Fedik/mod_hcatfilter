/**
 * jquery.relatedSelect.js
 *
 * @author  Fedir Zinchuk <getthesite at gmail dot com>
 * @license http://www.gnu.org/licenses/gpl.html
 */

;(function(window, document, $){
	"use strict";

	$.relatedSelect = function(element, options){
		this.$element = $(element);

        // merge options
        this.options = $.extend({}, $.relatedSelect.defaults, options);



	};

	// defaults
    $.relatedSelect.prototype.defaults = {
    	name: 'selectedItem', //default name for select
    	choose: 'Choose...', // string with text or function that will be passed current level and returns a string
    	empty_value: '', // what value to set the input to if no valid option was selected
    	request_url: null, //url for children request in JSON, will be POST request with query: relatedSelect=1&id=PARENT_ID,
    	loading_image: '', // link to image, show an ajax loading graphics (animated gif) while loading ajax (eg. /ajax-loader.gif)
    	preselect: [], //array with ids of elements that selected. IMPORTANT: (3 != '3') ... 3 not the same as '3'
    	labels: [], //array of labels for each level
    	instant_init: true // whether build selects in initialisation time or init later using init()
    };

    $.fn.relatedSelect = function(options){
    	var instance = $(this).data('relatedSelect');
    	if(!instance){
    		instance = new $.relatedSelect(this, options);
    		$(this).data('relatedSelect', instance);
    	}
    	return instance;
    };

})(window, document, jQuery);
