class AuthControl{
    constructor(){
        this.url = "auth.php"
        this.signIn()
    }

    signIn(){
        const inst = this;
        $('#sign-in').click(function(){
            const isValidated = baseControl.validateFields(['#username','#password'])
            if(isValidated){
                const username = $('#username').val();
                const password = $('#password').val();
                const success = (res,statusCode,status) => {
                    // window.location.assign();
                    console.log(res);
                    window.location.assign('employee/');
                }
                const error = (status) => {
                    status === 400 && baseControl.showToast("Invalid username and/or password.");
                }
                const data = {
                    username,
                    password,
                    task: 'signIn'
                }

                baseControl.fetchData(inst.url,data,'Sign In','#sign-in',false,success,error)
            }
        });
    }
}

authControl = new AuthControl();