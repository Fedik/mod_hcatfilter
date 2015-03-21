/**
 * @version		2012.08.11
 * @package Hierarchical Category Filter for Joomla 2.5
 * @author  Fedik
 * @email	getthesite@gmail.com
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPL http://www.gnu.org/licenses/gpl.html
 *
 * mod_hcatfilter
 */
var hCatFilterItems = new Array();

hCatFilterInit = function () {
	//execute each stored settings
	Array.each(hCatFilterItems, function(o){
		//set some common options
		o.options.empty_value = '0';
		o.options.instant_init = false;
		//get optiontree
		var tree = new mooOptionTree(o.element, o.options, o.treeRoot, o.tree);

		//for set selected category id
		var catInput = document.id(o.element + '-form').getElement('input[name=id]');
		tree.addEvent('changed',function(changed){
			if(changed){
				var id = changed.get('value');
				var t = id.indexOf('_');
				id = (t == -1) ? id : id.substr(id.indexOf('_') + 1);
				if (id != 0) {
					catInput.set('value', id);
				}
			}
		});

		//init optiontree
		tree.init();

		//clear button
		var clearBt = document.id(o.element + '-form').getElement('.clear');
		if(clearBt){
			clearBt.addEvent('click',function(){tree.resetTree(true);});
		}
	});
};

//init
window.addEvent('domready',function(){
	hCatFilterInit();
});
