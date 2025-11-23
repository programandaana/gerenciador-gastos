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
});
