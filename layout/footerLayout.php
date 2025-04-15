
<script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('themeToggle');
            const icon = themeToggle.querySelector('.icon i');
            
            // Check for saved theme preference
            const darkMode = localStorage.getItem('darkMode') === 'true';
            const cookieDarkMode = document.cookie.split('; ').find(row => row.startsWith('darkMode='));
            
            // Apply theme based on preference
            if (darkMode || (cookieDarkMode && cookieDarkMode.split('=')[1] === 'true')) {
                document.body.classList.add('dark-mode');
                icon.classList.replace('fa-moon', 'fa-sun');
            }
            
            // Toggle theme
            themeToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                
                if (document.body.classList.contains('dark-mode')) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                    localStorage.setItem('darkMode', 'true');
                    document.cookie = "darkMode=true; path=/; max-age=31536000"; // 1 year
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                    localStorage.setItem('darkMode', 'false');
                    document.cookie = "darkMode=false; path=/; max-age=31536000";
                }
            });
            
            // Mobile menu toggle (existing code)
            const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
            if ($navbarBurgers.length > 0) {
                $navbarBurgers.forEach(el => {
                    el.addEventListener('click', () => {
                        const target = el.dataset.target;
                        const $target = document.getElementById(target);
                        el.classList.toggle('is-active');
                        $target.classList.toggle('is-active');
                    });
                });
            }
        });
    </script>
</body>
</html>