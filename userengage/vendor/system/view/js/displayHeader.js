/* global prestashpp */

let pshow_msg_pattern = "%c This shop uses module %s from PrestaShow.pl";
if (typeof prestashop !== 'undefined' && typeof prestashop.language !== 'undefined' &&
    prestashop.language.iso_code === 'pl') {
    pshow_msg_pattern = "%c Ten sklep używa modułu %s z PrestaShow.pl";
}
for (let i in window) {
    if (i.match(/pshow_loaded_module.*/) && window[i] !== null) {
        console.log(pshow_msg_pattern, "background: #f00; color: #fff; padding: 10px;", window[i]);
        window[i] = null;
    }
}