let isMobile;
let baseUrl = window.location.origin;

class Common {
    static resizeTimeout = null;
    static #loadedFiles = {};
    static #initScrollEvent() {
        const scrollUpButton = document.getElementById('scrollUp');
        const header = document.querySelector('header');

        if (!header) return;

        let lastScroll = 0;
        let ticking = false;

        // ✅ ĐÃ SỬA: Cache chiều cao cố định lazily để tránh forced reflow lúc load trang
        let headerHeight = null;

        function handleScroll() {
            if (headerHeight === null) {
                headerHeight = header.offsetHeight || 135;
            }
            const currentScroll = window.scrollY;
            
            // ✅ ĐÃ SỬA: Xác định trạng thái mobile thông qua bom (window) thay vì các thuộc tính dom hình học
            const isMobileMode = window.innerWidth < 992; 

            // ===== Scroll to top button =====
            if (scrollUpButton) {
                // ✅ ĐÃ SỬA: Dùng .toggle kèm điều kiện logic. 
                // Trình duyệt chỉ thay đổi DOM khi currentScroll vượt qua hoặc lùi lại mốc 200, thay vì chạy liên tục.
                scrollUpButton.classList.toggle('d-none', currentScroll <= 200);
            }

            // ===== Header logic =====
            if (isMobileMode) {
                if (currentScroll <= headerHeight) {
                    header.classList.remove('is-sticky', 'header-hide');
                } else {
                    header.classList.add('is-sticky');

                    if (currentScroll > lastScroll && currentScroll > headerHeight * 2) {
                        header.classList.add('header-hide');
                    } else if (currentScroll < lastScroll) {
                        header.classList.remove('header-hide');
                    }
                }
            } else {
                // Reset mobile state
                header.classList.remove('header-hide');
                
                if (currentScroll > lastScroll && currentScroll > headerHeight) {
                    header.classList.remove('top-0');
                } else if (currentScroll < lastScroll) {
                    header.classList.add('top-0');
                }
            }

            lastScroll = currentScroll <= 0 ? 0 : currentScroll;
        }

        // ✅ ĐÃ SỬA: Giữ nguyên cấu trúc requestAnimationFrame rất tốt của bạn
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    handleScroll();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        // ===== Scroll to top =====
        scrollUpButton?.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    static async fetch(url, options = {}) {
        const defaultHeaders = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
        };

        return fetch(url, {
            ...options,
            headers: {
                ...defaultHeaders,
                ...(options.headers || {})
            }
        });
    }
    static debounce(callback, delay = 300) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => callback.apply(this, args), delay);
        };
    }
    static lazyImage() {
        document.querySelectorAll('img[data-lazy]')?.forEach(i => {
            i.src = i.dataset.lazy;
        })
    }
    static #initSearch() {
        const forms = document.querySelectorAll('form.formSearch');

        forms.forEach(form => {
            const input = form.querySelector('input');
            const results = form.querySelector('.searchResult');
            if (!input || !results) return;

            let oldValue = '';

            const handleSearch = Common.debounce(async () => {
                const keyword = input.value.trim();
                if (!keyword || keyword === oldValue) {
                    return;
                }

                oldValue = keyword;

                try {
                    const response = await Common.fetch(`/ajax/search?s=${encodeURIComponent(keyword)}`);
                    const data = await response.json();
                    results.innerHTML = data.html || '';
                    results.classList.remove('d-none'); // Hiển thị kết quả khi có dữ liệu
                } catch (error) {
                    console.error('Lỗi tìm kiếm:', error);
                    results.innerHTML = '';
                    results.classList.add('d-none'); // Ẩn kết quả khi có lỗi
                }
            }, 300);

            input.addEventListener('input', handleSearch);
            input.addEventListener('focus', () => {
                if (results.innerHTML.trim() !== '') {
                    results.classList.remove('d-none');
                }
            });

            document.addEventListener('click', (e) => {
                if (!form.contains(e.target)) {
                    results.classList.add('d-none');
                }
            });
        });
    }

    static async loadJs(src) {
        if (! /^https?:\/\//i.test(src)) {
            // Lay ?v cua main.min.js de dung chung cho file tai sau, tranh
            // trinh duyet dung cache cua ban cu
            const mainScript = document.querySelector('script[src*="main.min.js"], script[src*="main.js"]');
            let version = '';
            if (mainScript) {
                try {
                    const urlObj = new URL(mainScript.src, window.location.href);
                    version = urlObj.searchParams.get('v') || '';
                } catch (e) {}
            }
            src = `${baseUrl}/js-new/fe/${src}`;
            if (version) {
                src += `?v=${version}`;
            }
        }

        if (this.#loadedFiles[src]) return this.#loadedFiles[src];

        this.#loadedFiles[src] = new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) return resolve();
            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = resolve;
            script.onerror = () => reject(new Error(`Failed to load JS: ${src}`));
            document.head.appendChild(script);
        });

        return this.#loadedFiles[src];
    }

    static formSubmit() {
        document.addEventListener('submit', async e => {
            const form = e.target;

            if (!(form instanceof HTMLFormElement)) return;
            if (form.hasAttribute('data-skip-ajax')) return;

            e.preventDefault();

            // ✅ beforeSend logic khác
            const beforeSend = form.dataset.beforesend;
            if (typeof beforeSend === 'function') {
                if (beforeSend(form) === false) {
                    return
                }
            }

            const onSuccess = form.dataset.onsuccess && window[form.dataset.onsuccess];
            const onError = form.dataset.onerror && window[form.dataset.onerror];

            try {
                let result = await postData(form.method, form.action, new FormData(form));
                let code = result.status;
                result = result.json();

                result.then(async rs => {
                    switch (code) {
                        case 200: {
                            if (rs.message) {
                                await Toastr.success(rs.message);
                            }
                            onSuccess?.(rs, form);
                            break;
                        }
                        case 422: {
                            this.renderFormErrors(rs.errors, form);
                            onError?.(rs, form);
                            break;
                        }
                        default: onError?.(rs, form);
                    }
                });
            } catch (err) {
                await Toastr.warning(err);
            }
        });
    }

    static renderFormErrors(errors, form) {
        if (typeof errors !== 'object') return;

        for (const [field, messages] of Object.entries(errors)) {
            let formId = form.id;
            let input = form.querySelector(`[name="${field}"]`);
            if (!input && formId)
                input = document.querySelector(`[name="${field}"][form=${formId}]`)
            if (!input) {
                Toastr.error(messages.join('\n'));
            } else {
                validateField(input, messages.join('\n'));
            }
        }
    }

    static init() {
        isMobile = window.matchMedia('(max-width: 991px)').matches;
        window.addEventListener('resize', () => {
            isMobile = window.matchMedia('(max-width: 991px)').matches;
        })
        setTimeout(this.lazyImage, 0);

        this.formSubmit();
        this.#initScrollEvent();
        this.#initSearch();

        // const btnShow = document.querySelector('.btn-show-cat');
        // if (btnShow) {
        //     btnShow.addEventListener('click', function () {
        //         document.querySelector('.menu-dnone').classList.toggle('open');
        //     });
        // }

        
        // document.addEventListener('click', function (e) {
        //     const menu = document.querySelector('.menu-dnone');
        //     const button = document.querySelector('.btn-show-cat');
        //     if (!menu || !button) return;
        //     if (menu.classList.contains('open') &&
        //         !menu.contains(e.target) &&
        //         !button.contains(e.target)) {

        //         menu.classList.remove('open');
        //     }
        // Close mobile menu on click-outside or scroll
        const menuToggle = document.getElementById('menu-mobi-toggle') || document.querySelector('.icon-menu + input[name="menu"]');
        if (menuToggle) {
            let scrollYOnOpen = 0;
            menuToggle.addEventListener('change', function() {
                if (this.checked) {
                    scrollYOnOpen = window.scrollY;
                }
            });

            document.addEventListener('click', function(e) {
                const menuContainer = menuToggle.closest('.has-submenu');
                if (menuToggle.checked && menuContainer && !menuContainer.contains(e.target)) {
                    menuToggle.checked = false;
                    menuContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                }
            });

            window.addEventListener('scroll', function() {
                if (menuToggle.checked) {
                    if (Math.abs(window.scrollY - scrollYOnOpen) > 300) {
                        menuToggle.checked = false;
                        const menuContainer = menuToggle.closest('.has-submenu');
                        if (menuContainer) {
                            menuContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                        }
                    }
                }
            }, { passive: true });
        }
    }
}
Common.init();

class Modal {
    static #addEventOpen() {
        document.querySelectorAll('[data-target-modal]').forEach(i => {
            i.addEventListener('click', () => {
                let target = i.dataset.targetModal ? document.querySelector(i.dataset.targetModal) : i.closest('dialog');
                if (!target) return;
                if (target.open) {
                    target.close();
                } else {
                    target.showModal();
                }
            })
        })
    }
    static #addEventClose() {
        document.querySelectorAll('dialog').forEach(i => {
            /*click backdrop hide modal*/
            i.addEventListener('click', (event) => {
                if (event.target === i) i.close();
            })
        });
    }
    static #addEventBackdrop() {
        document.querySelectorAll('.eventBackdrop').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = document.querySelector('details.has-backdrop[open]');
                if (modal) {
                    modal.removeAttribute('open');
                }
            });
        });
    }
    static init() {
        this.#addEventOpen();
        this.#addEventClose();
        this.#addEventBackdrop();
    }
}
Modal.init();

class Product {
    static #initBuyButtons() {
        document.addEventListener('click', async (e) => {
            const button = e.target.closest('.btnBuy');
            if (!button) return;

            e.preventDefault();
            const id = button.getAttribute('data-id');
            const quantity = button.getAttribute('data-quantity') || 1;

            // Phụ kiện đang tick ở khối "Thường được mua cùng" (trang chi tiết)
            const accessoryIds = [...document.querySelectorAll('.js-bought-together:checked')].map(
                (checkbox) => checkbox.value
            );

            try {
                for (const accessoryId of accessoryIds) {
                    try {
                        await Common.fetch(`/cart/add/${accessoryId}`, {
                            method: 'POST',
                            body: JSON.stringify({ quantity: 1 }),
                        });
                    } catch (accessoryError) {
                        console.error('Lỗi khi thêm phụ kiện vào giỏ hàng:', accessoryError);
                    }
                }

                const response = await Common.fetch(`/cart/add/${id}`, {
                    method: 'POST',
                    body: JSON.stringify({ quantity }),
                });

                if (!response.ok) throw new Error('Thêm giỏ hàng thất bại');

                window.location.href = '/gio-hang';
            } catch (error) {
                Toastr.error('Đã có lỗi. Vui lòng thử lại!');
                console.error('Lỗi khi thêm vào giỏ hàng:', error);
            }
        });
    }
    static init() {
        this.#initBuyButtons();
    }
}
Product.init();
class Home {
    static menuToSidebar() {
        if (!isMobile) {
            let sidebarHome = document.querySelector('#sidebarHome');
            if (sidebarHome) {
                let menu = document.querySelector('.main-menu');
                sidebarHome.append(menu);
                menu.classList.remove('position-absolute', 'top-100');
                menu.classList.add('position-relative', 'h-100', 'justify-content-between');
            }
        }
    }
}
Home.menuToSidebar();

/* Mega menu desktop: nút "Danh mục" mở panel, rail trái đổi cột nội dung theo hover */
class MegaMenu {
    static init() {
        const header = document.querySelector('.site-header');
        const btn = document.getElementById('mega-toggle');
        const panel = header && header.querySelector('.mega');
        if (!header || !btn || !panel) return;

        const panelItems = [...panel.querySelectorAll('.mega-rail-item')];
        const panes = [...panel.querySelectorAll('.mega-pane')];
        const inner = panel.querySelector('.mega-inner');
        const body = panel.querySelector('.mega-body');
        const rail = panel.querySelector('.mega-rail');
        const desktop = window.matchMedia('(min-width: 993px)');

        // Chiều cao card đặt thẳng vào --mega-h (CSS đọc ở height của .mega-inner) thay vì
        // để nội dung tự quyết: mép dưới card chạy theo transition height, không nhảy số.
        // Không pane nào hiện thì cao bằng rail; pane ngắn hơn rail vẫn giữ cao rail kẻo cắt item.
        const syncHeight = () => {
            if (!inner || !body || !rail) return;
            const pane = panel.querySelector('.mega-pane.is-active');
            const cs = getComputedStyle(body);
            const limit = window.innerHeight - 120;
            const railH = rail.scrollHeight; // đã gồm padding 8px hai đầu
            const h = pane
                ? Math.max(railH, pane.offsetHeight + parseFloat(cs.paddingTop) + parseFloat(cs.paddingBottom))
                : railH;
            inner.style.setProperty('--mega-h', Math.min(limit, Math.round(h)) + 'px');
        };

        // Khi card nở ngang 280px -> 100%, cột nội dung bị ép còn vài px nên chữ wrap lại
        // rồi thả ra trong lúc animation (card phồng lên rồi co — "giật từ dưới lên").
        // Ép body giữ bề rộng cuối bằng min-width để bố cục nội dung đứng yên, card chỉ
        // trượt ngang để lộ dần nội dung.
        const pinBodyWidth = () => {
            if (!body) return;
            const container = panel.querySelector('.container');
            const cs = getComputedStyle(container);
            const w = container.clientWidth - parseFloat(cs.paddingLeft) - parseFloat(cs.paddingRight) - 280;
            body.style.minWidth = Math.max(0, w) + 'px';
        };
        const unpinBodyWidth = () => { if (body) body.style.minWidth = ''; };

        const activate = index => {
            panelItems.forEach(li => li.classList.toggle('is-active', +li.dataset.megaIndex === index));
            panes.forEach(p => p.classList.toggle('is-active', +p.dataset.megaPane === index));
            pinBodyWidth();
            syncHeight();
            // Ảnh banner chưa kịp decode thì chiều cao đo lần đầu sẽ thiếu
            const pane = panel.querySelector('.mega-pane.is-active');
            pane && [...pane.querySelectorAll('img')].filter(img => !img.complete).forEach(img => {
                img.addEventListener('load', syncHeight, { once: true });
                img.addEventListener('error', syncHeight, { once: true });
            });
        };

        const resetPane = () => {
            panelItems.forEach(li => li.classList.remove('is-active'));
            panes.forEach(p => p.classList.remove('is-active'));
            unpinBodyWidth();
            syncHeight();
        };

        const close = () => {
            header.classList.remove('mega-open');
            btn.setAttribute('aria-expanded', 'false');
            unpinBodyWidth();
        };
        const open = () => {
            // Mới mở chỉ hiện rail cấp 1 (giống sidebar danh mục trang chủ);
            // pane nội dung chỉ hiện khi hover vào một danh mục.
            // Bật mega-open TRƯỚC rồi mới đo/syncHeight: khi panel còn display:none
            // thì scrollHeight/offsetHeight đo ra đều là 0.
            header.classList.add('mega-open');
            btn.setAttribute('aria-expanded', 'true');
            resetPane();
        };

        // Card nở ngang xong thì bỏ min-width để body co/giãn lại theo flex
        // (resize cửa sổ lúc panel đang mở vẫn đúng bố cục)
        inner && inner.addEventListener('transitionend', e => {
            if (e.propertyName === 'width') unpinBodyWidth();
        });

        btn.addEventListener('click', e => {
            e.stopPropagation();
            header.classList.contains('mega-open') ? close() : open();
        });

        panelItems.forEach(li => {
            // Danh mục cấp 1 không có con: giữ nguyên danh sách (không nở panel trắng),
            // click vào tên vẫn vô trang danh mục.
            const index = +li.dataset.megaIndex;
            const activateIfHasPane = () => {
                const pane = panes[index];
                if (!pane || !pane.querySelector('.mega-cols')) {
                    // Danh mục cấp 1 không có con: thu card về rail, không nở trắng
                    resetPane();
                    return;
                }
                activate(index);
            };
            li.addEventListener('mouseenter', activateIfHasPane);
            li.addEventListener('focusin', activateIfHasPane);
        });

        // Đóng khi click bất cứ đâu NGOÀI card .mega-inner: kể cả vùng trong suốt
        // hai bên card (thẻ .mega full-width nằm trong header) hay thanh header-top,
        // vì chúng vẫn thuộc .site-header nên điều kiện cũ !header.contains không bắt được.
        // Nút Danh mục tự stopPropagation ở handler riêng nên không tới đây.
        document.addEventListener('click', e => {
            if (!header.classList.contains('mega-open')) return;
            if (inner && inner.contains(e.target)) return;
            close();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') close();
        });
        header.querySelector('.formSearch')?.addEventListener('focusin', close);
        desktop.addEventListener('change', e => {
            if (!e.matches) close();
        });
    }
}
MegaMenu.init();

/* Sidebar danh mục ở khối banner trang chủ: submenu là của riêng nó, hiện đè lên cột banner.
   Không dùng chung và không liên quan tới panel mega của nút "Danh mục" trên header. */
class SideMenu {
    static init() {
        const side = document.querySelector('.home-side');
        const body = side && side.querySelector('.mega-body');
        if (!body) return;

        const items = [...side.querySelectorAll('.mega-rail-item')];
        const panes = [...side.querySelectorAll('.mega-pane')];
        let closeTimer = null;

        const show = index => {
            clearTimeout(closeTimer);
            items.forEach(li => li.classList.toggle('is-active', +li.dataset.megaIndex === index));
            panes.forEach(p => p.classList.toggle('is-active', +p.dataset.megaPane === index));
            side.classList.add('is-open');
        };
        const hide = () => {
            clearTimeout(closeTimer);
            side.classList.remove('is-open');
            items.forEach(li => li.classList.remove('is-active'));
            panes.forEach(p => p.classList.remove('is-active'));
        };

        items.forEach(li => {
            // Danh mục cấp 1 không có con vẫn mở submenu: bảng trắng đè lên cột banner thay vì
            // hiển thị submenu của danh mục rê qua trước đó. Click vào tên vẫn vô trang danh mục.
            const index = +li.dataset.megaIndex;
            li.addEventListener('mouseenter', () => show(index));
            li.addEventListener('focusin', () => show(index));
        });

        /* .mega-body là con của .home-side dù nằm ngoài hộp của nó, nên rê chuột từ sidebar
           sang submenu không kích hoạt mouseleave; trễ 150ms chỉ để an toàn khi rê nhanh ra ngoài. */
        const leave = () => {
            closeTimer = setTimeout(hide, 150);
        };
        [side, body].forEach(el => {
            el.addEventListener('mouseenter', () => clearTimeout(closeTimer));
            el.addEventListener('mouseleave', leave);
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') hide();
        });
    }
}
SideMenu.init();

class Content {
    static #replaceThumbYoutube() {
        document.querySelectorAll('.changeIframe').forEach(i => {
            i.addEventListener('click', () => {
                const boxIframe = i.closest('.box-iframe'); // Tìm phần tử cha gần nhất có class 'box-iframe'
                if (boxIframe) {
                    const iframe = boxIframe.querySelector('iframe'); // Tìm thẻ iframe bên trong .box-iframe
                    if (iframe) {
                        const currentSrc = iframe.getAttribute('src');
                        if (currentSrc && !currentSrc.includes('autoplay=1')) {
                            const newSrc = currentSrc + (currentSrc.includes('?') ? '&' : '?') + 'autoplay=1&mute=1';
                            iframe.setAttribute('src', newSrc);
                        }
                    }
                    i.remove(); // Loại bỏ nút changeIframe sau khi đã xử lý
                }
            })
        })
    }
    static toc() {
        document.querySelectorAll('[data-scroll]').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();

                let maxEl = document.querySelector('.content-maxH');
                if (maxEl) {
                    maxEl.classList.remove('content-maxH');
                    maxEl.classList.add('is-expanded');
                    let overlay = maxEl.querySelector('.fade-overlay');
                    if (overlay) overlay.style.display = 'none';
                    let btn = maxEl.querySelector('button');
                    if (btn) btn.innerText = 'Thu gọn nội dung';
                }

                const targetId = this.getAttribute('data-scroll');
                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    targetEl.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
    // static contentMaxH() {
    //     let el = document.querySelector('.content-maxH');
    //     if (el) {
    //         if (el.scrollHeight > el.clientHeight) {
    //             el.classList.add('position-relative');

    //             // tạo overlay mờ dần
    //             let overlay = document.createElement('div');
    //             overlay.className = 'fade-overlay position-absolute bottom-0 start-0 col-12';
    //             el.appendChild(overlay);

    //             // tạo nút xem thêm
    //             let btn = document.createElement('button');
    //             btn.innerText = 'Xem thêm';
    //             btn.className = 'border rounded-30 border-1F1B56 mb-3 px-30 py-10 bg-1F1B56 text-fff position-absolute bottom-0 start-50 translate-middle-x fw-6 fs-16';
    //             btn.style.zIndex = 2; // cao hơn overlay
    //             overlay.style.zIndex = 1;
    //             overlay.style.height = '120px';
    //             overlay.style.background = '#fff';
    //             overlay.style.pointerEvents = 'none';
    //             overlay.style.boxShadow = '0px 0px 10px 10px #fff';

    //             btn.addEventListener('click', () => {
    //                 el.classList.toggle('content-maxH');
    //                 let collapsed = el.classList.contains('content-maxH');

    //                 btn.innerText = collapsed ? 'Xem thêm' : 'Ẩn bớt';
    //                 overlay.style.display = collapsed ? 'block' : 'none';
    //             });

    //             el.appendChild(btn);
    //         }
    //     }
    // }
    static contentMaxH() {
    let elements = document.querySelectorAll('.content-maxH');
    
    elements.forEach(el => {
        let style = window.getComputedStyle(el);
        let initialMaxHeight = style.maxHeight; 

        if (el.scrollHeight > el.clientHeight) {
            el.classList.add('position-relative');

            // Tạo overlay mờ dần
            let overlay = document.createElement('div');
            overlay.className = 'fade-overlay position-absolute bottom-0 start-0 col-12';
            overlay.style.height = '120px';
            overlay.style.zIndex = 1;
            overlay.style.background = 'linear-gradient(to bottom, rgba(255,255,255,0), rgb(255, 255, 255) 65%)';
            overlay.style.pointerEvents = 'none'; 
            el.appendChild(overlay);

            let btn = document.createElement('button');
            btn.type = 'button';
            btn.innerText = 'Xem toàn bộ nội dung';
            btn.className = 'cta-xemthem';
            btn.style.zIndex = 2;

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (el.style.maxHeight !== 'none') {
                    // MỞ RA
                    el.style.maxHeight = 'none';
                    el.style.overflow = 'visible';
                    el.classList.add('is-expanded');
                    btn.innerText = 'Thu gọn nội dung';
                    overlay.style.setProperty('display', 'none', 'important');
                } else {
                    el.style.maxHeight = initialMaxHeight;
                    el.style.overflow = 'hidden';
                    el.classList.remove('is-expanded');
                    btn.innerText = 'Xem toàn bộ nội dung';
                    overlay.style.setProperty('display', 'block', 'important');
                    
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });

            el.appendChild(btn);
        }
    });
}
    static scrollTable() {
        document.querySelectorAll('.content table').forEach(table => {
            const wrapper = document.createElement('div');
            wrapper.className = 'overflow-auto';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        });
    }

    static init() {
        this.#replaceThumbYoutube();
        this.toc();
        // Chạy các thao tác đo đạc DOM bất đồng bộ sau load để tránh forced reflow
        setTimeout(() => {
            this.contentMaxH();
            this.scrollTable();
        }, 50);
    }
}

Content.init();

function showLoading() {
    // document.getElementById('spinning').showModal();
}
function hideLoading() {
    // document.getElementById('spinning').close();
}
function validateField(el, message = '') {
    el.setCustomValidity(message);
    el.reportValidity();

    if (message !== '') {
        const handler = () => {
            el.setCustomValidity('');       // bỏ lỗi
            el.removeEventListener('input', handler); // gỡ sự kiện sau khi sửa
        };

        el.addEventListener('input', handler);
    }
}
async function postData(method, url, data = {}, effect = true) {
    method = method.toUpperCase();

    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
    };

    let body = null;

    if (method === 'GET') {
        const params = new URLSearchParams();

        if (data instanceof FormData) {
            for (const [key, value] of data.entries()) {
                params.append(key, value);
            }
        } else {
            for (const key in data) {
                if (data.hasOwnProperty(key)) {
                    params.append(key, data[key]);
                }
            }
        }

        const query = params.toString();
        if (query) {
            url += (url.includes('?') ? '&' : '?') + query;
        }

    } else if (data instanceof FormData) {
        if (method !== 'POST') {
            data.append('_method', method);
            method = 'POST';
        }
        body = data;

    } else {
        if (method !== 'POST') {
            data._method = method;
            method = 'POST';
        }
        headers['Content-Type'] = 'application/json';
        body = JSON.stringify(data);
    }

    if (effect)
        showLoading();
    const response = await fetch(url, {
        method,
        headers,
        body,
    });
    if (effect)
        hideLoading();

    return response;
}

function formatCurrency(number) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(number).replace(/\u00A0/g, '');
}
function jsPrice() {
    document.querySelectorAll('.jsPrice:not([data-price])').forEach(i => {
        i.dataset.price = parseInt(i.innerText);
        if (i.dataset.price > 0) {
            i.innerText = formatCurrency(i.innerText);
        } else {
            i.innerText = 'Liên hệ'
        }
    })
}

class Toastr {
    static #loadingPromise = null;

    static async #loadToastify() {
        if (this.#loadingPromise) return this.#loadingPromise;

        this.#loadingPromise = new Promise((resolve, reject) => {
            // 1. Load CSS
            const link = document.createElement("link");
            link.rel = "stylesheet";
            link.href = "/css-new/toastify.min.css";
            document.head.appendChild(link);

            // 2. Load JS
            const script = document.createElement("script");
            script.src = "js-new/fe/toastify-js.js";
            script.onload = () => {
                resolve();
            };
            script.onerror = reject;
            document.head.appendChild(script);
        });

        return this.#loadingPromise;
    }

    static async success(text) {
        try {
            await this.#loadToastify();
            Toastify({
                text: text,
                backgroundColor: "#03CA0A"
            }).showToast();
        } catch (e) {
            console.log(e)
        }

    }

    static async warning(text) {
        await this.#loadToastify();
        Toastify({
            text: text,
            backgroundColor: "#FFAE0D"
        }).showToast();
    }

    static async error(text) {
        await this.#loadToastify();
        Toastify({
            text: text,
            backgroundColor: "#ED1B24"
        }).showToast();
    }
}



        let lastScrollTop = 0;
        const header = document.querySelector('.site-header');

        window.addEventListener('scroll', function () {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > 100) {
                if (scrollTop > lastScrollTop) {
                    header.classList.add('hidden');
                } else {
                    header.classList.remove('hidden');
                }
            } else {
                header.classList.remove('hidden');
            }

            lastScrollTop = scrollTop;
        });
        document.addEventListener("DOMContentLoaded", function () {
            const menuToggle = document.getElementById('menu-toggle');
            const mainNav = document.querySelector('.main-nav');
            const mobileMenu = document.getElementById('modelMenuMobi');

            // Tạo sẵn thẻ màng đen (overlay)
            let menuOverlay = document.querySelector('.menu-overlay');
            if (!menuOverlay) {
                menuOverlay = document.createElement('div');
                menuOverlay.className = 'menu-overlay';
                document.body.appendChild(menuOverlay);
            }

            // Hàm cập nhật chiều cao linh hoạt cho menu
            function updateMenuHeight() {
                if (!mainNav) return;
                const hasActiveSub = mainNav.querySelector('.has-submenu-custom.is-active');
                if (hasActiveSub) {
                    mainNav.style.maxHeight = '85vh';
                    mainNav.style.overflowY = 'auto';
                } else {
                    mainNav.style.maxHeight = '515px';
                    mainNav.style.overflowY = 'auto';
                }
            }

            // Hàm mở menu
            function openMenu() {
                mainNav.classList.add('is-open');
                document.body.classList.add('menu-is-open');
                updateMenuHeight();
            }

            // Hàm đóng menu
            function closeMenu() {
                mainNav.classList.remove('is-open');
                document.body.classList.remove('menu-is-open');
                mainNav.style.display = '';
            }

            // 1. Bật/tắt menu chính khi bấm nút toggle
            if (menuToggle && mobileMenu) {
                // Drawer <dialog>: đóng bằng ESC hoặc click vào ::backdrop (target chính là dialog)
                const closeDrawer = () => {
                    if (mobileMenu.open) mobileMenu.close();
                    document.body.classList.remove('mobile-menu-open');
                };

                menuToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (mobileMenu.open) {
                        closeDrawer();
                    } else {
                        mobileMenu.showModal();
                        document.body.classList.add('mobile-menu-open');
                    }
                });

                mobileMenu.addEventListener('click', function (e) {
                    if (e.target === mobileMenu) closeDrawer();
                });

                ['close', 'cancel'].forEach(evt =>
                    mobileMenu.addEventListener(evt, () => document.body.classList.remove('mobile-menu-open'))
                );
            } else if (menuToggle && mainNav) {
                menuToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (mainNav.classList.contains('is-open')) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                });

                // Click vào màng đen thì đóng menu
                menuOverlay.addEventListener('click', function () {
                    closeMenu();
                });

                // Ngăn việc click bên trong menu làm menu bị đóng
                mainNav.addEventListener('click', function (e) {
                    e.stopPropagation();
                });

                // Tự động đóng menu khi cuộn chuột hoặc resize màn hình lớn
                window.addEventListener('scroll', function () {
                    if (mainNav.classList.contains('is-open')) closeMenu();
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth >= 992) closeMenu();
                });
            }

            // 2. Toggle bộ lọc Sidebar trên Mobile
            const sidebar = document.querySelector('.sidebar');
            const sidebarTitle = document.querySelector('.sidebar-title');
            if (sidebar && sidebarTitle) {
                sidebarTitle.addEventListener('click', function () {
                    if (window.innerWidth <= 992) {
                        sidebar.classList.toggle('is-open');
                    }
                });
            }

            // 3. Toggle menu con (accordion) trong drawer mobile.
            //    Bắt sự kiện kiểu delegation trên <dialog> thay vì querySelectorAll + cổng
            //    `window.innerWidth <= 992` lúc DOMContentLoaded: cổng đó làm mọi nút "+" chết
            //    khi trang được load ở bề rộng > 992 rồi mới thu nhỏ (resize cửa sổ, DevTools
            //    responsive, xoay máy tính bảng) vì listener không bao giờ được gắn lại.
            if (mobileMenu) {
                mobileMenu.addEventListener('click', function (e) {
                    const btn = e.target.closest && e.target.closest('.submenu-toggle-btn');
                    if (!btn) return;

                    e.preventDefault();
                    e.stopPropagation();

                    const parentLi = btn.closest('.has-submenu-custom');
                    if (parentLi) {
                        // Cho phép mở nhiều menu con độc lập
                        parentLi.classList.toggle('is-active');

                        // Cập nhật lại chiều cao menu ngay sau khi mở/đóng submenu
                        setTimeout(updateMenuHeight, 50);
                    }
                });
            }
        });
        
        document.addEventListener('DOMContentLoaded', () => {
            const titles = document.querySelectorAll('.footer-title');

            titles.forEach(title => {
                title.addEventListener('click', (e) => {

                    if (window.innerWidth <= 992) {
                        console.log('Đã click! 992');
                        const content = title.nextElementSibling;
                        if (content) {
                            content.classList.toggle('footer-active');
                            title.classList.toggle('active-rotate');
                        }
                    }
                });
            });
        });
    document.addEventListener("DOMContentLoaded", function () {
        const checkbox = document.getElementById("toggle-hotline-popup");
        const overlay = document.querySelector(".hotline-overlay");

        if (checkbox && overlay) {
            checkbox.addEventListener("change", function () {
                if (this.checked) {
                    overlay.classList.add("is-active");
                    document.body.style.overflow = "hidden"; 
                } else {
                    overlay.classList.remove("is-active");
                    document.body.style.overflow = "";
                }
            });

            function closePopup() {
                checkbox.checked = false;
                overlay.classList.remove("is-active");
                document.body.style.overflow = "";
            }

            const closeTriggers = overlay.querySelectorAll('.overlay-close-bg, .popup-close-btn');
            closeTriggers.forEach(trigger => {
                trigger.addEventListener("click", function (e) {
                    e.preventDefault();
                    closePopup();
                });
            });

            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape" && overlay.classList.contains("is-active")) {
                    closePopup();
                }
            });
        }
    });
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        const scrollBtn = document.getElementById("scrollToTopBtn");

        if (scrollBtn) {
            let ticking = false;
            const toggleScrollBtn = () => {
                if (ticking) return;
                ticking = true;
                window.requestAnimationFrame(() => {
                    scrollBtn.classList.toggle("is-visible", window.scrollY > 300);
                    ticking = false;
                });
            };
            // Đồng bộ trạng thái sau 2 khung hình: đọc scrollY ngay lúc DOMContentLoaded
            // sẽ kéo theo forced reflow trong lúc trang đang render lần đầu
            requestAnimationFrame(function () {
                requestAnimationFrame(toggleScrollBtn);
            });
            window.addEventListener("scroll", toggleScrollBtn, { passive: true });
        }
    });




    document.addEventListener("DOMContentLoaded", function () {
        let lastScrollTop = 0;
        const toolHome = document.querySelector('.tool-home');

        if (toolHome) {
            window.addEventListener('scroll', function () {
                if (window.innerWidth <= 768) {
                    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                    if (scrollTop > lastScrollTop && scrollTop > 100) {
                        toolHome.style.opacity = '0';
                        toolHome.style.visibility = 'hidden';
                        toolHome.style.transform = 'translateY(-50%) translateX(50px)';
                        toolHome.style.pointerEvents = 'none';
                        toolHome.style.transition = 'opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease';
                    } else {
                        toolHome.style.opacity = '1';
                        toolHome.style.visibility = 'visible';
                        toolHome.style.transform = 'translateY(-50%) translateX(0)';
                        toolHome.style.pointerEvents = 'auto';
                        toolHome.style.transition = 'opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease';
                    }
                    
                    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
                } else {
                    toolHome.style.opacity = '1';
                    toolHome.style.visibility = 'visible';
                    toolHome.style.transform = 'translateY(-50%) translateX(0)';
                    toolHome.style.pointerEvents = 'auto';
                    toolHome.style.transition = 'none';
                }
            }, { passive: true });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 768) {
                    toolHome.style.opacity = '1';
                    toolHome.style.visibility = 'visible';
                    toolHome.style.transform = 'translateY(-50%) translateX(0)';
                    toolHome.style.pointerEvents = 'auto';
                    toolHome.style.transition = 'none';
                }
            });
        }
    });

    function updateMenuHeight() {
        const mainNav = document.querySelector('.main-nav.is-open');
        if (!mainNav) return;

        // Kiểm tra xem có bất kỳ menu con nào đang mở (.is-active) hay không
        const hasActiveSub = mainNav.querySelector('.has-submenu-custom.is-active');

        if (hasActiveSub) {
            // Nếu có mở menu con: cho phép chiều cao tối đa lên 85vh và bật thanh cuộn dọc
            mainNav.style.maxHeight = '85vh';
            mainNav.style.overflowY = 'auto';
        } else {
            // Nếu đóng hết: thu về 500px mặc định
            mainNav.style.maxHeight = '500px';
            mainNav.style.overflowY = 'auto';
        }
    }

    // Gọi hàm cập nhật chiều cao mỗi khi click vào nút mở/đóng submenu
    document.querySelectorAll('.submenu-toggle-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            setTimeout(updateMenuHeight, 50);
        });
    });

    (function() {
    // Sử dụng biến cục bộ bên trong IIFE để tránh xung đột toàn cục (global scope)
    let scrollUpCount = 0;
    let lastScrollTop = 0;

    // 1. Kiểm tra khi ở đầu trang thì luôn hiển thị thanh bar
    window.addEventListener('scroll', function() {
        let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        let quickBar = document.getElementById('admin-quick-bar');
        
        if (!quickBar) return; // Nếu trang nào không có thanh bar thì thoát luôn, tránh lỗi

        if (currentScroll <= 20) {
            quickBar.style.transform = 'translateY(0)';
            scrollUpCount = 0;
        } else if (currentScroll > lastScrollTop && currentScroll > 50) {
            // Đang cuộn xuống -> Ẩn thanh bar đi
            quickBar.style.transform = 'translateY(-100%)';
            scrollUpCount = 0;
        }
        lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
    }, { passive: true });

    // 2. Bắt sự kiện lăn chuột (wheel) để đếm chính xác số nhịp cuộn lên
    window.addEventListener('wheel', function(event) {
        let quickBar = document.getElementById('admin-quick-bar');
        let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        
        if (!quickBar) return;

        // Nếu đang ở đầu trang thì không cần đếm nữa
        if (currentScroll <= 20) return;

        if (event.deltaY < 0) {
            // Hướng cuộn lên
            let isHidden = quickBar.style.transform === 'translateY(-100%)';
            
            if (isHidden) {
                scrollUpCount++;
                
                // Đủ số nhịp cuộn chuột lên thì mới hiện thanh bar (bạn có thể đổi số 4 thành số khác tùy ý)
                if (scrollUpCount >= 4) {
                    quickBar.style.transform = 'translateY(0)';
                    scrollUpCount = 0; // Reset bộ đếm
                }
            }
        } else if (event.deltaY > 0) {
            // Hướng cuộn xuống -> reset lại bộ đếm cuộn lên
            scrollUpCount = 0;
        }
    }, { passive: true });
})();


    document.addEventListener("DOMContentLoaded", function() {
        // Tìm tất cả các thẻ img nằm trong phần .content của bài viết
        const images = document.querySelectorAll('.content img');
        images.forEach(function(img) {
            img.removeAttribute('title'); // Xóa bỏ hoàn toàn thuộc tính title
        });
    });

/* =====================================================================
   HOVER CARD SAN PHAM - 2 LOI
   1. Hover ca card: CSS doi sang anh thu 2 (.product-img-hover)
   2. Hover dung vao O ANH: phong to anh that theo chuot. Panel moc vao
      con tro va mo ra huong NGOAI VA tam o anh (chuot goc nao => panel
      phia goc do), nen no khong che nut Xem nhanh nam giua anh.
   ===================================================================== */
(function () {
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

    const SCALE = 1.9;
    const EDGE = 8;
    // Cach cua panel ra khoi con tro de khi chuot o chinh giua anh,
    // panel van dung ngoai nut Xem nhanh
    const GAP = 16;
    let zoom = null;
    let owner = null;
    let leaveTimer = null;

    function close() {
        if (!zoom) return;
        const current = zoom;
        zoom = null;
        owner = null;
        current.classList.remove('is-in');
        setTimeout(function () {
            current.remove();
        }, 300);
    }

    function fit(v, max) {
        return Math.max(EDGE, Math.min(v, max));
    }

    function open(card, img, cx, cy) {
        // San pham chi co 1 anh thi phong to anh dai dien
        const src = card.querySelector('.product-img-hover') || card.querySelector('.product-img-main');
        if (!src) return;

        const vw = window.innerWidth;
        const vh = window.innerHeight;
        const width = Math.min(Math.round(img.width * SCALE), vw - EDGE * 2);
        const height = Math.min(Math.round(img.height * SCALE), vh - EDGE * 2);

        const toRight = cx >= img.left + img.width / 2;
        const toBottom = cy >= img.top + img.height / 2;
        let left = toRight ? cx + GAP : cx - GAP - width;
        let top = toBottom ? cy + GAP : cy - GAP - height;

        // Sat le man hinh thi dao sang phia ben kia neu con cho
        if (left < EDGE || left + width > vw - EDGE) {
            const flip = toRight ? cx - GAP - width : cx + GAP;
            left = (flip >= EDGE && flip + width <= vw - EDGE) ? flip : fit(left, vw - width - EDGE);
        }
        if (top < EDGE || top + height > vh - EDGE) {
            const flip = toBottom ? cy - GAP - height : cy + GAP;
            top = (flip >= EDGE && flip + height <= vh - EDGE) ? flip : fit(top, vh - height - EDGE);
        }

        // Panel duoc deo len o anh nhung khong duoc che nut Xem nhanh o giua
        // anh: thu day ra 4 phia (va 4 goc), chon phia het de ma di chuyen it
        // nhat sau khi da kep vao man hinh
        const btn = card.querySelector('.view-product-smart');
        if (btn) {
            const b = btn.getBoundingClientRect();
            const kL = b.left - 6, kT = b.top - 6, kR = b.right + 6, kB = b.bottom + 6;
            const area = (x, y) => Math.max(0, Math.min(x + width, kR) - Math.max(x, kL)) *
                Math.max(0, Math.min(y + height, kB) - Math.max(y, kT));
            if (area(left, top) > 0) {
                const cand = [
                    [kR, top], [kL - width, top], [left, kB], [left, kT - height],
                    [kR, kB], [kL - width, kB], [kR, kT - height], [kL - width, kT - height]
                ];
                let best = null;
                for (const p of cand) {
                    const x = fit(p[0], vw - width - EDGE);
                    const y = fit(p[1], vh - height - EDGE);
                    const cost = area(x, y) * 1e4 + Math.abs(x - left) + Math.abs(y - top);
                    if (!best || cost < best.cost) best = { x, y, cost };
                }
                left = best.x;
                top = best.y;
            }
        }

        const changed = owner !== card;
        if (!zoom) {
            zoom = document.createElement('img');
            zoom.className = 'pc-zoom';
            zoom.alt = '';
        } else if (changed) {
            // Sang card khac: chay lai animation moc ra
            zoom.classList.remove('is-in');
            void zoom.offsetWidth;
        }
        if (changed) zoom.src = src.currentSrc || src.src;
        zoom.style.width = width + 'px';
        zoom.style.height = height + 'px';
        zoom.style.left = left + 'px';
        zoom.style.top = top + 'px';
        // Moc goc o gap gan con tro nhat de panel moc ra tu phia chuot
        zoom.style.transformOrigin = (left >= cx ? 0 : width) + 'px ' + (top >= cy ? 0 : height) + 'px';

        document.body.appendChild(zoom);
        owner = card;

        const shown = zoom;
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                if (shown === zoom) shown.classList.add('is-in');
            });
        });
    }

    document.addEventListener('mousemove', function (e) {
        const card = e.target.closest && e.target.closest('.product-card.item-product');
        const box = card && card.querySelector('.product-image-wrapper');
        // Link phu bao tron card (z-index 2) luon la target, nen phai do toa do
        // chuot xem no co cham vao o anh khong
        if (!box) {
            if (!leaveTimer) leaveTimer = setTimeout(close, 60);
            return;
        }
        const img = box.getBoundingClientRect();
        const inside = e.clientX >= img.left && e.clientX <= img.right &&
            e.clientY >= img.top && e.clientY <= img.bottom;
        if (!inside) {
            if (!leaveTimer) leaveTimer = setTimeout(close, 60);
            return;
        }
        clearTimeout(leaveTimer);
        leaveTimer = null;
        open(card, img, e.clientX, e.clientY);
    }, { passive: true });

    window.addEventListener('scroll', close, { passive: true });
})();

/* =====================================================================
   WIDGET LIEN HE NOI (Zalo / Messenger / Hotline)
   Markup nam trong web/layout.blade.php, duoi <script defer> nen DOM
   da san khi doan chay.
   ===================================================================== */
(function () {
    const wrap = document.getElementById('lhWrap');
    if (!wrap) return;

    const fab = wrap.querySelector('.lh-fab');
    if (!fab) return;

    function setOpen(open) {
        wrap.classList.toggle('is-open', open);
        fab.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    fab.addEventListener('click', function () {
        setOpen(!wrap.classList.contains('is-open'));
    });

    const closeBtn = wrap.querySelector('.lh-close');
    if (closeBtn) closeBtn.addEventListener('click', () => setOpen(false));

    // Mo san pham/kenh ben trong roi dong lai (mobile: quay lai trang da mo)
    wrap.querySelectorAll('.lh-row').forEach(row => {
        row.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('click', function (e) {
        if (wrap.contains(e.target)) return;
        setOpen(false);
    }, true);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && wrap.classList.contains('is-open')) {
            setOpen(false);
            fab.focus();
        }
    });
})();