document.addEventListener("DOMContentLoaded", function () {
  const checkInterval = setInterval(() => {
    const phoneInput = document.querySelector("input[name='phone']");
    const form = phoneInput ? phoneInput.closest("form") : null;
    const submitButton = form.querySelector('input[type="submit"]');

    if (phoneInput && form && window.intlTelInput) {
      clearInterval(checkInterval);

      phoneInput.setAttribute("placeholder", "");

      const iti = intlTelInput(phoneInput, {
        initialCountry: "auto",
        formatOnDisplay: true,
        nationalMode: false,
        separateDialCode: true,
        autoPlaceholder: "polite",
        geoIpLookup: function (callback) {
          fetch("https://ipapi.co/json")
            .then(res => res.json())
            .then(data => callback(data.country_code))
            .catch(() => callback("au"));
        },
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
      });

      // Error creators
      const createErrorMsg = (message, className) => {
        const div = document.createElement("div");
        div.className = className;
        div.textContent = message;
        return div;
      };

      const phoneError = createErrorMsg("Please enter a valid phone number.", "phone-error-msg");
      const nameError = createErrorMsg("First name is required.", "form-error-msg");
      const emailError = createErrorMsg("Please enter a valid email address.", "form-error-msg");

      // Validation functions
      const validatePhone = () => {
        phoneInput.classList.remove("error");
        if (phoneError.parentNode) phoneError.remove();

        const phoneValue = phoneInput.value.trim();
        const phoneWrapper = phoneInput.closest('.hs-phone');
        const inputDigits = phoneInput.value.replace(/\D/g, '').length;

        if (!phoneValue) {
          phoneInput.classList.add("error");
          phoneError.textContent = "Phone number is required.";
          phoneWrapper.parentNode.insertBefore(phoneError, phoneWrapper.nextSibling);
          if (submitButton) submitButton.disabled = true;
          return false;
        }

        if (inputDigits < 7) {
          phoneInput.classList.add("error");
          phoneError.textContent = "Phone number is too short.";
          phoneWrapper.parentNode.insertBefore(phoneError, phoneWrapper.nextSibling);
          if (submitButton) submitButton.disabled = true;
          return false;
        }
        
        if (inputDigits > 15) {
          phoneInput.classList.add("error");
          phoneError.textContent = "Phone number must not exceed 15 digits.";
          phoneWrapper.parentNode.insertBefore(phoneError, phoneWrapper.nextSibling);
          if (submitButton) submitButton.disabled = true;
          return false;
        }
        
        if (!iti.isValidNumber()) {
          phoneInput.classList.add("error");
          phoneError.textContent = "Please enter a valid phone number.";
          phoneWrapper.parentNode.insertBefore(phoneError, phoneWrapper.nextSibling);
          if (submitButton) submitButton.disabled = true;
          return false;
        }

        if (submitButton) submitButton.disabled = false;
        return true;
      };
      
      const validateName = (nameInput) => {
        nameInput.classList.remove("error");
        if (nameError.parentNode) nameError.remove();

        if (!nameInput.value.trim()) {
          nameInput.classList.add("error");
          nameInput.parentNode.appendChild(nameError);
          if (submitButton) submitButton.disabled = true;
          return false;
        }
        if (submitButton) submitButton.disabled = false;
        return true;
      };

      const validateEmail = (emailInput) => {
        emailInput.classList.remove("error");
        if (emailError.parentNode) emailError.remove();

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const emailValue = emailInput.value.trim();
        if (!emailValue || !emailPattern.test(emailValue)) {
          emailInput.classList.add("error");
          emailInput.parentNode.appendChild(emailError);
          if (submitButton) submitButton.disabled = true;
          return false;
        }
        if (submitButton) submitButton.disabled = false;
        return true;
      };

      // Sanitize and validate live
      phoneInput.addEventListener("input", function () {
        phoneInput.value = phoneInput.value.replace(/[^\d]/g, '');
        validatePhone();
      });

      phoneInput.addEventListener("blur", validatePhone);

      form.addEventListener("submit", function (e) {
        const nameInput = form.querySelector("input[name='firstname']");
        const emailInput = form.querySelector("input[name='email']");

        let isValid = true;

        if (!validateName(nameInput)) isValid = false;
        if (!validateEmail(emailInput)) isValid = false;
        if (!validatePhone()) isValid = false;

        if (!isValid) {
          e.stopImmediatePropagation(); // ✅ Prevent HubSpot's handler too
          e.preventDefault();
          return false;
        }
      
        phoneInput.value = iti.getNumber(); // Format to E.164

      });
    }
  }, 300);
});
