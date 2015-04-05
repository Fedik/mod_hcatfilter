/**
 * @version	2012.08.11
 * @package Hierarchical Category Filter
 * @author  Fedir Zinchuk
 * @email	getthesite@gmail.com
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPL http://www.gnu.org/licenses/gpl.html
 *
 * mod_hcatfilter
 */
var hCatFilterItems = [];

;(function(window, document, $){
	"use strict";

    window.hCatFilterInit = function () {
    	//execute each stored settings
    	for(var i = 0, l = hCatFilterItems.length; i < l; i++) {
    		var o = hCatFilterItems[i];

    		//set some common options
    		o.options.emptyValue = '0';
    		o.options.instantInit = false;

            // Initialize
			var $el = $(o.element);
			$el.relatedSelect(o.options);
			var tree = $el.data('relatedSelect');

    		//for set selected category id
    		var $catInput = $(o.element + '-form').children('input[name=id]');
    		$el.on('update', function(event, changed){
    			if(changed){
    				var id = $(changed).val();
    				if (id != 0) {
    					$catInput.val(id);
    				}
    			}
    		});

    		tree.init();

    		// Bind clear button
    		$(o.element + '-form').on('click', '.reset', function() {
    			tree.resetTree(true);
    		});
    	}
    };

    //init
    $(document).ready(window.hCatFilterInit);

})(window, document, jQuery);
