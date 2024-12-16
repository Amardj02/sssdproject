document.addEventListener('DOMContentLoaded', function () {
    // Show the signup form when clicked
    document.getElementById('show-signup').addEventListener('click', function () {
        document.getElementById('login-form').style.display = 'none';
        document.getElementById('signup-form').style.display = 'block';
        clearMessage();
    });

    // Show the login form when clicked
    document.getElementById('show-login').addEventListener('click', function () {
        document.getElementById('signup-form').style.display = 'none';
        document.getElementById('login-form').style.display = 'block';
        clearMessage();
    });

    // Handle login form submission
    document.getElementById('login-form').addEventListener('submit', async function (e) {
        e.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        const response = await fetch('http://localhost/sssdproject/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, password })
        });

        const rawResponse = await response.text();
        console.log("Raw response:", rawResponse);

        try {
            const data = JSON.parse(rawResponse.trim().replace(/^[^\{]*\{/, '{').replace(/\}[^\}]*$/, '}'));

            if (response.ok) {
                if (data.message) {
                    displayMessage(data.message, 'success');
                }

                if (data.status === 200) {
                    // Save the token in localStorage
                    localStorage.setItem('token', data.token);

                    if (data.message === "Scan the QR code and enter the OTP.") {
                        document.getElementById('qr-code').src = data.link;
                        document.getElementById('otp-container').style.display = 'block';
                    } else {
                        window.location.href = 'home.html';
                    }
                } else {
                    displayMessage(data.message || 'Login failed', 'error');
                }
            } else {
                displayMessage(data.message || 'Login failed', 'error');
            }
        } catch (error) {
            console.error("Error parsing JSON:", error);
            displayMessage('There was an error processing your request. Please try again later.', 'error');
        }
    });

    // Handle OTP form submission
    document.getElementById('submit-otp').addEventListener('click', async function () {
        const otpCode = document.getElementById('otp-code').value;
        const token = localStorage.getItem('token');

        if (!token) {
            displayMessage('No token found. Please log in again.', 'error');
            return;
        }

        const response = await fetch('http://localhost/sssdproject/api/entertwofactormethodcode', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ otp_code: otpCode })
        });

        const rawResponse = await response.text();
        console.log("Raw OTP response:", rawResponse);

        try {
            const data = JSON.parse(rawResponse.trim().replace(/^[^\{]*\{/, '{').replace(/\}[^\}]*$/, '}'));

            if (response.ok) {
                if (data.status === 200) {
                    displayMessage(data.message, 'success');
                    window.location.href = 'home.html';
                } else {
                    displayMessage(data.message || 'OTP verification failed', 'error');
                }
            } else {
                displayMessage(data.message || 'OTP verification failed', 'error');
            }
        } catch (error) {
            console.error("Error parsing OTP response:", error);
            displayMessage('There was an error processing your OTP. Please try again later.', 'error');
        }
    });

    // Handle sign-up form submission
    document.getElementById('signup-form').addEventListener('submit', async function (event) {
        event.preventDefault();

        const fullName = document.getElementById('full_name').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone_number').value;
        const username = document.getElementById('username-signup').value;
        const password = document.getElementById('password-signup').value;

        const response = await fetch('http://localhost/sssdproject/api/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ full_name: fullName, username, email, phone_number: phone, password })
        });

        const rawResponse = await response.text();
        console.log("Raw register response:", rawResponse);

        try {
            const result = JSON.parse(rawResponse.trim().replace(/^[^\{]*\{/, '{').replace(/\}[^\}]*$/, '}'));

            if (result.status === 200) {
                displayMessage('Registration successful! You can now log in.', 'success');
                document.getElementById('signup-form').style.display = 'none';
                document.getElementById('login-form').style.display = 'block';
                clearMessage();
            } else {
                displayMessage(result.message || 'Registration failed', 'error');
            }
        } catch (error) {
            console.error("Error parsing registration response:", error);
            displayMessage('There was an error processing your registration. Please try again later.', 'error');
        }
    });

    // Function to display message from backend
    function displayMessage(message, type) {
        const messageContainer = document.getElementById('message-container');
        messageContainer.innerHTML = `<p>${message}</p>`;
        
        // Set message styles based on success or error type
        if (type === 'success') {
            messageContainer.style.color = 'green';
            messageContainer.style.borderColor = '#4CAF50';
            messageContainer.style.backgroundColor = '#d4edda';
        } else {
            messageContainer.style.color = 'red';
            messageContainer.style.borderColor = '#f5c6cb';
            messageContainer.style.backgroundColor = '#f8d7da';
        }
        messageContainer.style.display = 'block';
    }

    // Clear the message container
    function clearMessage() {
        const messageContainer = document.getElementById('message-container');
        messageContainer.style.display = 'none';
    }

    // Toggle password visibility
    const passwordField = document.getElementById('password');
    const passwordFieldSignup = document.getElementById('password-signup');
    const passwordToggle = document.getElementById('password-toggle');
    const passwordToggleSignup = document.getElementById('password-toggle-signup');

    if (passwordField && passwordToggle) {
        passwordToggle.addEventListener('click', function () {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordToggle.textContent = 'Hide';
            } else {
                passwordField.type = 'password';
                passwordToggle.textContent = 'Show';
            }
        });
    }

    if (passwordFieldSignup && passwordToggleSignup) {
        passwordToggleSignup.addEventListener('click', function () {
            if (passwordFieldSignup.type === 'password') {
                passwordFieldSignup.type = 'text';
                passwordToggleSignup.textContent = 'Hide';
            } else {
                passwordFieldSignup.type = 'password';
                passwordToggleSignup.textContent = 'Show';
            }
        });
    }
});
