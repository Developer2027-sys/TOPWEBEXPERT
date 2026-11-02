    </main>

    <!-- Footer -->
    <footer class="px-6 py-5 flex flex-col sm:flex-row justify-between gap-2 text-[11px] font-semibold text-neutral-400 uppercase tracking-[0.16em]">
      <span>&copy; <?= date('Y') ?> TopWebExpert &mdash; Admin Panel</span>
      <span class="inline-flex items-center gap-1.5">Secured with 2FA <span class="w-1.5 h-1.5 rounded-full bg-accent inline-block"></span></span>
    </footer>
  </div>
</div>

<script>
  var st = document.getElementById('sidebar-toggle');
  var sb = document.getElementById('admin-sidebar');
  var bd = document.getElementById('sidebar-backdrop');
  function openSidebar() { sb.classList.remove('-translate-x-full'); if (bd) bd.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
  function closeSidebar() { sb.classList.add('-translate-x-full'); if (bd) bd.classList.add('hidden'); document.body.style.overflow = ''; }
  if (st) st.addEventListener('click', function () {
    sb.classList.contains('-translate-x-full') ? openSidebar() : closeSidebar();
  });
  if (bd) bd.addEventListener('click', closeSidebar);
  // Close when a nav link is tapped (mobile) and on resize to desktop
  sb.querySelectorAll('nav a').forEach(function (a) {
    a.addEventListener('click', function () { if (window.innerWidth < 1024) closeSidebar(); });
  });
  window.addEventListener('resize', function () { if (window.innerWidth >= 1024) { sb.classList.remove('-translate-x-full'); if (bd) bd.classList.add('hidden'); document.body.style.overflow = ''; } });
</script>
</body>
</html>
