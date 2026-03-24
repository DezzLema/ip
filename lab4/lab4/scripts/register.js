document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("register-form");
    const username = document.getElementById("username");
    const email = document.getElementById("email");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");
    const fullName = document.getElementById("full_name");
    
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
    
    // Валидация username
    username.addEventListener("input", function() {
        const value = username.value.trim();
        if (!value) {
            showError(username, "Введите username");
        } else if (value.length < 3) {
            showError(username, "Username слишком короткий");
        } else if (value.length > 50) {
            showError(username, "Username слишком длинный");
        } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
            showError(username, "Не используйте запрещенные символы");
        } else {
            clearError(username);
        }
    });
    
    // Валидация email
    email.addEventListener("input", function() {
        const value = email.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!value) {
            showError(email, "Введите почту");
        } else if (!emailRegex.test(value)) {
            showError(email, "Пожалуйста, введите действительный адрес электронной почты.");
        } else if (value.length > 100) {
            showError(email, "В почте не должно быть более 100 символов.");
        } else {
            clearError(email);
        }
    });
    
    // Валидация пароля
    password.addEventListener("input", function() {
        const value = password.value;
        
        if (!value) {
            showError(password, "Введите пароль");
        } else if (value.length < 8) {
            showError(password, "Пароль должен состоять как минимум из 8 символов.");
        } else if (value.length > 100) {
            showError(password, "Пароль не должен превышать 100 символов.");
        } else if (!/[A-Z]/.test(value)) {
            showError(password, "Должно содержать как минимум одну заглавную букву.");
        } else if (!/[a-z]/.test(value)) {
            showError(password, "Должно содержать как минимум одну строчную букву.");
        } else if (!/[0-9]/.test(value)) {
            showError(password, "Должно содержать как минимум одно число.");
        } else {
            clearError(password);
        }
        
        // Проверка подтверждения пароля, если оно уже введено
        if (confirmPassword.value) {
            if (value !== confirmPassword.value) {
                showError(confirmPassword, "Пароль не совпадает");
            } else {
                clearError(confirmPassword);
            }
        }
    });
    
    // Валидация подтверждения пароля
    confirmPassword.addEventListener("input", function() {
        if (!confirmPassword.value) {
            showError(confirmPassword, "Подтвердите пароль");
        } else if (password.value !== confirmPassword.value) {
            showError(confirmPassword, "Пароль не совпадает");
        } else {
            clearError(confirmPassword);
        }
    });
    
    // Валидация полного имени
    fullName.addEventListener("input", function() {
        const value = fullName.value.trim();
        if (value.length > 100) {
            showError(fullName, "Полное имя не должно превышать 100 символов.");
        } else {
            clearError(fullName);
        }
    });
    
    // Финальная проверка перед отправкой
    form.addEventListener("submit", function(e) {
        let hasErrors = false;
        
        // Проверяем все поля
        const inputs = [username, email, password, confirmPassword];
        
        inputs.forEach(input => {
            // Триггерим событие input для запуска валидации
            const event = new Event("input", { bubbles: true });
            input.dispatchEvent(event);
            
            // Проверяем, есть ли ошибка
            if (input.classList.contains("error")) {
                hasErrors = true;
            }
        });
        
        // Дополнительные проверки для пустых полей
        if (!username.value.trim()) {
            showError(username, "Username обязателен");
            hasErrors = true;
        }
        
        if (!email.value.trim()) {
            showError(email, "Email обязателен");
            hasErrors = true;
        }
        
        if (!password.value) {
            showError(password, "Password обязателен");
            hasErrors = true;
        }
        
        if (!confirmPassword.value) {
            showError(confirmPassword, "Подтвердите пароль");
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
        submitBtn.innerHTML = 'Регистрация... <span class="btn-icon">---</span>';
        submitBtn.disabled = true;
        
        // Автоматическое восстановление кнопки через 10 секунд (на случай ошибки)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 10000);
    });
});