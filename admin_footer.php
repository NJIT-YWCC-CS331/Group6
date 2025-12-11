<hr>
<footer class="bg-primary">
    <p>Online Fancy Book Store - Admin Panel</p>
</footer>

<!-- Theme toggle script: toggles .theme-dark on <body> and persists choice -->
<script>
  (function(){
    const body = document.body;
    const key = 'site-theme';
    if (localStorage.getItem(key) === 'dark') body.classList.add('theme-dark');
    window.toggleTheme = function(){
      const isDark = body.classList.toggle('theme-dark');
      localStorage.setItem(key, isDark ? 'dark' : 'light');
    };
  })();
</script>

</body>
</html>
