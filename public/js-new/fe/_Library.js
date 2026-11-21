class Library {
    static async init() {
        await Common.loadJs('CustomGlide.min.js');
        await CustomGlide.initAll();

        let glideBig = document.querySelector('.boxLibrary:not(.boxLibraryInited) .glideBig');
        let glideThumb = document.querySelector('.boxLibrary:not(.boxLibraryInited) .glideThumb');
        if (!glideBig || !glideThumb) return;
        glideBig.closest('.boxLibrary').classList.add('boxLibraryInited')

        // lấy instance glide chính
        let mainGlide = CustomGlide.getInstance(glideBig);

        // Active thumb đầu tiên
        glideThumb.querySelector('.glideThumbItem')?.classList.add('active');

        // Click vào thumb
        glideThumb.querySelectorAll('img').forEach(img => {
            img.addEventListener('click', () => {
                const item = img.closest('.glideThumbItem');
                const index = Array.from(
                    glideThumb.querySelectorAll('.glideThumbItem')
                ).indexOf(item);

                this.#setIndexGlideThumb(glideThumb, index);
                // chuyển slide chính
                mainGlide.go(`=${index}`);
            });
        });

        // Đồng bộ 2 slider
        mainGlide.on('run', () => {
            this.#setIndexGlideThumb(glideThumb, mainGlide.index);
        });
    }

    static #setIndexGlideThumb(glideThumb, index) {
        const items = glideThumb.querySelectorAll('.glideThumbItem');

        // remove active cũ
        glideThumb.querySelector('.glideThumbItem.active')?.classList.remove('active');

        // set active mới
        if (items[index]) {
            items[index].classList.add('active');
        }
    }
}
