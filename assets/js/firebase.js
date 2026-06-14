// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.1.0/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.1.0/firebase-analytics.js";
import { getDatabase, ref, push } from "https://www.gstatic.com/firebasejs/11.1.0/firebase-database.js";

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyDpYe-UFZwL0TzLxToOflI4115n0k7yU2c",
  authDomain: "codingcreators-9c318.firebaseapp.com",
  projectId: "codingcreators-9c318",
  storageBucket: "codingcreators-9c318.firebasestorage.app",
  messagingSenderId: "668175311877",
  appId: "1:668175311877:web:50d8e06ffa996f3c70686c",
  measurementId: "G-F5HCYB5VKN"
};

// Initialize Firebase 
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
const database = getDatabase(app);

// Form validation function
function validateForm() {
  let firstName = document.getElementById("firstname-field").value.trim();
  let lastName = document.getElementById("lastname-field").value.trim();
  let mobileNumber = document.getElementById("mobilenumber-field").value.trim();
  let email = document.getElementById("email-field").value.trim();
  let domain = document.getElementById("domain").value;
  let service = document.getElementById("service").value;
  let message = document.getElementById("message-field").value.trim();


  let nameRegex = /^[A-Za-z]{3,30}$/;
  let mobileRegex = /^[0-9]{10}$/;
  let emailRegex = /^[A-Za-z0-9]+@[A-Za-z]+\.[A-Za-z]{2,}$/; // Restricts special characters before @

  if (!nameRegex.test(firstName)) {
    alert("First name must be between 3 to 30 alphabetic characters only.");
    return false;
  }
  if (!nameRegex.test(lastName)) {
    alert("Last name must be between 3 to 30 alphabetic characters only.");
    return false;
  }
  if (!mobileRegex.test(mobileNumber)) {
    alert("Mobile number must be exactly 10 digits.");
    return false;
  }
  if (!emailRegex.test(email)) {
    alert("Email should not contain special characters like ., %, -, _ before '@'.");
    return false;
  }
  if (domain === "") {
    alert("Please select a preferred domain.");
    return false;
  }
  if (service === "") {
    alert("Please select a service.");
    return false;
  }
  if (message.length < 30 || message.length > 500) {
    alert("Required Services and Brief Overview must be between 30 to 500 characters.");
    return false;
  }
  return true; // Form is valid
}

// Form submission logic
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('contact-form');
  const popupForm = document.getElementById('popup-contact-form');

  const setupFormSubmission = (formObj, isPopup = false) => {
    if (!formObj) return;
    
    formObj.addEventListener('submit', async (e) => {
      e.preventDefault();

      // Validate the form before submission
      if (isPopup) {
        if (typeof window.validatePopupForm === 'function' && !window.validatePopupForm()) {
          return;
        }
      } else {
        if (typeof validateForm === 'function' && !validateForm()) {
          return; // Stop form submission if validation fails
        }
      }

      // Get form values
      const formData = {
        firstname: formObj.firstname.value.trim(),
        lastname: formObj.lastname.value.trim(),
        mobilenumber: formObj.mobilenumber.value.trim(),
        email: formObj.email.value.trim(),
        domain: formObj.domain.value,
        service: formObj.service.value,
        message: formObj.message.value.trim(),
        timestamp: new Date().toISOString(),
      };
      // Include 'project' only if IT and Non-IT Real-time Projects is selected
      if (formObj.service.value === "IT and Non-IT Real-time Projects") {
        formData.project = formObj.project.value;
      }

      if (formObj.service.value === "Classroom Training") {
        formData.classroom = formObj.classroom.value;
      }
      try {
        const loadingEl = isPopup ? formObj.querySelector('.popup-loading') : document.querySelector('.loading');
        const sentEl = isPopup ? formObj.querySelector('.popup-sent-message') : document.querySelector('.sent-message');
        const errorEl = isPopup ? formObj.querySelector('.popup-error-message') : document.querySelector('.error-message');
        
        if(loadingEl) loadingEl.style.display = 'block'; // Show loading message

        // Push data to Firebase
        const dbRef = ref(database, 'contact-page');
        await push(dbRef, formData);

        // Hide loading message and show success message
        if(loadingEl) loadingEl.style.display = 'none';
        if(sentEl) sentEl.style.display = 'block';
        if(errorEl) errorEl.style.display = 'none';
        formObj.reset();

        // Hide success message after 5 seconds
        setTimeout(() => {
          if(sentEl) sentEl.style.display = 'none';
          if(isPopup) {
            const modal = document.getElementById("autoContactModal");
            if(modal) modal.classList.remove("show");
          }
        }, 5000);
        
        // Hide project and classroom selection after form submission
        const projSelect = isPopup ? document.getElementById('popup-project-selection') : document.getElementById('project-selection');
        const classSelect = isPopup ? document.getElementById('popup-classroom-selection') : document.getElementById('classroom-selection');
        
        if(projSelect) projSelect.style.display = 'none';
        if(classSelect) classSelect.style.display = 'none';
      } catch (error) {
        console.error('Error submitting form:', error);

        const loadingEl = isPopup ? formObj.querySelector('.popup-loading') : document.querySelector('.loading');
        const errorEl = isPopup ? formObj.querySelector('.popup-error-message') : document.querySelector('.error-message');

        // Hide loading message and show error message
        if(loadingEl) loadingEl.style.display = 'none';
        if(errorEl) {
          errorEl.style.display = 'block';
          errorEl.textContent = 'Error submitting form. Please try again.';
          
          // Hide error message after 5 seconds
          setTimeout(() => {
            errorEl.style.display = 'none';
          }, 5000);
        }
      }
    });
  };

  setupFormSubmission(form, false);
  setupFormSubmission(popupForm, true);
});
