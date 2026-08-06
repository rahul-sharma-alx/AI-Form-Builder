import './bootstrap';
import Sortable from 'sortablejs';

window.initSortable = function initSortable() {
    document.querySelectorAll('[data-sortable]').forEach((el) => {
        if (el._sortableInstance) return;

        const group = el.dataset.sortable;

        el._sortableInstance = Sortable.create(el, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function (evt) {
                if (evt.oldIndex === evt.newIndex && evt.to === evt.from) return;

                const ids = Array.from(evt.to.children)
                    .filter((c) => c.dataset && c.dataset.id)
                    .map((c) => c.dataset.id);

                if (ids.length && window.Livewire) {
                    window.Livewire.dispatch(`${group}-reorder`, { ids });
                }
            },
        });
    });
};

document.addEventListener('DOMContentLoaded', () => initSortable());

document.addEventListener('livewire:init', () => {
    initSortable();
});
