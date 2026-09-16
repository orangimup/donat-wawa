@extends('layouts.admin')

@section('title', 'Product Management — Donat Wawa')

@section('content')

    <div class="admin-page-header">
        <div>
            <h1>Product Management</h1>
            <p>Manage your bakery's sweet offerings.</p>
        </div>
        <button type="button" class="admin-btn" onclick="openCreateModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                stroke-linecap="round">
                <path d="M12 5v14M5 12h14" />
            </svg>
            New Product
        </button>
    </div>

    <div class="admin-card">
        <div class="admin-toolbar">
            <div class="admin-filter-pills">
                @php
                    $tabs = ['all' => 'All', 'classics' => 'Classics', 'special' => 'Special'];
                @endphp
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('admin.menu-items.index', array_filter(['category' => $key, 'search' => $search, 'sort' => $sort])) }}"
                        class="admin-filter-pill {{ $category === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <form method="GET" class="admin-sort" id="sortForm">
                <label for="sortTrigger">Sort by:</label>
                <input type="hidden" name="category" value="{{ $category }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="sort" id="sortValue" value="{{ $sort }}">

                @php
                    $sortLabels = [
                        'latest' => 'Latest',
                        'name' => 'Name',
                        'price_asc' => 'Price: Low to High',
                        'price_desc' => 'Price: High to Low',
                        'stock' => 'Stock',
                    ];
                @endphp

                <div class="admin-select" id="sortSelect">
                    <button type="button" class="admin-select-trigger" id="sortTrigger" aria-haspopup="listbox"
                        aria-expanded="false">
                        <span id="sortTriggerLabel">{{ $sortLabels[$sort] ?? 'Latest' }}</span>
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                    <ul class="admin-select-menu" id="sortMenu" role="listbox">
                        @foreach ($sortLabels as $key => $label)
                            <li role="option" data-value="{{ $key }}"
                                class="admin-select-option {{ $sort === $key ? 'is-selected' : '' }}">{{ $label }}</li>
                        @endforeach
                    </ul>
                </div>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($menuItems as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($menuItems->currentPage() - 1) * $menuItems->perPage() }}</td>
                            <td><img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="admin-table-thumb"></td>
                            <td>
                                <span class="admin-table-name">{{ $item->name }}</span>
                            </td>
                            <td>
                                <span class="admin-table-desc">{{ $item->description }}</span>
                            </td>
                            <td>{{ ucfirst($item->category) }}</td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>{{ $item->stock }}</td>
                            <td>
                                <span
                                    class="admin-status-pill {{ $item->status === 'active' ? 'admin-status-active' : 'admin-status-inactive' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="admin-table-actions">
                                    <button type="button" class="admin-icon-btn" aria-label="Edit {{ $item->name }}"
                                        onclick="openEditModal(this)" data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                        data-description="{{ $item->description }}" data-category="{{ $item->category }}"
                                        data-price="{{ $item->price }}"
                                        data-stock="{{ $item->stock }}" data-status="{{ $item->status }}"
                                        data-has-image="{{ $item->image ? '1' : '0' }}" data-image-url="{{ $item->image_url }}"
                                        data-image-name="{{ $item->image ? basename($item->image) : '' }}"
                                        data-update-url="{{ route('admin.menu-items.update', $item) }}"
                                        data-destroy-url="{{ route('admin.menu-items.destroy', $item) }}">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                        </svg>
                                    </button>
                                    <form action="{{ route('admin.menu-items.destroy', $item) }}" method="POST"
                                        class="admin-delete-form" data-name="{{ $item->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="admin-icon-btn admin-icon-btn-danger"
                                            aria-label="Delete {{ $item->name }}" onclick="openDeleteModal(this.closest('form'))">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="admin-empty-row">
                            <td colspan="9">No products yet. Click "New Product" to add one.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            <span class="admin-pagination-info">
                Showing {{ $menuItems->count() }} of {{ $menuItems->total() }} entries
            </span>
            {{ $menuItems->onEachSide(1)->links('admin.partials.pagination') }}
        </div>
    </div>

    <div class="admin-modal-overlay" id="productModalOverlay">
        <div class="admin-modal">
            <div class="admin-modal-head">
                <div>
                    <h2 id="productModalTitle">Add New Product</h2>
                    <p class="admin-modal-subtitle" id="productModalSubtitle">Enter the details of a new Donat Wawa
                        artisan donut variant</p>
                </div>
                <button type="button" class="admin-modal-close" onclick="closeProductModal()" aria-label="Close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="productForm" method="POST" action="{{ route('admin.menu-items.store') }}"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="productFormMethod" value="POST">
                <input type="hidden" name="_edit_id" id="productFormEditId" value="{{ old('_edit_id') }}">

                @include('admin.product-form')

                <div class="admin-form-actions">
                    <div class="admin-form-actions-right">
                        <button type="button" class="admin-btn admin-btn-outline"
                            onclick="closeProductModal()">Cancel</button>
                        <button type="submit" class="admin-btn" id="productFormSubmit">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.6" stroke-linecap="round">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            <span id="productFormSubmitText">Save Product</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

        <div class="admin-modal-overlay admin-modal-overlay-center" id="deleteModalOverlay">
        <div class="admin-modal admin-modal-sm">
            <div class="admin-modal-head">
                <div>
                    <h2>Delete Product?</h2>
                    <p class="admin-modal-subtitle" id="deleteModalText">Are you sure you want to delete this
                        product?</p>
                </div>
                <button type="button" class="admin-modal-close" onclick="closeDeleteModal()" aria-label="Close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="admin-form-actions">
                <div class="admin-form-actions-right">
                    <button type="button" class="admin-btn admin-btn-outline"
                        onclick="closeDeleteModal()">Cancel</button>
                    <button type="button" class="admin-btn admin-btn-danger" id="deleteConfirmBtn">Delete
                        Product</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            const sortSelect = document.getElementById('sortSelect');
            const sortTrigger = document.getElementById('sortTrigger');
            const sortTriggerLabel = document.getElementById('sortTriggerLabel');
            const sortMenu = document.getElementById('sortMenu');
            const sortValue = document.getElementById('sortValue');
            const sortForm = document.getElementById('sortForm');

            function closeSortMenu() {
                sortSelect.classList.remove('is-open');
                sortTrigger.setAttribute('aria-expanded', 'false');
            }

            sortTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = sortSelect.classList.toggle('is-open');
                sortTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            sortMenu.querySelectorAll('.admin-select-option').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    sortValue.value = opt.dataset.value;
                    sortTriggerLabel.textContent = opt.textContent;
                    sortMenu.querySelectorAll('.admin-select-option').forEach(function (o) { o.classList.remove('is-selected'); });
                    opt.classList.add('is-selected');
                    closeSortMenu();
                    sortForm.submit();
                });
            });

            document.addEventListener('click', function (e) {
                if (!sortSelect.contains(e.target)) closeSortMenu();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeSortMenu();
            });
        })();

        (function () {
            const overlay = document.getElementById('productModalOverlay');
            const form = document.getElementById('productForm');
            const methodField = document.getElementById('productFormMethod');
            const editIdField = document.getElementById('productFormEditId');
            const title = document.getElementById('productModalTitle');
            const subtitle = document.getElementById('productModalSubtitle');
            const submitText = document.getElementById('productFormSubmitText');
            const submitIcon = document.getElementById('productFormSubmit').querySelector('svg');
            const stockLabel = document.getElementById('stockLabel');

            const dropzone = document.getElementById('imageDropzone');
            const fileInput = document.getElementById('image');
            const currentImageBlock = document.getElementById('currentImageBlock');
            const currentImagePreview = document.getElementById('currentImagePreview');
            const currentImageName = document.getElementById('currentImageName');
            const removeImageFlag = document.getElementById('removeImageFlag');
            const changePhotoBtn = document.getElementById('changePhotoBtn');
            const removePhotoBtn = document.getElementById('removePhotoBtn');

            const statusToggleBtn = document.getElementById('statusToggleBtn');
            const statusToggleLabel = document.getElementById('statusToggleLabel');
            const statusToggleTitle = document.getElementById('statusToggleTitle');
            const statusToggleHint = document.getElementById('statusToggleHint');
            const statusField = document.getElementById('status');

            const fields = ['name', 'description', 'price', 'stock'];

            function showDropzone() {
                dropzone.style.display = 'flex';
                currentImageBlock.style.display = 'none';
            }

            function showCurrentImage(url, name) {
                currentImagePreview.src = url;
                currentImageName.textContent = name || 'Foto produk';
                dropzone.style.display = 'none';
                currentImageBlock.style.display = 'flex';
            }

            function setStatus(isActive) {
                statusField.value = isActive ? 'active' : 'inactive';
                statusToggleBtn.classList.toggle('is-on', isActive);
                statusToggleBtn.setAttribute('aria-checked', isActive ? 'true' : 'false');
                statusToggleLabel.textContent = isActive ? 'Active' : 'Inactive';
            }

            statusToggleBtn.addEventListener('click', function () {
                setStatus(statusField.value !== 'active');
            });

            dropzone.addEventListener('click', function () { fileInput.click(); });
            changePhotoBtn.addEventListener('click', function () { fileInput.click(); });

            removePhotoBtn.addEventListener('click', function () {
                removeImageFlag.value = '1';
                fileInput.value = '';
                showDropzone();
            });

            fileInput.addEventListener('change', function () {
                if (fileInput.files && fileInput.files[0]) {
                    removeImageFlag.value = '0';
                    const url = URL.createObjectURL(fileInput.files[0]);
                    showCurrentImage(url, fileInput.files[0].name);
                }
            });

            ['dragenter', 'dragover'].forEach(function (evt) {
                dropzone.addEventListener(evt, function (e) {
                    e.preventDefault();
                    dropzone.classList.add('is-dragover');
                });
            });
            ['dragleave', 'drop'].forEach(function (evt) {
                dropzone.addEventListener(evt, function (e) {
                    e.preventDefault();
                    dropzone.classList.remove('is-dragover');
                });
            });
            dropzone.addEventListener('drop', function (e) {
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    fileInput.files = e.dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });

            const categorySelect = document.getElementById('categorySelect');
            const categoryTrigger = document.getElementById('categoryTrigger');
            const categoryTriggerLabel = document.getElementById('categoryTriggerLabel');
            const categoryMenu = document.getElementById('categoryMenu');
            const categoryField = document.getElementById('category');
            const categoryLabels = { classics: 'Classics', special: 'Special' };

            function closeCategoryMenu() {
                categorySelect.classList.remove('is-open');
                categoryTrigger.setAttribute('aria-expanded', 'false');
            }

            function setCategory(value) {
                categoryField.value = value || '';
                categoryTriggerLabel.textContent = categoryLabels[value] || 'Select Category...';
                categoryTrigger.classList.toggle('is-placeholder', !value);
                categoryMenu.querySelectorAll('.admin-select-option').forEach(function (o) {
                    o.classList.toggle('is-selected', o.dataset.value === value);
                });
            }

            categoryTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = categorySelect.classList.toggle('is-open');
                categoryTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            categoryMenu.querySelectorAll('.admin-select-option').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    setCategory(opt.dataset.value);
                    closeCategoryMenu();
                });
            });

            document.addEventListener('click', function (e) {
                if (!categorySelect.contains(e.target)) closeCategoryMenu();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeCategoryMenu();
            });

            window.openProductModal = function () {
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            };

            window.closeProductModal = function () {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            };

            window.openCreateModal = function () {
                form.reset();
                form.action = "{{ route('admin.menu-items.store') }}";
                methodField.value = 'POST';
                editIdField.value = '';
                removeImageFlag.value = '0';
                title.textContent = 'Add New Product';
                subtitle.textContent = 'Enter the details of a new Donat Wawa artisan donut variant';
                stockLabel.innerHTML = 'STOCK QUANTITY <span class="admin-required">*</span>';
                statusToggleTitle.textContent = 'Publication Status';
                statusToggleHint.textContent = 'Show immediately in the customer menu catalog';
                submitText.textContent = 'Save Product';
                submitIcon.style.display = '';
                setStatus(true);
                setCategory('');
                showDropzone();
                openProductModal();
            };

            window.openEditModal = function (btn) {
                form.reset();
                fields.forEach(function (f) {
                    const el = document.getElementById(f);
                    if (el) el.value = btn.dataset[f] || '';
                });
                form.action = btn.dataset.updateUrl;
                methodField.value = 'PUT';
                editIdField.value = btn.dataset.id;
                removeImageFlag.value = '0';
                title.textContent = 'Edit Product Variant';
                subtitle.textContent = 'Update the price, stock, or recipe details for this donut';
                stockLabel.innerHTML = 'AVAILABLE STOCK <span class="admin-required">*</span>';
                statusToggleTitle.textContent = 'Product Status';
                statusToggleHint.textContent = 'Availability of this variant on the public menu';
                submitText.textContent = 'Save Changes';
                submitIcon.style.display = 'none';
                setStatus(btn.dataset.status === 'active');
                setCategory(btn.dataset.category);

                if (btn.dataset.hasImage === '1') {
                    showCurrentImage(btn.dataset.imageUrl, btn.dataset.imageName);
                } else {
                    showDropzone();
                }

                openProductModal();
            };

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeProductModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeProductModal();
            });

            @if ($errors->any())
                @if ($retryItem)
                    form.action = "{{ route('admin.menu-items.update', $retryItem) }}";
                    methodField.value = 'PUT';
                    title.textContent = 'Edit Product Variant';
                    subtitle.textContent = 'Update the price, stock, or recipe details for this donut';
                    stockLabel.innerHTML = 'AVAILABLE STOCK <span class="admin-required">*</span>';
                    statusToggleTitle.textContent = 'Product Status';
                    statusToggleHint.textContent = 'Availability of this variant on the public menu';
                    submitText.textContent = 'Save Changes';
                    submitIcon.style.display = 'none';
                    @if ($retryItem->image)
                        showCurrentImage("{{ $retryItem->image_url }}", "{{ basename($retryItem->image) }}");
                    @endif
                @else
                    form.action = "{{ route('admin.menu-items.store') }}";
                    methodField.value = 'POST';
                    title.textContent = 'Add New Product';
                    subtitle.textContent = 'Enter the details of a new Donat Wawa artisan donut variant';
                @endif
                setStatus(document.getElementById('status').value === 'active');
                setCategory(document.getElementById('category').value);
                openProductModal();
            @endif
        })();

        (function () {
            const overlay = document.getElementById('deleteModalOverlay');
            const text = document.getElementById('deleteModalText');
            const confirmBtn = document.getElementById('deleteConfirmBtn');
            let activeForm = null;

            window.openDeleteModal = function (form) {
                activeForm = form;
                text.textContent = 'Are you sure you want to delete "' + form.dataset.name + '"? This action cannot be undone.';
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            };

            window.closeDeleteModal = function () {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
                activeForm = null;
            };

            confirmBtn.addEventListener('click', function () {
                if (activeForm) activeForm.submit();
            });

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeDeleteModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.classList.contains('open')) closeDeleteModal();
            });
        })();
    </script>
@endpush