<div class="admin-form-group full">
    <label for="image">DONUT PRODUCT PHOTO <span class="admin-required">*</span></label>

    <div class="admin-dropzone" id="imageDropzone">
        <div class="admin-dropzone-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="3"></rect>
                <circle cx="8.5" cy="9.5" r="1.5"></circle>
                <path d="M21 15l-5-5L5 21"></path>
            </svg>
        </div>
        <p class="admin-dropzone-text">Click to upload or drag &amp; drop a file</p>
        <p class="admin-dropzone-hint">PNG, JPG or JPEG (max. 5MB)</p>
    </div>

    <div class="admin-current-image" id="currentImageBlock" style="display:none;">
        <div class="admin-current-image-thumb">
            <img src="" alt="" id="currentImagePreview">
        </div>
        <div class="admin-current-image-info">
            <strong id="currentImageName">&nbsp;</strong>
            <div class="admin-current-image-actions">
                <button type="button" class="admin-link-btn" id="changePhotoBtn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 19V5"></path>
                        <path d="m5 12 7-7 7 7"></path>
                    </svg>
                    Change Photo
                </button>
                <span class="admin-dot">·</span>
                <button type="button" class="admin-link-btn admin-link-btn-danger" id="removePhotoBtn">Remove
                    Photo</button>
            </div>
        </div>
    </div>

    <input type="file" name="image" id="image" accept="image/*" class="admin-file-input">
    <input type="hidden" name="remove_image" id="removeImageFlag" value="0">
    @error('image')
        <span class="admin-form-error">{{ $message }}</span>
    @enderror
</div>

<div class="admin-form-grid">
    <div class="admin-form-group">
        <label for="name">PRODUCT NAME <span class="admin-required">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Enter product name...">
        @error('name')
            <span class="admin-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="admin-form-group">
        <label for="categoryTrigger">CATEGORY <span class="admin-required">*</span></label>
        <div class="admin-select admin-select-form" id="categorySelect">
            <button type="button" class="admin-select-trigger {{ old('category') ? '' : 'is-placeholder' }}"
                id="categoryTrigger" aria-haspopup="listbox" aria-expanded="false">
                <span id="categoryTriggerLabel">
                    {{ old('category') === 'classics' ? 'Classics' : (old('category') === 'special' ? 'Special' : 'Select Category...') }}
                </span>
                <svg width="10" height="6" viewBox="0 0 10 6" fill="none">
                    <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
            <ul class="admin-select-menu" id="categoryMenu" role="listbox">
                <li role="option" data-value="classics"
                    class="admin-select-option {{ old('category') === 'classics' ? 'is-selected' : '' }}">Classics</li>
                <li role="option" data-value="special"
                    class="admin-select-option {{ old('category') === 'special' ? 'is-selected' : '' }}">Special</li>
            </ul>
        </div>
        <input type="hidden" name="category" id="category" value="{{ old('category') }}">
        @error('category')
            <span class="admin-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="admin-form-group">
        <label for="price">UNIT PRICE <span class="admin-required">*</span></label>
        <div class="admin-input-prefix">
            <span>Rp</span>
            <input type="number" name="price" id="price" min="0" step="500" value="{{ old('price') }}" placeholder="0">
        </div>
        @error('price')
            <span class="admin-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="admin-form-group">
        <label for="stock" id="stockLabel">STOCK QUANTITY <span class="admin-required">*</span></label>
        <div class="admin-input-suffix">
            <input type="number" name="stock" id="stock" min="0" value="{{ old('stock') }}" placeholder="0">
            <span>pcs</span>
        </div>
        @error('stock')
            <span class="admin-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="admin-form-group full">
        <label for="description">DESCRIPTION</label>
        <textarea name="description" id="description"
            placeholder="Describe the brioche dough, glaze, topping, and donut flavor...">{{ old('description') }}</textarea>
        @error('description')
            <span class="admin-form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="admin-form-group full">
        <div class="admin-toggle-row">
            <div>
                <strong id="statusToggleTitle">Publication Status</strong>
                <p id="statusToggleHint">Show immediately in the customer menu catalog</p>
            </div>
            <div class="admin-toggle-control">
                <span class="admin-toggle-label" id="statusToggleLabel">Active</span>
                <button type="button" class="admin-toggle-switch is-on" id="statusToggleBtn" role="switch"
                    aria-checked="true" aria-label="Product status"></button>
            </div>
        </div>
        <input type="hidden" name="status" id="status" value="{{ old('status', 'active') }}">
        @error('status')
            <span class="admin-form-error">{{ $message }}</span>
        @enderror
    </div>
</div>