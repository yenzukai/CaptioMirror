
const togglePasswordIcon = document.querySelector('#togglePasswordIcon');
const passwordInput = document.querySelector('#password');

togglePasswordIcon.addEventListener('click', function () {
    // Toggle the type attribute
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    // Toggle the icon (you can use a different icon for hide if you want)
    if (type === 'text') {
        togglePasswordIcon.src = '../assets/svg/eye-off-svgrepo-com.svg'; 
        togglePasswordIcon.alt = 'Hide Password';
    } else {
        togglePasswordIcon.src = '../assets/svg/eye-svgrepo-com.svg';
        togglePasswordIcon.alt = 'Show Password';
    }
});

