document.addEventListener("DOMContentLoaded", () => {

  const content = document.getElementById("content");

  function loadPage(page) {
    content.innerHTML =
      '<div class="d-flex justify-content-center align-items-center" style="min-height:200px">' +
      '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>' +
      '</div>';

    fetch(`pages/${page}.php`)
      .then(r => {
        if (r.status === 401) {
          window.location.href = "logout.php";
          return;
        }
        return r.text();
      })
      .then(html => {
        if (!html) return;

        // stop page-specific behaviors
        window.stopLockPolling?.();

        content.innerHTML = html;

        // Run page-specific init
        window[`init_${page.replace(/-/g, "_")}`]?.();

        // Bind ALL logout buttons
        window.init_logout_buttons?.();

        // Update menu state
        setActiveMenu(page);
      })
      .catch(err => console.error("Load error:", err));
  }

  function setActiveMenu(page) {
    document.querySelectorAll(".side-nav-item.active").forEach(li => {
      li.classList.remove("active");
    });

    const activeLink = document.querySelector(
      `.side-nav-link[data-page="${page}"]`
    );
    if (!activeLink) return;

    activeLink.closest(".side-nav-item")?.classList.add("active");

    const activeCollapse = activeLink.closest(".collapse");

    document.querySelectorAll(".collapse.show").forEach(collapse => {
      if (collapse !== activeCollapse) {
        bootstrap.Collapse.getOrCreateInstance(collapse).hide();
      }
    });

    if (activeCollapse) {
      bootstrap.Collapse.getOrCreateInstance(activeCollapse).show();
      activeCollapse.closest(".side-nav-item")?.classList.add("active");
    }
  }

  document.addEventListener("click", e => {
    const link = e.target.closest(".nav-link[data-page]");
    if (!link) return;

    e.preventDefault();
    loadPage(link.dataset.page);
  });

  loadPage("dashboard");

});
