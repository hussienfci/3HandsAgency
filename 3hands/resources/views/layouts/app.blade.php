<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel App')</title>    <!-- Use CDN for immediate functionality -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        .nav-link { display: inline-flex; align-items: center; padding: 0.25rem 0.25rem 0; border-bottom: 2px solid transparent; font-size: 0.875rem; font-weight: 500; transition: all 0.2s; color: #6b7280; }
        .nav-link:hover { border-color: #d1d5db; color: #374151; }
        .nav-link.active { border-color: #6366f1; color: #111827; }
        .dark .nav-link { color: #9ca3af; }
        .dark .nav-link:hover { color: #d1d5db; }
        .dark .nav-link.active { border-color: #818cf8; color: white; }
        .hidden { display: none !important; }
        #toast { position: fixed; top: 1rem; right: 1rem; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); transform: translateX(100%); transition: transform 0.3s; z-index: 50; display: none; }
        #toast.show { transform: translateX(0); display: flex; }
        .toast-success { background: #10b981; color: white; }
        .toast-error { background: #ef4444; color: white; }
        .toast-warning { background: #f59e0b; color: black; }
        .toast-info { background: #3b82f6; color: white; }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800 dark:text-white">
                            3Hands
                        </a>
                    </div>
                    <div class="flex md:flex ml-6 xs:ml-0.5 md:space-x-8 xs:space-x-0">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            Home
                        </a>
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            Users
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <select id="language-select" class="text-sm bg-transparent border-none text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-0 cursor-pointer">
                        <option value="en">English</option>
                        <option value="es">Español</option>
                        <option value="fr">Français</option>
                    </select>

                    <button id="theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/>
                        </svg>
                        <svg class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Toast Notification -->
    <div id="toast" class="hidden">
        <div class="flex items-center space-x-2">
            <span id="toast-message"></span>
            <button id="toast-close" class="text-lg">&times;</button>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Utility functions
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const messageEl = document.getElementById('toast-message');
            
            if (toast && messageEl) {
                toast.className = 'toast-' + type + ' show';
                messageEl.textContent = message;
                
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 5000);
            } else {
                alert(message);
            }
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.classList.remove('show');
            }
        }

        // Theme management
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const themeToggle = document.getElementById('theme-toggle');
            if (!themeToggle) return;
            
            const icons = themeToggle.querySelectorAll('svg');
            const isDark = document.documentElement.classList.contains('dark');
            
            if (icons.length >= 2) {
                icons[0].classList.toggle('hidden', isDark);
                icons[1].classList.toggle('hidden', !isDark);
            }
        }

        // Initialize theme
        document.addEventListener('DOMContentLoaded', function() {
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }
            updateThemeIcon();
            
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', toggleTheme);
            }

            const toastClose = document.getElementById('toast-close');
            if (toastClose) {
                toastClose.addEventListener('click', hideToast);
            }

            const languageSelect = document.getElementById('language-select');
            if (languageSelect) {
                languageSelect.addEventListener('change', function() {
                    showToast(`Language changed to ${this.options[this.selectedIndex].text}`, 'info');
                });
            }
        });

        // Make functions global
        window.toggleTheme = toggleTheme;
        window.showToast = showToast;
        window.hideToast = hideToast;
    </script>
 <!-- class="text-lg">&times;</button> -->
        </div>
    </div>

    <!-- Include the JavaScript file directly -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>