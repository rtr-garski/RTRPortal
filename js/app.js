document.addEventListener("DOMContentLoaded", () => {

  const content = document.getElementById("content");

  function loadPage(page) {
    fetch(`pages/${page}.php`)
      .then(r => {
        if (r.status === 401) {
          window.location.href = "login.php";
          return;
        }
        return r.text();
      })
      .then(html => {
        if (!html) return;

        // 1. Inject page HTML
        // stop page-specific behaviors
        window.stopLockPolling?.();

        content.innerHTML = html;

        // 2. Run page-specific init (email-compose, dashboard, etc.)
        window[`init_${page.replace(/-/g, "_")}`]?.();

        // 3. Bind ALL logout buttons (VERY IMPORTANT)
        window.init_logout_buttons?.();

        // 4. Update menu state
        setActiveMenu(page);
      })
      .catch(err => console.error("Load error:", err));
  }

  function setActiveMenu(page) {
    // 1. Remove all active states
    document.querySelectorAll(".side-nav-link").forEach(link => {
      link.classList.remove("active");
    });

    // 2. Find active page link
    const activeLink = document.querySelector(
      `.side-nav-link[data-page="${page}"]`
    );
    if (!activeLink) return;

    activeLink.classList.add("active");

    // 3. Check if inside a collapse (Email submenu)
    const activeCollapse = activeLink.closest(".collapse");

    // 4. Close unrelated collapses
    document.querySelectorAll(".collapse.show").forEach(collapse => {
      if (collapse !== activeCollapse) {
        bootstrap.Collapse.getOrCreateInstance(collapse).hide();
      }
    });

    // 5. If inside Main Column → open collapse & highlight parent
    if (activeCollapse) {
      bootstrap.Collapse.getOrCreateInstance(activeCollapse).show();

      const parentToggle = document.querySelector(
        `[href="#${activeCollapse.id}"]`
      );

      parentToggle?.classList.add("active");
    }
  }

  document.addEventListener("click", e => {
    const link = e.target.closest(".side-nav-link[data-page]");
    if (!link) return;

    e.preventDefault();
    loadPage(link.dataset.page);
  });

  window.loadPage = loadPage;
  loadPage("dashboard");

});

