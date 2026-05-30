/**
 * Friendship Group Web Application
 * Client-side JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Navigation Toggle
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    const navbar = document.getElementById('navbar');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
            document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
        });
    }

    // Navbar scroll effect
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 10) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // 2. Auto-dismiss flash alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-success, .alert-info');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            alert.style.transition = 'all 0.4s ease';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

    // 3. Delete Confirmation Modal
    const deleteTriggers = document.querySelectorAll('.delete-trigger');
    const deleteModal = document.getElementById('delete-modal');
    
    if (deleteTriggers.length > 0 && deleteModal) {
        const deleteCancel = document.getElementById('delete-cancel');
        const deleteForm = document.getElementById('delete-form');
        const deleteInput = document.getElementById('delete-input');
        const deleteLabel = document.getElementById('delete-label');

        // Open modal
        deleteTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const action = trigger.getAttribute('data-action');
                const id = trigger.getAttribute('data-id');
                const name = trigger.getAttribute('data-name');
                const label = trigger.getAttribute('data-label');

                deleteForm.setAttribute('action', action);
                deleteInput.setAttribute('name', name);
                deleteInput.value = id;
                deleteLabel.textContent = label;

                deleteModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });

        // Close modal (Cancel button)
        if (deleteCancel) {
            deleteCancel.addEventListener('click', () => {
                deleteModal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }

        // Close modal (Click outside)
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    // 4. Photo Upload Drag & Drop and Preview
    const fileUploadArea = document.getElementById('file-upload-area');
    const photoInput = document.getElementById('photo');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');

    if (fileUploadArea && photoInput) {
        // Click to open file dialog
        fileUploadArea.addEventListener('click', () => {
            photoInput.click();
        });

        // Drag events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, () => {
                fileUploadArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, () => {
                fileUploadArea.classList.remove('dragover');
            }, false);
        });

        // Handle drop
        fileUploadArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                photoInput.files = files;
                handleFiles(files[0]);
            }
        });

        // Handle input change
        photoInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                handleFiles(this.files[0]);
            }
        });

        function handleFiles(file) {
            // Check if file is image
            if (!file.type.startsWith('image/')) {
                alert('Please upload an image file (JPG, PNG, WEBP).');
                return;
            }

            // Check size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size exceeds the 5MB limit.');
                return;
            }

            // Update UI text
            const textElement = fileUploadArea.querySelector('.file-upload-text');
            if (textElement) {
                textElement.innerHTML = `Selected: <strong>${file.name}</strong>`;
            }

            // Show preview
            if (imagePreview && previewImg) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImg.src = e.target.result;
                    imagePreview.classList.add('active');
                };
                reader.readAsDataURL(file);
            }
        }
    }

    // 5. Dynamic Vote Options
    const addOptionBtn = document.getElementById('add-option-btn');
    const optionsContainer = document.getElementById('options-container');

    if (addOptionBtn && optionsContainer) {
        addOptionBtn.addEventListener('click', () => {
            const optionCount = optionsContainer.children.length;
            
            const groupDiv = document.createElement('div');
            groupDiv.className = 'option-input-group';
            groupDiv.style.opacity = '0';
            groupDiv.style.transform = 'translateY(-10px)';
            groupDiv.style.transition = 'all 0.3s ease';
            
            groupDiv.innerHTML = `
                <input type="text" name="options[]" placeholder="Option ${optionCount + 1}" required maxlength="150">
                <button type="button" class="btn-remove-option" title="Remove option" onclick="this.parentElement.remove()">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            `;
            
            optionsContainer.appendChild(groupDiv);
            
            // Trigger animation
            setTimeout(() => {
                groupDiv.style.opacity = '1';
                groupDiv.style.transform = 'translateY(0)';
            }, 10);
            
            // Focus new input
            groupDiv.querySelector('input').focus();
        });
    }

    // 6. Form Submission Buttons Loading State
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Check if form is valid before showing loading state
            if (!this.checkValidity()) {
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalContent = submitBtn.innerHTML;
                const originalWidth = submitBtn.offsetWidth;
                
                // Keep button width from collapsing
                submitBtn.style.minWidth = `${originalWidth}px`;
                submitBtn.innerHTML = `
                    <svg class="btn-icon" style="animation: spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Please wait...</span>
                `;
                submitBtn.classList.add('loading');
                
                // Add keyframes for spin if not exists
                if (!document.getElementById('spin-keyframes')) {
                    const style = document.createElement('style');
                    style.id = 'spin-keyframes';
                    style.innerHTML = `
                        @keyframes spin {
                            from { transform: rotate(0deg); }
                            to { transform: rotate(360deg); }
                        }
                    `;
                    document.head.appendChild(style);
                }
            }
        });
    });
});
