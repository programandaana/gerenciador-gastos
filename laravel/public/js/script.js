document.addEventListener('DOMContentLoaded', () => {
    const themeToggleButton = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const currentTheme = localStorage.getItem('theme') || 'light';

    // Aplica o tema salvo ao carregar a página
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-mode');
        if(themeIcon) themeIcon.textContent = '🌙';
    } else {
        if(themeIcon) themeIcon.textContent = '☀️';
    }

    if(themeToggleButton) {
        themeToggleButton.addEventListener('click', () => {
            // Alterna a classe no body
            document.body.classList.toggle('dark-mode');

            // Verifica qual tema está ativo e salva no localStorage
            let theme = 'light';
            if (document.body.classList.contains('dark-mode')) {
                theme = 'dark';
                if(themeIcon) themeIcon.textContent = '🌙'; // Lua
            } else {
                if(themeIcon) themeIcon.textContent = '☀️'; // Sol
            }
            localStorage.setItem('theme', theme);
        });
    }

    // Function to display Bootstrap toasts
    function showToast(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Toast container not found!');
            return;
        }

        const toastElement = document.createElement('div');
        toastElement.classList.add('toast', 'align-items-center', 'border-0');
        toastElement.setAttribute('role', 'alert');
        toastElement.setAttribute('aria-live', 'assertive');
        toastElement.setAttribute('aria-atomic', 'true');
        toastElement.style.color = 'white'; // Default text color

        let bgColorClass = '';
        let icon = '';

        switch (type) {
            case 'success':
                bgColorClass = 'bg-success';
                icon = '✅';
                break;
            case 'error':
                bgColorClass = 'bg-danger';
                icon = '❌';
                break;
            case 'warning':
                bgColorClass = 'bg-warning';
                icon = '⚠️';
                break;
            case 'info':
                bgColorClass = 'bg-info';
                icon = 'ℹ️';
                break;
            case 'loading':
                bgColorClass = 'bg-primary';
                icon = '⏳';
                break;
            default:
                bgColorClass = 'bg-secondary';
                break;
        }

        toastElement.classList.add(bgColorClass);

        toastElement.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${icon} ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        toastContainer.appendChild(toastElement);

        const bsToast = new bootstrap.Toast(toastElement);
        bsToast.show();

        // If it's a loading toast, we might want to return the instance to hide it later
        if (type === 'loading') {
            return bsToast;
        }
    }

    const uploadForm = document.querySelector('form[data-receipt-upload-url]');
    if (uploadForm) {
        const actionUrl = uploadForm.dataset.receiptUploadUrl;
        const submitButton = uploadForm.querySelector('button[type="submit"]');

        uploadForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Enviando...';
            }

            const loadingToast = showToast('Arquivo enviado para processamento...', 'loading');

            const formData = new FormData(uploadForm);

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json', // Expect JSON response
                    }
                });

                // The loading toast should be hidden regardless of success or failure
                if (loadingToast) {
                    loadingToast.hide();
                }

                const data = await response.json();

                if (response.ok) {
                    if (data.success) {
                        showToast(data.success, 'success');
                        // Optionally clear the file input
                        const fileInput = uploadForm.querySelector('input[type="file"]');
                        if (fileInput) fileInput.value = '';
                    } else if (data.error) {
                        showToast(data.error, 'error');
                    } else {
                        showToast('Resposta do servidor inesperada.', 'warning');
                    }
                } else {
                    // Handle HTTP errors (e.g., 400, 500)
                    if (data.errors) {
                        // Laravel validation errors
                        const errorMessages = Object.values(data.errors).flat().join('<br>');
                        showToast(`Falha na validação:<br>${errorMessages}`, 'error');
                    } else if (data.message) {
                        showToast(`Erro: ${data.message}`, 'error');
                    } else {
                        showToast('Ocorreu um erro no servidor.', 'error');
                    }
                }
            } catch (error) {
                if (loadingToast) {
                    loadingToast.hide(); // Hide loading toast on network error
                }
                console.error('Erro ao enviar o arquivo:', error);
                showToast('Erro de rede ou ao enviar o arquivo.', 'error');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Enviar';
                }
            }
        });
    }
});
