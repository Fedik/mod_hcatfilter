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
        this.tree = this.options.tree;

        // make image element for loading animation
        if (this.options.loading_image) {
			this.loading_image = $('<img/>', {
				src: this.options.loading_image,
				alt: 'Loading...',
				'class': 'loading-animation'
			});
		}

        if(this.options.instantInit){
			this.updateTree();
		}

        console.log(this);
	};

	/**
	 * function for start build selects manualy,
	 * usefull when exist preselected items,
	 * because "changed" event fires before object ready
	 */
	$.relatedSelect.prototype.init = function() {
   		this.updateTree();
   	};

	/**
	 * Function for check changes and make update
	 * @param  changed - DOM element of chenged select tag
	 */
	$.relatedSelect.prototype.updateTree = function (changed) {
   		//change event
		this.$element.trigger('update', [changed]);

		if(changed){
			//clear all after changed
			var $el = $(changed);
			$el.next('label').remove();
			$el.next('select').remove();

			//add children select
			var id = $el.val();
			//get children JSON if link is given and tree empty
			if (this.options.requestUrl && id != this.options.emptyValue && !this.tree[id]){
				this.requestChildren(changed);
			} else {//add select
				this.addSelect(id);
			}
		} else {
			// clear
			this.$element.find('label').remove();
			this.$element.find('select').remove();

			// and build new
			this.addSelect();
		}
	};

	/**
  	 * function for add new select
  	 *
	 * @param id, string - id of parrent
	 */
	$.relatedSelect.prototype.addSelect = function(id){
		var name = this.options.name,
			root = this.options.root,
			tree = [];

		if(!id){
			tree = this.tree[root];
		}
		else if(id && this.tree[id] && this.tree[id].length){ //build children
			tree = this.tree[id];
			name += id;
		} else { //item have no children
			return;
		}

		var $select = $('<select/>',{name: name});

		//appaned to DOM
		this.$element.append($select);

		$select.on('change', function(e){
			this.updateTree(e.target);
		}.bind(this));

		//add label if given
		if(this.options.labels.length){
			//find curent level via count previous elements
			var lvl = $select.next('select').length;
			//label text
			var lbl_text = this.options.labels[lvl];
			if (lbl_text){
				var $label = $('<label/>',{
					'for': name,
					html: lbl_text
				});
				$select.before($label);
			}
		}

		//add options

		//empty item
		$select.append($('<option/>',{
		    value: this.options.emptyValue,
			html: this.options.choose
		}));

		//from tree
		for(var i = 0, l = tree.length; i < l; i++){
			var el = tree[i];
		    $select.append($('<option/>',{
		    	value: el.value,
		    	html: el.title
		    }));

		    //preselect
		    if(this.options.preselect.length && this.options.preselect.indexOf(el.value) !== -1){
		    	$select.val(el.value);
		    	$select.trigger('change');
		    }
		};

	};

	// defaults
    $.relatedSelect.defaults = {
    	root: 0, // Parent id in tree
    	tree: [], // tree elements, eg [1:[{text:'Text',value:3,parent:1},{text:'Text',value:4,parent:1}],2:[{text:'Text',value:4,parent:2},{text:'Text',value:5,parent:2}]]
    	name: 'selectedItem', //default name for select
    	choose: 'Choose...', // string with text or function that will be passed current level and returns a string
    	emptyValue: '', // what value to set the input to if no valid option was selected
    	requestUrl: null, //url for children request in JSON, will be POST request with query: relatedSelect=1&id=PARENT_ID,
    	loadingImage: '', // link to image, show an ajax loading graphics (animated gif) while loading ajax (eg. /ajax-loader.gif)
    	preselect: [], //array with ids of elements that selected. IMPORTANT: (3 != '3') ... 3 not the same as '3'
    	labels: [], //array of labels for each level
    	instantInit: true // whether build selects in initialisation time or init later using init()
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
