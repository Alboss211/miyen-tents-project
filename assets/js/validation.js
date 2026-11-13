document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    
    if(form) {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        form.addEventListener('submit', function(e) {
            // Check if passwords match
            if (password.value !== confirmPassword.value) {
                e.preventDefault(); // Stop submission
                alert('Passwords do not match!');
                return;
            }

            // Check password length
            if (password.value.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return;
            }
        });
    }
});