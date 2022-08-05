class AuthControl{
    constructor(){
        this.url = 'auth.php'
        this.signIn()
    }

    signIn(){
        const inst = this;
        $('#sign-in').click(function(){
            const isValidated = baseControl.validateFields(['#username','#password'])
            if(isValidated){
                const username = $('#username');
                const password = $('#password');
                const success = (res,statusCode,status) => {
                    // window.location.assign();
                    console.log(res);
                }
                const error = () => {
                    baseControl.showToast("Invalid username and/or password.");
                }
                const data = {
                    username,
                    password,
                    task: 'signIn'
                }
                // baseControl.fetchData(inst.url,data,'Sign In','#sign-in',false,success,error);
            }
        });
    }
}

authControl = new AuthControl();