document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("loginForm");
    const email = document.getElementById("email");
    const password = document.getElementById("password");
    const errorMsg = document.getElementById("error-message");

    function showError(message) {
        errorMsg.textContent = message;
        errorMsg.style.display = "block";    // Ensure it's visible
        errorMsg.classList.add("show");      // Trigger fade-in
    }

    function clearError() {
        errorMsg.textContent = "";
        errorMsg.style.display = "none";
        errorMsg.classList.remove("show");
    }

    form.addEventListener("submit", (e) => {
        clearError(); // Clear any old error first

        // ✅ Check email
        if (!email.value.trim()) {
            e.preventDefault();
            showError("Please enter your email.");
            return;
        }

        if (!/\S+@\S+\.\S+/.test(email.value.trim())) {
            e.preventDefault();
            showError("Please enter a valid email address.");
            return;
        }

        // ✅ Check password
        if (!password.value.trim()) {
            e.preventDefault();
            showError("Please enter your password.");
            return;
        }

        if (password.value.length < 4) {
            e.preventDefault();
            showError("Password must be at least 4 characters.");
            return;
        }
    });

    // ✅ Clear error when user starts typing
    email.addEventListener("input", clearError);
    password.addEventListener("input", clearError);
});
