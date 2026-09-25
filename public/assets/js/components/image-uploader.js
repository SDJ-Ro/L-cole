// MVC/public/assets/js/components/image-uploader.js
// Standardized client-side image upload and gallery management component.

(function () {
  'use strict';

  if (window.ImageUploader) return;

  const escapeHtml = window.escapeHtml || ((text) => {
    if (!text && text !== 0) return '';
    return String(text).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  });

  const ImageUploader = {
    /**
     * Reads a single File object as a Data URL with validation.
     * @param {File} file 
     * @param {number} maxSizeMB 
     * @returns {Promise<string|null>}
     */
    readFile(file, maxSizeMB = 10) {
      return new Promise((resolve, reject) => {
        if (!file) {
          resolve(null);
          return;
        }

        if (!file.type.startsWith('image/')) {
          console.warn('Selected file is not an image:', file.name);
          resolve(null);
          return;
        }

        if (file.size > maxSizeMB * 1024 * 1024) {
          alert(`Image size exceeds the ${maxSizeMB}MB limit. Please choose a smaller image.`);
          resolve(null);
          return;
        }

        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target.result);
        reader.onerror = (e) => reject(e);
        reader.readAsDataURL(file);
      });
    },

    /**
     * Reads multiple File objects as Data URLs.
     * @param {FileList|File[]} files 
     * @param {number} maxSizeMB 
     * @returns {Promise<string[]>}
     */
    readMultipleFiles(files, maxSizeMB = 10) {
      if (!files || !files.length) return Promise.resolve([]);
      const promises = Array.from(files).map((f) => this.readFile(f, maxSizeMB));
      return Promise.all(promises).then((results) => results.filter(Boolean));
    },

    /**
     * Binds a single image picker (preview box + hidden input).
     * @param {Object} options
     * @param {string|HTMLInputElement} options.input - Input element or selector ID
     * @param {string|HTMLElement} options.preview - Preview element or selector ID
     * @param {string} [options.emptyText='No cover photo selected']
     * @param {function(string, File): void} [options.onLoaded]
     * @param {function(): void} [options.onCleared]
     */
    setupSinglePicker({ input, preview, emptyText = 'No cover photo selected', onLoaded, onCleared }) {
      const inputEl = typeof input === 'string' ? document.getElementById(input) : input;
      const previewEl = typeof preview === 'string' ? document.getElementById(preview) : preview;

      if (!inputEl) return null;

      const setPreview = (dataUrl) => {
        if (!previewEl) return;
        if (dataUrl) {
          previewEl.innerHTML = `<img class="j-ex-124" src="${dataUrl}" alt="Preview" />`;
        } else {
          previewEl.innerHTML = `<span class="j-ex-119">${escapeHtml(emptyText)}</span>`;
        }
      };

      const changeHandler = (e) => {
        e.preventDefault();
        const file = e.target.files && e.target.files[0];
        if (file) {
          this.readFile(file).then((dataUrl) => {
            if (dataUrl) {
              setPreview(dataUrl);
              if (typeof onLoaded === 'function') onLoaded(dataUrl, file);
            }
            inputEl.value = '';
          }).catch((err) => {
            console.error('Image read error:', err);
            inputEl.value = '';
          });
        }
      };

      inputEl.addEventListener('change', changeHandler);

      return {
        setPreview,
        clear() {
          inputEl.value = '';
          setPreview(null);
          if (typeof onCleared === 'function') onCleared();
        }
      };
    },

    /**
     * Binds a multi-photo gallery container.
     * @param {Object} options
     * @param {string|HTMLElement} options.container - Gallery grid container
     * @param {string|HTMLInputElement} options.input - Multiple file input
     * @param {string|HTMLElement} [options.trigger] - Button to trigger upload
     * @param {string[]} [options.initialImages=[]]
     * @param {boolean} [options.isEditable=true]
     * @param {function(string[]): void} [options.onChanged]
     */
    setupGallery({ container, input, trigger, initialImages = [], isEditable = true, onChanged }) {
      const containerEl = typeof container === 'string' ? document.getElementById(container) : container;
      const inputEl = typeof input === 'string' ? document.getElementById(input) : input;
      let images = Array.isArray(initialImages) ? initialImages.slice() : [];

      if (!containerEl) return null;

      const render = () => {
        containerEl.innerHTML = `
          ${images.map((src, idx) => `
            <div class="c-gallery-item j-ex-105">
              <img class="j-ex-106" src="${escapeHtml(src)}" alt="Gallery photo ${idx + 1}" />
              ${isEditable ? `
                <button type="button" class="j-remove-gallery-img j-ex-107" data-idx="${idx}" title="Delete photo">
                  <svg class="c-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-x"/></svg>
                </button>
              ` : ''}
            </div>
          `).join('')}
          ${isEditable ? `
            <button type="button" class="c-gallery-add-btn j-gallery-add-trigger">
              <svg class="c-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-plus"/></svg>
              <span>Add photos</span>
            </button>
          ` : ''}
        `;

        if (isEditable) {
          containerEl.querySelectorAll('.j-remove-gallery-img').forEach((btn) => {
            btn.addEventListener('click', (e) => {
              e.preventDefault();
              e.stopPropagation();
              const idx = Number(btn.dataset.idx);
              images.splice(idx, 1);
              render();
              if (typeof onChanged === 'function') onChanged(images);
            });
          });

          const addBtn = containerEl.querySelector('.j-gallery-add-trigger');
          if (addBtn && inputEl) {
            addBtn.onclick = (e) => {
              e.preventDefault();
              e.stopPropagation();
              inputEl.click();
            };
          }
        }
      };

      if (inputEl && isEditable) {
        inputEl.addEventListener('change', (e) => {
          e.preventDefault();
          const files = e.target.files;
          if (files && files.length) {
            this.readMultipleFiles(files).then((newUrls) => {
              if (newUrls.length) {
                images = images.concat(newUrls);
                render();
                if (typeof onChanged === 'function') onChanged(images);
              }
              inputEl.value = '';
            });
          }
        });
      }

      render();

      return {
        getImages() {
          return images.slice();
        },
        setImages(newImages) {
          images = Array.isArray(newImages) ? newImages.slice() : [];
          render();
        },
        clear() {
          images = [];
          if (inputEl) inputEl.value = '';
          render();
          if (typeof onChanged === 'function') onChanged(images);
        }
      };
    }
  };

  window.ImageUploader = ImageUploader;
})();
