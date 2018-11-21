/**
 * JivoSite live chat: the module for PrestaShop.
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2014 Maksim T.
 * @link      https://prestashop.modulez.ru/en/frontend-features/27-jivochat-more-than-live-chat.html The module's homepage
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

$(document).ready(function () {
    var $checkboxOn = $('#widget_id_existence_on');
    var $checkboxOff = $('#widget_id_existence_off');

    toggleAutoRegForm($checkboxOff.prop('checked'));

    $checkboxOff.click(function () {
        toggleAutoRegForm(true);
    });

    $checkboxOn.click(function () {
        toggleAutoRegForm(false);
    });
});

function toggleAutoRegForm(show) {
    var $fieldsets = $('[id*="fieldset_"]');
    var $fieldsetWidgetId = $($fieldsets.get(1));
    var $fieldsetAutoReg = $($fieldsets.get(2));

    if (show) {
        $fieldsetWidgetId.hide();
        $fieldsetAutoReg.show();
    }
    else {
        $fieldsetWidgetId.show();
        $fieldsetAutoReg.hide();
    }
}