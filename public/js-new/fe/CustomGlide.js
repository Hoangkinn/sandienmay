class CustomGlide {
    static instances = new Map();

    constructor(el) {
        this.el = el;
        this.glide = null;
    }

    async #init() {
        await Common.loadJs('glide.min.js');

        const el = this.el;
        const track = el.querySelector('[data-glide-el="track"]');
        if (!track) return;

        // Count slides (children of the track wrapper, typically a row or ul)
        const slidesContainer = track.children[0];
        if (!slidesContainer) return;
        const slideCount = slidesContainer.children.length;
        if (slideCount <= 1) {
            return;
        }

        const defaultPerView = parseInt(el.dataset.perview) || 1;
        // Không hạ perView xuống theo số slide: thiếu sản phẩm thì giữ nguyên kích thước
        // ô card và để trống chỗ còn lại.
        let pcPerView = parseInt(el.dataset.pcPerview) || defaultPerView;

        let mobilePerView = defaultPerView;
        if (slideCount < mobilePerView) {
            mobilePerView = slideCount;
        }

        // Đọc cấu hình riêng từ các thuộc tính data-bp-* (nếu có)
        const bp1200Val = el.dataset['bp-1200'] ? parseInt(el.dataset['bp-1200']) : pcPerView;
        const bp1024Val = el.dataset['bp-1024'] ? parseInt(el.dataset['bp-1024']) : pcPerView;
        const bp992Val = el.dataset['bp-992'] ? parseInt(el.dataset['bp-992']) : mobilePerView;
        
        let autoplay = el.dataset.autoplay ?? 3000;

        let option = {
            type: 'slider',
            startAt: 0,
            perView: pcPerView,
            breakpoints: {
                1200: {
                    perView: bp1200Val
                },
                1024: {
                    perView: bp1024Val
                },
                992: { 
                    perView: bp992Val 
                },
                767: {
                    perView: mobilePerView
                }
            },
            gap: 0,
            hoverpause: true,
            bound: true
        };

        if (typeof autoplay === "number" || (typeof autoplay === "string" && !isNaN(autoplay))) {
            const parsedAutoplay = parseInt(autoplay);
            if (parsedAutoplay > 0) {
                option.autoplay = parsedAutoplay;
            }
        }

        try {
            const glide = new Glide(el, option);

            // Wait for mount with a fallback timeout
            await new Promise(resolve => {
                const timeout = setTimeout(resolve, 500);
                glide.on('mount.after', () => {
                    clearTimeout(timeout);
                    resolve();
                });
                try {
                    glide.mount();
                } catch (e) {
                    clearTimeout(timeout);
                    console.warn('Error during glide mount:', e);
                    resolve();
                }
            });

            this.glide = glide;
            CustomGlide.instances.set(this.el, glide);
        } catch (err) {
            console.warn('Glide initialization failed:', err);
        }
    }

    static async create(el) {
        const instance = new CustomGlide(el);
        await instance.#init();
        return instance;
    }

    static async initAll(selector = '.glide:not(.glide--slider)') {
        const elements = document.querySelectorAll(selector);
        if (!elements.length) return;

        await Common.loadJs('glide.min.js');

        // Initialize each carousel individually and catch failures so one error doesn't break everything
        const promises = Array.from(elements).map(el => {
            return this.create(el).catch(err => {
                console.error('Failed to initialize glide element:', el, err);
            });
        });

        return Promise.all(promises);
    }

    static getInstance(el) {
        return this.instances.get(el);
    }

    static async onReady(callback) {
        await Common.loadJs('glide.min.js');
        callback();
    }
}
