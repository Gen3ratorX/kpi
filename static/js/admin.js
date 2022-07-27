class AdminControl{
    constructor(){
        this.url = './utils.php';
        this.pageInit();
        // this.toggleRoleState();
        this.projectInit();
        this.roleInit();
        this.employeeInit();
    }

    pageInit(){
        // const myModalAlternative = new bootstrap.Modal('#deleteFocusModal', {});
        // myModalAlternative.show();
    }

    toggleRoleState(){
        $('#save-or-edit').click(function(){
            const command = $(this).data('command');
            const value = $('#role-input').val();
            if(value){
                if(command === 'save'){
                    $(this).data('command','edit');
                    $('#role-input').attr('disabled',true);
                    $(this).text("Edit");
                }else{
                    $(this).data('command','save');
                    $('#role-input').attr('disabled',false);
                    $(this).text("Save");
                }
                $('#role-error').data('error',false);
                $('#role-error').text("");
            }
            else{
                $('#role-error').text("You need to put in a role.");
                $('#role-error').data('error',true);
            }
        });
    }

    toggleEventState(ele,action,event='click'){
        $(ele).off(event);
        action();
    }

    // Project
    projectInit(){
        this.saveProject();
        this.toggleProjectIsOpen();
        this.selectEmployee();
    }

    toggleProjectIsOpen(){
        $('#isOpen > .btn').click(function(){
            const value = $(this).text().toLowerCase().trim();
            $('#isOpen > .btn').attr('class','btn btn-4-outline btn-sm');
            $(this).attr('class','btn btn-4-solid btn-sm');
            $('#isOpen').data('value',value)
        });
    }

    selectEmployee(){
        $('.employee-item').click(function(){
            const name = $(this).text();
            $(this).toggleClass('selected-employee');
        });
    }

    saveProject(){
        let inst = this;
        $('#save-project').click(function(){
            const selectedEmployees = [];
            $('.selected-employee').each(function(){
                // selectedEmployees.push($(this).data('employeeId').trim());
                selectedEmployees.push($(this).text().trim());
            });
            const isValidated = baseControl.validateFields(['#projectName','#projectDeadline','#projectEmployees']);
            if(isValidated){
                const projectName = baseControl.capitalize($('#projectName').val());
                const deadline = $('#projectDeadline').val();
                const isOpen = $('#isOpen').data('value');
                $.post(
                    inst.url,
                    {
                        projectName,
                        deadline,
                        isOpen,
                        selectedEmployees,
                        task: 'saveProject'
                    },
                    function(res){
                        console.log(res);
                    }
                );
            }
        });
    }


    // Role
    roleInit(){
        this.saveRole();
        this.deleteRole();
    }

    saveRole(){
        const inst = this;
        $('#save-role').click(function(){
            const roleEdit = $(this).data('roleEdit');
            const roleId = $(this).data('roleId');
            const isValidated = baseControl.validateFields(['#role']);
            if(isValidated){
                const role = baseControl.capitalize($('#role').val());
                const data = {
                    role,
                    task: roleEdit ? 'editRole' : 'saveRole',
                    roleId,
                }
                const success = (res,statusCode,status) => window.location.assign('./roles.php');
                baseControl.fetchData(inst.url,data,'Save Role','#save-role',false,success);
            }
        });
    }


    deleteRole(){
        const inst = this;
        $('.delete-role-attempt').click(function(){
            const roleId = $(this).attr('id').split('-')[1];
            $('#delete-role').data('id',roleId);
        });

        $('#delete-role').click(function(){
            const roleId = $(this).data('id');
            const success = (res,statusCode,status) => {
                if($('#roles').children().length - 1 == 0){
                    $('#roles').hide('slow',function(){
                        $(this).before(
                            `<!-- No Item -->
                                <section class='no-item'>
                                    No Role Has Been added.
                                    <div>
                                        <a href='role_form.php' class='btn btn-1 btn-md'> Add Role </a>
                                    </div>
                                </section>`
                        );
                        $(this).remove();

                    });
                }
                else{
                    $(`#role-${roleId}`).parents('.col').hide('slow');
                }

                // Close modal
                $('#close-delete-role').click();
                
            }
            const data = {task: 'deleteRole',roleId}
            baseControl.fetchData(inst.url,data,'Yes','#delete-role',false,success);
        });
    }

    
    // Employee
    employeeInit(){
        this.saveEmployee();
        this.deleteEmployee();
    }

    saveEmployee(){
        const inst = this;
        $('#save-employee').click(function(){
            const employeeEdit = $(this).data('employeeEdit');
            const employeeId = $(this).data('employeeId');
            const isValidated = baseControl.validateFields(['#surname','#otherNames','#phone','#location']);
            const email = $('#email').val();
            const emailIsValid = baseControl.validateEmail('#email');
            if(isValidated & (email ? emailIsValid : true)){
                const surname = baseControl.capitalize($('#surname').val());
                const otherNames = baseControl.capitalize($('#otherNames').val());
                const phone = $('#phone').val();
                const location = baseControl.capitalize($('#location').val());
                const role = $('#role').val();
                const unit = $('#unit').val();
                const department = $('#department').val();
                const data = {
                    surname,otherNames,phone,location,role,unit,department,email,employeeId,
                    task: employeeEdit ? 'editEmployee' :'saveEmployee',
                }
                console.log(data);
                const success = (res,statusCode,status) => {
                    console.log(res);
                    window.location.assign('./employees.php')
                };
                baseControl.fetchData(inst.url,data,'Save Employee','#save-employee',false,success)
            }
        });
    }

    deleteEmployee(){
        const inst = this;
        $('.delete-employee-attempt').click(function(){
            const employeeId = $(this).attr('id').split('-')[1];
            $('#delete-employee').data('id',employeeId);
        });

        $('#delete-employee').click(function(){
            const employeeId = $(this).data('id');
            const success = (res,statusCode,status) => {
                if($('#employees').children().length - 1 == 0){
                    $('#employees').hide('slow',function(){
                        $(this).before(
                            `<!-- No Item -->
                                <section class='no-item'>
                                    No Employee Has Been added.
                                    <div>
                                        <a href='./employee_form.php' class='btn btn-1 btn-md'> Add Employee </a>
                                    </div>
                                </section>`
                        );
                        $(this).remove();

                    });
                }
                else{
                    $(`#employee-${roleId}`).parents('.col').hide('slow');
                }

                // Close modal
                $('#close-delete-employee').click();
                
            }
            const data = {task: 'deleteEmployee',employeeId}
            baseControl.fetchData(inst.url,data,'Yes','#delete-employee',false,success);
        });
    }


}

const adminControl = new AdminControl();