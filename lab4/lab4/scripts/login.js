document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("login-form");
    const username = document.getElementById("username");
    const password = document.getElementById("password");
    
    // Функция для показа ошибки
    function showError(input, message) {
        const formGroup = input.closest(".form-group");
        let errorDiv = formGroup.querySelector(".error-text");
        
        if (!errorDiv) {
            errorDiv = document.createElement("div");
            errorDiv.className = "error-text";
            errorDiv.style.color = "#ff6b6b";
            errorDiv.style.fontSize = "14px";
            errorDiv.style.marginTop = "5px";
            formGroup.appendChild(errorDiv);
        }
        
        errorDiv.textContent = message;
        input.classList.add("error");
    }
    
    // Функция для очистки ошибки
    function clearError(input) {
        const formGroup = input.closest(".form-group");
        const errorDiv = formGroup.querySelector(".error-text");
        
        if (errorDiv) {
            errorDiv.remove();
        }
        
        input.classList.remove("error");
    }
    
    // Валидация username/email
    username.addEventListener("input", function() {
        const value = username.value.trim();
        if (!value) {
            showError(username, "Username or email is required");
        } else if (value.length > 100) {
            showError(username, "Must not exceed 100 characters");
        } else {
            clearError(username);
        }
    });
    
    // Валидация пароля
    password.addEventListener("input", function() {
        const value = password.value;
        if (!value) {
            showError(password, "Password is required");
        } else if (value.length > 100) {
            showError(password, "Password must not exceed 100 characters");
        } else {
            clearError(password);
        }
    });
    
    // Финальная проверка перед отправкой
    form.addEventListener("submit", function(e) {
        let hasErrors = false;
        
        if (!username.value.trim()) {
            showError(username, "Username or email is required");
            hasErrors = true;
        }
        
        if (!password.value) {
            showError(password, "Password is required");
            hasErrors = true;
        }
        
        if (hasErrors) {
            e.preventDefault();
            
            // Прокрутка к первой ошибке
            const firstError = form.querySelector(".error");
            if (firstError) {
                firstError.scrollIntoView({ 
                    behavior: "smooth", 
                    block: "center" 
                });
                firstError.focus();
            }
            
            return false;
        }
        
        // Показываем индикатор загрузки
        const submitBtn = form.querySelector(".send-btn");
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Logging in... <span class="btn-icon">---</span>';
        submitBtn.disabled = true;
        
        // Автоматическое восстановление кнопки через 10 секунд (на случай ошибки)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 10000);
    });
});