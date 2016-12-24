/**
 * JivoChat/JivoSite Live Chat: module for Prestashop 1.5-1.6
 *
 * @author    zapalm <zapalm@ya.ru>
 * @copyright (c) 2014-2016, zapalm
 * @link      http://prestashop.modulez.ru/en/free-products/27-jivosite-live-chat.html The module's homepage
 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

$(document).ready(function() {

	var $checkboxOn  = $('#widget_id_existence_on');
	var $checkboxOff = $('#widget_id_existence_off');

	toggleAutoRegForm($checkboxOff.prop('checked'));

	$checkboxOff.click(function() {
		toggleAutoRegForm(true);
	});

	$checkboxOn.click(function() {
		toggleAutoRegForm(false);
	});
});

function toggleAutoRegForm(show)
{
	var $fieldsets        = $('[id*="fieldset_"]');
	var $fieldsetWidgetId = $($fieldsets.get(1));
	var $fieldsetAutoReg  = $($fieldsets.get(2));

	if (show) {
		$fieldsetWidgetId.hide();
		$fieldsetAutoReg.show();
	}
	else {
		$fieldsetWidgetId.show();
		$fieldsetAutoReg.hide();
	}
}