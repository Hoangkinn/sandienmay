class Wishlist {
    static init() {
        this.addEvent();
    }

    static addEvent() {
        for (const el of document.querySelectorAll('.jsAddToWishlist:not([data-inited])')) {
            el.dataset.inited = 1;

            el.addEventListener('click', async e => {
                let id = el.dataset.id;
                postData('post', '/wishlist/add', { id: id })
                    .then(rs => rs.json())
                    .then(res => {
                        if (res.status) {
                            if (res.action === 'added') {
                                el.classList.add('active');
                            } else {
                                el.classList.remove('active');
                            }
                            Toastr.success(res.message);
                        }
                    });
            });
        }
    }
}

Wishlist.init();

