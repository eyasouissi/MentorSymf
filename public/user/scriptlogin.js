
    document.addEventListener("DOMContentLoaded", function () {
        const container = document.getElementById('container');
        const registerBtn = document.getElementById('register');
        const loginBtn = document.getElementById('login');

        if (registerBtn && loginBtn && container) {
            registerBtn.addEventListener('click', () => {
                container.classList.add("right-panel-active"); // Unification des classes
            });

            loginBtn.addEventListener('click', () => {
                container.classList.remove("right-panel-active");
            });
        }
    });

