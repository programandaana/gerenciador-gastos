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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json', // Expect JSON response
                    }
                });

                // The loading toast should be hidden regardless of success or failure
                if (loadingToast) {
                    loadingToast.hide();
                }

                const data = await response.json();

                if (response.ok) {
                    if (data.success && data.job_status_uuid) {
                        showToast(data.success, 'success');
                        // Optionally clear the file input
                        const fileInput = uploadForm.querySelector('input[type="file"]');
                        if (fileInput) fileInput.value = '';

                        // Start polling for job status
                        startPollingForJobStatus(data.job_status_uuid);
                    } else if (data.success) { // Handle success without job_status_uuid
                        showToast(data.success, 'success');
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
                // Submit button is re-enabled once the initial response is received
                // The final status will be shown via polling.
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Enviar';
                }
            }
        });
    }

    // Function to start polling for job status
    function startPollingForJobStatus(jobStatusUuid) {
        const pollInterval = 3000; // Poll every 3 seconds
        let pollingAttempts = 0;
        const maxPollingAttempts = 60; // Max 60 attempts = 3 minutes

        const intervalId = setInterval(async () => {
            pollingAttempts++;

            if (pollingAttempts > maxPollingAttempts) {
                clearInterval(intervalId);
                showToast('O processamento do arquivo demorou muito e a verificação de status foi interrompida. Por favor, verifique mais tarde.', 'warning');
                return;
            }

            try {
                const response = await fetch(`/job-status/${jobStatusUuid}`);
                const jobStatus = await response.json();

                if (response.ok && jobStatus && jobStatus.status) {
                    if (jobStatus.status === 'completed') {
                        clearInterval(intervalId);
                        showToast(jobStatus.message, 'success');
                    } else if (jobStatus.status === 'failed') {
                        clearInterval(intervalId);
                        showToast(jobStatus.message, 'error');
                    } else {
                        // Job is still pending or processing, continue polling
                        // Optionally update a "processing" toast here if desired
                        // showToast(`Status: ${jobStatus.status}...`, 'info'); // Could be noisy
                    }
                } else {
                    // Handle API errors for job status
                    clearInterval(intervalId);
                    showToast('Erro ao verificar o status do processamento.', 'error');
                }
            } catch (error) {
                clearInterval(intervalId);
                console.error('Erro ao buscar status do job:', error);
                showToast('Erro de rede ao verificar o status do processamento.', 'error');
            }
        }, pollInterval);
    }

    const checkStatusBtn = document.getElementById('checkStatusBtn');
    const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    const statusModalBody = document.getElementById('statusModalBody');
    const statusCount = document.getElementById('statusCount');

    function updateStatusCount(statuses) {
        const totalCount = statuses.length;
        if (totalCount > 0) {
            statusCount.textContent = totalCount;
            statusCount.style.display = '';
        } else {
            statusCount.style.display = 'none';
        }
    }


    document.getElementById('statusModal').addEventListener('hidden.bs.modal', async () => {
        try {
            fetchJobStatuses();
        } catch (error) {
            console.error('Erro ao marcar notificações como lidas ao fechar o modal:', error);
        }
    });

    async function fetchJobStatuses() {
        try {
            const response = await fetch('/job-status');
            const statuses = await response.json();
            updateStatusModal(statuses);
            updateStatusCount(statuses);
        } catch (error) {
            console.error('Erro ao buscar status:', error);
        }
    }

    function updateStatusModal(statuses) {
        statusModalBody.innerHTML = '';

        if (statuses.length === 0) {
            statusModalBody.innerHTML = '<p>Nenhum processamento recente.</p>';
            return;
        }

        const statusTranslations = {
            'failed': 'Falha',
            'completed': 'Concluído',
            'processing': 'Processando',
            'pending': 'Pendente'
        };

        statuses.forEach(status => {
            const translatedStatus = statusTranslations[status.status] || status.status; // Usa a tradução ou o original se não encontrar

            const statusElement = document.createElement('div');
            statusElement.classList.add('alert', 'd-flex', 'justify-content-between', 'align-items-center'); // Added flex classes
            statusElement.setAttribute('data-uuid', status.uuid); // Add uuid to element for easier targeting

            let statusClass = 'alert-info';
            if (status.status === 'completed') statusClass = 'alert-success';
            if (status.status === 'failed') statusClass = 'alert-danger';
            statusElement.classList.add(statusClass);
            statusElement.innerHTML = `
                <div>
                    <p class="mb-0"><strong>Status:</strong> ${translatedStatus}</p>
                    <p class="mb-0">${status.message}</p>
                    <small class="text-muted">${new Date(status.created_at).toLocaleString()}</small>
                </div>
                ${(status.status === 'completed' || status.status === 'failed') ? `<button type="button" class="btn btn-lg confirm-read-btn" data-uuid="${status.uuid}">
                    <i class="bi bi-check2-square"></i>
                </button>` : ''}
            `;
            statusModalBody.appendChild(statusElement);
        });
    }

    // Nova função para confirmar leitura e remover
    async function deleteStatus(uuid) {
        try {
            const response = await fetch(`/job-status/${uuid}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                showToast(`Erro ao confirmar leitura: ${errorData.message || 'Erro desconhecido'}`, 'error');
                return false; // Indicate failure
            }
            return true; // Indicate success
        } catch (error) {
            console.error(`Erro ao confirmar leitura e remover status ${uuid}:`, error);
            showToast('Erro de rede ao confirmar leitura.', 'error');
            return false; // Indicate failure
        }
    }

    async function confirmReadStatusAndRemove(uuid) {
        if (confirm('Tem certeza que deseja confirmar a leitura desta notificação?')) {
            const success = await deleteStatus(uuid);
            if (success) {
                showToast('Leitura confirmada com sucesso!', 'success');
                fetchJobStatuses(); // Atualiza a lista após a exclusão
            }
        }
    }

    // Listener para o clique no botão de confirmar leitura
    statusModalBody.addEventListener('click', (event) => {
        const target = event.target.closest('.confirm-read-btn');
        if (target) {
            const uuid = target.dataset.uuid;
            if (uuid) {
                confirmReadStatusAndRemove(uuid);
            }
        }
    });


    if (checkStatusBtn) {
        checkStatusBtn.addEventListener('click', () => {
            fetchJobStatuses();
            statusModal.show();
        });
    }

    const readAllNotificationsBtn = document.getElementById('readAllNotificationsBtn');
    if (readAllNotificationsBtn) {
        readAllNotificationsBtn.addEventListener('click', async () => {
            if (!confirm('Tem certeza que deseja marcar todas as notificações como lidas?')) {
                return;
            }

            try {
                const response = await fetch('/job-status');
                const statuses = await response.json();

                const completedOrFailedStatuses = statuses.filter(s =>
                    s.status === 'completed' || s.status === 'failed'
                );

                for (const status of completedOrFailedStatuses) {
                    await deleteStatus(status.uuid);
                }
                
                showToast('Todas as notificações foram marcadas como lidas.', 'success');
                fetchJobStatuses();

            } catch (error) {
                console.error('Erro ao marcar todas as notificações como lidas:', error);
                showToast('Erro ao marcar todas as notificações como lidas.', 'error');
            }
        });
    }

    // Periodically check for new statuses
    setInterval(fetchJobStatuses, 10000); // Check every 10 seconds
    // Initial check
    fetchJobStatuses();
});