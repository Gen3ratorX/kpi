class AuthControl {
    constructor() {
        this.url = "auth.php";
        this.signIn();
    }

    signIn() {
        const inst = this;
        $('#sign-in').click(function(e) {
            e.preventDefault();
            
            const isValidated = baseControl.validateFields(['#username', '#password']);
            if (isValidated) {
                const username = $('#username').val().trim();
                const password = $('#password').val().trim();
                
                // auth.php answers {status: 'SUCCESS', employee: true|false}
                const success = (res) => {
                    if (res && res.status === 'SUCCESS') {
                        baseControl.showToast("Login successful!");
                        setTimeout(() => {
                            window.location.assign(res.employee ? 'employee/' : 'admin/');
                        }, 500);
                    } else {
                        baseControl.showToast("Login failed. Please try again.");
                    }
                };

                const error = (status, responseText) => {
                    if (status === 400) {
                        baseControl.showToast("Invalid username and/or password.");
                    } else if (status === 401) {
                        baseControl.showToast("Unauthorized access. Please contact administrator.");
                    } else if (status === 500) {
                        baseControl.showToast("Server error. Please try again later.");
                    } else {
                        baseControl.showToast("Login failed. Please try again.");
                    }
                };

                const data = {
                    username: username,
                    password: password,
                    task: 'signIn'
                };

                baseControl.fetchData(
                    inst.url,
                    data,
                    'Sign In',
                    '#sign-in',
                    false,
                    success,
                    error
                );
            } else {
                baseControl.showToast("Please fill in all required fields.");
            }
        });
    }
}

// Initialize
$(document).ready(function() {
    authControl = new AuthControl();
});