/**
 * JivoChat/JivoSite Live Chat: module for Prestashop 1.5-1.6
 *
 * @author    zapalm <zapalm@ya.ru>
 * @copyright (c) 2014-2016, zapalm
 * @link      http://prestashop.modulez.ru/en/free-products/27-jivosite-live-chat.html The module's homepage
 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

$(document).ready(function() {
	if ($('#widget_id_existence_off').val() == 1)
		showAutoRegOptions(false);
	else
		showAutoRegOptions(true);

	$('#widget_id_existence_off').click(function() {
		showAutoRegOptions(false);
	});

	$('#widget_id_existence_on').click(function() {
		showAutoRegOptions(true);
	});
});

function showAutoRegOptions(param)
{
	if (param) {
		$('#fieldset_1_1').show();
		$('#fieldset_2_2').hide();
	}
	else {
		$('#fieldset_1_1').hide();
		$('#fieldset_2_2').show();
	}
}