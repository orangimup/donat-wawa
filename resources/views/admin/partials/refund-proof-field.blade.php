<div class="refund-field" id="proofField">
    <span class="refund-field-label">Proof of Transfer</span>

    <label class="admin-dropzone refund-dropzone" id="proofDropzone" for="bukti_transfer">
        <span class="proof-idle">
            <span class="admin-dropzone-icon" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4M7 9l5-5 5 5"></path><path d="M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"></path></svg>
            </span>
            <span class="admin-dropzone-text">Click to upload or drag &amp; drop</span>
            <span class="admin-dropzone-hint">PNG or JPG (max. 5MB)</span>
        </span>

        <span class="proof-preview">
            <img id="proofImg" class="proof-preview-img" alt="Transfer proof preview">
            <span class="proof-preview-bar">
                <span class="proof-preview-info">
                    <span class="proof-preview-name" id="proofName"></span>
                    <span class="proof-preview-size" id="proofSize"></span>
                </span>
                <button type="button" class="proof-icon-btn" id="proofZoom" title="View full size" aria-label="View full size">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.3-4.3M11 8v6M8 11h6"></path></svg>
                </button>
                <button type="button" class="proof-icon-btn" id="proofRemove" title="Remove file" aria-label="Remove file">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                </button>
            </span>
        </span>

        <input type="file" name="bukti_transfer" id="bukti_transfer" accept="image/png,image/jpeg" class="sr-only">
    </label>

    @error('bukti_transfer')
        <span class="admin-form-error">{{ $message }}</span>
    @enderror
</div>