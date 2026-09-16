(function () {
    const STORAGE_KEY = "donat_basket";

    function readBasket() {
        try {
            const raw = sessionStorage.getItem(STORAGE_KEY);
            const items = raw ? JSON.parse(raw) : [];
            return Array.isArray(items) ? items : [];
        } catch (e) {
            return [];
        }
    }

    function writeBasket(items) {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        window.dispatchEvent(
            new CustomEvent("basket:updated", { detail: { items } }),
        );
    }

    function formatRupiah(value) {
        return "Rp" + Math.round(value || 0).toLocaleString("id-ID");
    }

    const Basket = {
        getItems() {
            return readBasket();
        },

        getTotalQty() {
            return readBasket().reduce((sum, item) => sum + item.qty, 0);
        },

        getTotalPrice() {
            return readBasket().reduce(
                (sum, item) => sum + item.qty * item.price,
                0,
            );
        },

        /**
         * @param {{slug:string,name:string,price:number,image:string}} product
         * @param {number} qty
         * @returns {boolean} true jika item berhasil ditambahkan (user sudah login)
         */
        addItem(product, qty) {
            // Guest hanya boleh melihat-lihat, tidak boleh menambah pesanan.
            if (!window.isAuthenticated) {
                if (typeof window.openAuthPopup === "function") {
                    window.openAuthPopup("login");
                } else {
                    window.location.href = "/";
                }
                return false;
            }

            qty = Math.max(1, parseInt(qty, 10) || 1);
            const items = readBasket();
            const existing = items.find((item) => item.slug === product.slug);

            if (existing) {
                existing.qty += qty;
            } else {
                items.push({
                    slug: product.slug,
                    name: product.name,
                    price: Number(product.price) || 0,
                    image: product.image || "",
                    qty,
                });
            }

            writeBasket(items);
            return true;
        },

        setQty(slug, qty) {
            qty = parseInt(qty, 10) || 0;
            let items = readBasket();

            if (qty <= 0) {
                items = items.filter((item) => item.slug !== slug);
            } else {
                const existing = items.find((item) => item.slug === slug);
                if (existing) existing.qty = qty;
            }

            writeBasket(items);
        },

        removeItem(slug) {
            writeBasket(readBasket().filter((item) => item.slug !== slug));
        },

        /**
         * Ganti seluruh isi keranjang sekaligus (dipakai saat sinkron ulang
         * ke data terbaru database di halaman checkout).
         */
        replaceAll(items) {
            writeBasket(items);
        },

        clear() {
            writeBasket([]);
        },

        format: formatRupiah,
    };

    window.DonatBasket = Basket;

    // Kalau user sudah logout tapi sessionStorage masih nyimpen keranjang
    // dari sesi login sebelumnya (tab yang sama), bersihkan supaya guest
    // tidak bisa lanjut checkout dari sisa data lama.
    if (!window.isAuthenticated && readBasket().length > 0) {
        writeBasket([]);
    }

    let footerVisible = false;

    document.addEventListener("DOMContentLoaded", function () {
        function renderQtyWidgets() {
            const items = Basket.getItems();

            document
                .querySelectorAll(".basket-qty-widget")
                .forEach(function (widget) {
                    const slug = widget.dataset.slug;
                    const existing = items.find((item) => item.slug === slug);
                    const qty = existing ? existing.qty : 0;
                    const qtyValue = widget.querySelector(".basket-qty-value");

                    if (qty > 0) {
                        widget.classList.add("is-active");
                        if (qtyValue) qtyValue.textContent = qty;
                    } else {
                        widget.classList.remove("is-active");
                        if (qtyValue) qtyValue.textContent = 0;
                    }
                });
        }
        document
            .querySelectorAll(".basket-qty-widget")
            .forEach(function (widget) {
                const product = {
                    slug: widget.dataset.slug,
                    name: widget.dataset.name,
                    price: widget.dataset.price,
                    image: widget.dataset.image,
                };

                const addBtn = widget.querySelector(".basket-add-btn");
                const decBtn = widget.querySelector(".basket-decrement-btn");

                if (addBtn) {
                    addBtn.addEventListener("click", function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const added = Basket.addItem(product, 1);
                        if (!added) return;

                        addBtn.classList.add("is-added");
                        setTimeout(function () {
                            addBtn.classList.remove("is-added");
                        }, 350);
                    });
                }

                if (decBtn) {
                    decBtn.addEventListener("click", function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const current = Basket.getItems().find(
                            (item) => item.slug === product.slug,
                        );
                        const currentQty = current ? current.qty : 0;
                        Basket.setQty(product.slug, currentQty - 1);
                    });
                }
            });

        const footer = document.querySelector(".footer");
        if (footer && "IntersectionObserver" in window) {
            const observer = new IntersectionObserver(
                function (entries) {
                    footerVisible = entries[0].isIntersecting;
                    renderFloatingBar();
                },
                { rootMargin: "0px" },
            );
            observer.observe(footer);
        }

        renderQtyWidgets();
        renderFloatingBar();
        window.addEventListener("basket:updated", renderQtyWidgets);
        window.addEventListener("basket:updated", renderFloatingBar);
    });

    function renderFloatingBar() {
        const bar = document.getElementById("basketFloatingBar");
        if (!bar) return;

        const totalQty = Basket.getTotalQty();

        if (totalQty <= 0 || footerVisible) {
            bar.hidden = true;
            return;
        }

        bar.hidden = false;
        const badge = document.getElementById("basketFloatingBadge");
        const count = document.getElementById("basketFloatingCount");
        const total = document.getElementById("basketFloatingTotal");

        if (badge) badge.textContent = totalQty;
        if (count) count.textContent = totalQty + " Item";
        if (total) total.textContent = formatRupiah(Basket.getTotalPrice());
    }
})();