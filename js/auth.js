let CSRF_TOKEN = "";

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("loginForm");

  // IMPORTANT: silently exit if not on login page
  if (!form) return;

  form.addEventListener("submit", e => {
    e.preventDefault();
    login();
  });
});

// --------------------
// LOGIN
// --------------------
function login() {
  const usernameInput = document.getElementById("username");
  const passwordInput = document.getElementById("userPassword");
  const btn           = document.getElementById("loginBtn");
  const errDiv        = document.getElementById("loginError");

  if (!usernameInput || !passwordInput) return;

  errDiv.classList.add("d-none");
  errDiv.textContent = "";
  btn.disabled  = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Signing in…';

  function showError(msg) {
    errDiv.textContent = msg;
    errDiv.classList.remove("d-none");
    btn.disabled  = false;
    btn.innerHTML = "Sign In";
  }

  fetch("login_handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      username:    usernameInput.value,
      password:    passwordInput.value,
      remember_me: document.getElementById("rememberMe").checked ? "1" : ""
    })
  })
  .then(r => r.text())
  .then(text => {
    let res;
    try { res = JSON.parse(text); }
    catch { showError("Server error. Please try again."); return; }

    if (!res.success) { showError(res.message); return; }
    location.href = "index.php";
  })
  .catch(() => showError("Could not reach the server. Please try again."));
}


// --------------------
// LOGOUT
// --------------------

function logout() {
  fetch("api/auth/logout.php", { method: "POST" })
    .then(() => {
      window.location.href = "login.php";
    })
    .catch(err => console.error("Logout failed:", err));
}

function init_logout_buttons() {
  document.querySelectorAll(".logout-btn").forEach(btn => {
    if (btn.dataset.bound) return; // prevent double binding
    btn.dataset.bound = "1";

    btn.addEventListener("click", e => {
      e.preventDefault();
      logout();
    });
  });
}

