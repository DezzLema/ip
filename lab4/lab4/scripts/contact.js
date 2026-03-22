document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("contact-form");
    const nameInput = document.getElementById("name");
    const emailInput = document.getElementById("email");
    const messageInput = document.getElementById("message");
    const charCount = document.getElementById("char-count");
    const honeypot = document.getElementById("honeypot");
    
    // Счетчик символов для сообщения
    function updateCharCount() {
        const length = messageInput.value.length;
        charCount.textContent = length;
        
        if (length > 1900) {
            charCount.style.color = "#ff6b6b";
        } else if (length > 1800) {
            charCount.style.color = "#ffc107";
        } else {
            charCount.style.color = "#aaa";
        }
    }
    
    // Инициализация счетчика
    updateCharCount();
    messageInput.addEventListener("input", updateCharCount);
    
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
    
    // Валидация имени
    nameInput.addEventListener("input", function() {
        const value = nameInput.value.trim();
        
        if (!value) {
            showError(nameInput, "Name is required");
        } else if (value.length < 2) {
            showError(nameInput, "Name must be at least 2 characters");
        } else if (value.length > 100) {
            showError(nameInput, "Name must not exceed 100 characters");
        } else if (!/^[a-zA-Z\s\-\.']+$/.test(value)) {
            showError(nameInput, "Only letters, spaces, hyphens, dots and apostrophes allowed");
        } else {
            clearError(nameInput);
        }
    });
    
    // Валидация email
    emailInput.addEventListener("input", function() {
        const value = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!value) {
            showError(emailInput, "Email is required");
        } else if (!emailRegex.test(value)) {
            showError(emailInput, "Please enter a valid email");
        } else if (value.length > 100) {
            showError(emailInput, "Email must not exceed 100 characters");
        } else {
            clearError(emailInput);
        }
    });
    
    // Валидация сообщения
    messageInput.addEventListener("input", function() {
        const value = messageInput.value.trim();
        
        if (!value) {
            showError(messageInput, "Message is required");
        } else if (value.length < 10) {
            showError(messageInput, "Message must be at least 10 characters");
        } else if (value.length > 2000) {
            showError(messageInput, "Message must not exceed 2000 characters");
        } else {
            // Проверка на спам
            const spamWords = ["http://", "https://", "www.", ".com", "buy now", "click here", "viagra", "casino"];
            const lowerValue = value.toLowerCase();
            
            for (let word of spamWords) {
                if (lowerValue.includes(word)) {
                    showError(messageInput, "Message contains suspicious content");
                    return;
                }
            }
            
            clearError(messageInput);
        }
    });
    
    // Проверка honeypot поля
    honeypot.addEventListener("input", function() {
        if (honeypot.value) {
            // блокируем отправку
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                alert("Submission blocked - suspicious activity detected");
                return false;
            }, { once: true });
        }
    });
    
    // Финальная проверка перед отправкой
    form.addEventListener("submit", function(e) {
        let hasErrors = false;
        
        // Проверяем honeypot
        if (honeypot.value) {
            e.preventDefault();
            alert("Submission blocked - suspicious activity detected");
            return false;
        }
        
        // Проверяем все поля
        const inputs = [nameInput, emailInput, messageInput];
        
        inputs.forEach(input => {
            // Триггерим событие input для запуска валидации
            const event = new Event("input", { bubbles: true });
            input.dispatchEvent(event);
            
            // Проверяем, есть ли ошибка
            if (input.classList.contains("error")) {
                hasErrors = true;
            }
        });
        
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
        submitBtn.innerHTML = 'Sending... <span class="btn-icon">---</span>';
        submitBtn.disabled = true;
        
        // Автоматическое восстановление кнопки через 10 секунд (на случай ошибки)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 10000);
    });
});