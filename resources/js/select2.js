import $ from 'jquery';
import select2 from 'select2';
import 'select2/dist/css/select2.css';
import '../css/select2-theme.css';

window.$ = window.jQuery = $;
select2(window, $);

function dropdownParentFor(el) {
    const modal = el.closest('.fixed, [role="dialog"]');

    return modal ? $(modal) : $(document.body);
}

function placeholderFor($el) {
    return (
        $el.data('placeholder') ||
        $el.find('option[value=""]').first().text() ||
        'Pilih opsi'
    );
}

export function initSelect2(root = document) {
    const scope = root instanceof Element || root instanceof Document ? root : document;

    $(scope)
        .find('select.js-select2')
        .each(function initOne() {
            const $el = $(this);

            if (!this.isConnected || $el.hasClass('select2-hidden-accessible')) {
                return;
            }

            const hasEmpty = $el.find('option[value=""]').length > 0;
            const allowClear = $el.data('allow-clear') !== false && hasEmpty;
            // 0 = selalu tampilkan kotak pencarian (bisa di-override via data-search-threshold)
            const rawThreshold = $el.data('search-threshold');
            const searchThreshold =
                rawThreshold === undefined || rawThreshold === null || rawThreshold === ''
                    ? 0
                    : Number(rawThreshold);

            $el.select2({
                width: '100%',
                theme: 'default',
                placeholder: placeholderFor($el),
                allowClear,
                dropdownParent: dropdownParentFor(this),
                minimumResultsForSearch: Number.isFinite(searchThreshold) ? searchThreshold : 0,
                language: {
                    noResults: () => 'Tidak ada hasil',
                    searching: () => 'Mencari…',
                    inputTooShort: () => 'Ketik untuk mencari…',
                },
            });

            $el.on('select2:select select2:clear select2:unselect', () => {
                this.dispatchEvent(new Event('input', { bubbles: true }));
                this.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
}

export function destroySelect2(root = document) {
    const scope = root instanceof Element || root instanceof Document ? root : document;

    $(scope)
        .find('select.js-select2.select2-hidden-accessible')
        .each(function destroyOne() {
            const $el = $(this);
            $el.off('select2:select select2:clear select2:unselect');
            $el.select2('destroy');
        });
}

export function refreshSelect2(root = document) {
    destroySelect2(root);
    initSelect2(root);
}

document.addEventListener('DOMContentLoaded', () => initSelect2(document));

document.addEventListener('select2:reinit', (event) => {
    const root = event.target instanceof Element ? event.target : document;
    refreshSelect2(root);
});

window.initSelect2 = initSelect2;
window.refreshSelect2 = refreshSelect2;
