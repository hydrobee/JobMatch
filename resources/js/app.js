import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('themeToggle', () => ({
    dark: document.documentElement.classList.contains('dark'),

    toggle() {
        this.dark = ! this.dark;
        document.documentElement.classList.toggle('dark', this.dark);

        try {
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        } catch (e) {
            // Storage can be blocked; the toggle still works for this page view.
        }
    },
}));

Alpine.start();
