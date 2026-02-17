document.addEventListener("DOMContentLoaded", () => {

  const btn = document.getElementById("btn");
  const msg = document.getElementById("msg");
  const username = document.getElementById("username");
  const password = document.getElementById("password");
  const tahun = document.getElementById("tahun");

  if (!btn || !username || !password) {
    console.warn("Login elements not found, script skipped");
    return;
  }

  let confirmCallback = null;

  /* ===============================
     LOGIN FLOW
  ================================ */
  function login() {
    const u = username.value.trim();
    const p = password.value.trim();
    const t = tahun ? tahun.value : null;

    if (!u || !p) {
      toast("⚠️ Username dan password wajib diisi", "error");
      return;
    }

    openConfirm(
      "Konfirmasi Login",
      "Apakah Anda yakin ingin masuk ke sistem?",
      function () {
        processLogin(u, p, t);
      }
    );
  }

  function processLogin(u, p, t) {
    btn.disabled = true;
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<span>Memproses...</span><i class="ph ph-circle-notch animate-spin"></i>';
    msg.textContent = "";

    fetch("/login", {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute("content")
      },
      body: JSON.stringify({
        username: u,
        password: p,
        tahun: t
      })
    })
      .then(async res => {
        const text = await res.text();

        let data;
        try {
          data = JSON.parse(text);
        } catch {
          console.error("❌ BUKAN JSON:", text);
          throw new Error("Server error, bukan JSON");
        }

        if (!res.ok || !data.success) {
          throw new Error(data.message || "Login gagal");
        }

        // ✅ LOGIN SUKSES
        toast("Login berhasil! Mengalihkan...", "success");
        setTimeout(() => {
          window.location.href = "/dashboard";
        }, 1000);
      })
      .catch(err => {
        toast("❌ " + err.message, "error");
        btn.disabled = false;
        btn.innerHTML = originalContent;
        password.value = "";
      });
  }

  /* ===============================
     CONFIRM MODAL
  ================================ */
  function openConfirm(title, message, onOk) {
    confirmCallback = onOk;

    const titleEl = document.getElementById("confirmTitle");
    const msgEl = document.getElementById("confirmMessage");
    const modal = document.getElementById("confirmModal");

    if (!titleEl || !msgEl || !modal) return;

    titleEl.innerText = title;
    msgEl.innerText = message;

    modal.classList.add("show");
  }

  function closeConfirm() {
    const modal = document.getElementById("confirmModal");
    if (!modal) return;

    modal.classList.remove("show");
    confirmCallback = null;
  }

  function handleConfirmOk() {
    if (confirmCallback) confirmCallback();
    closeConfirm();
  }

  /* ===============================
     EVENT
  ================================ */
  btn.addEventListener("click", login);

  password.addEventListener("keyup", e => {
    if (e.key === "Enter") login();
  });

  // Toggle Password Visibility
  const btnTogglePass = document.getElementById("btn-toggle-pass");
  const eyeIcon = document.getElementById("eye-icon");

  if (btnTogglePass && eyeIcon) {
    btnTogglePass.addEventListener("click", () => {
      const isPassword = password.type === "password";
      password.type = isPassword ? "text" : "password";

      // Update icon
      if (isPassword) {
        eyeIcon.classList.remove("ph-eye");
        eyeIcon.classList.add("ph-eye-slash");
      } else {
        eyeIcon.classList.remove("ph-eye-slash");
        eyeIcon.classList.add("ph-eye");
      }
    });
  }

  // expose untuk modal
  window.closeConfirm = closeConfirm;
  window.handleConfirmOk = handleConfirmOk;

});
