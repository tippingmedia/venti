/**
 * Venti Calendar Class
 */

class VentiEvents {
    constructor (options) {
        this.sourceEvents();
    }

    sourceEvents () {
        const $this = this;
        const button = document.querySelectorAll('a.btn.submit.add.icon')[0];
        const items = document.querySelectorAll('.sidebar li');
        for (let i = 0; i < items.length; i++) {
            items[i].addEventListener('click', function (evt) {
                const anchor = evt.target;
                let group = "";
                if (anchor.tagName === "A") {
                    group = anchor.dataset.handle;
                }else if( anchor.tagName === "SPAN") {
                    group = anchor.parentNode.dataset.handle;
                }
                const href = `/admin/venti/${group}/new`;
                button.href = encodeURI(href);
            });
        }
    }
}
