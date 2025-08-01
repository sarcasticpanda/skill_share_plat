        </div><!-- .container -->
    </div><!-- .main-content -->
    
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> SkillShare. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Add 'active' class to current page in navigation
        document.addEventListener('DOMContentLoaded', function() {
            // Get current page URL
            const currentPage = window.location.pathname;
            
            // Get all navigation links
            const navLinks = document.querySelectorAll('.main-nav a');
            
            // Loop through nav links and set active class
            navLinks.forEach(link => {
                const linkPath = link.getAttribute('href');
                if (currentPage.includes(linkPath)) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>