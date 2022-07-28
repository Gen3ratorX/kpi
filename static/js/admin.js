class AdminControl{
    constructor(){
        this.url = './utils.php';
        this.pageInit();
        // this.toggleRoleState();
        this.projectInit();
        this.roleInit();
        this.employeeInit();
        this.departmentInit();
        this.unitInit();
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
                    $(`#role-${roleId}`).parents('.col').hide('slow',function(){
                        $(this).remove();
                    });
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
            if(isValidated && (email ? emailIsValid : true)){
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
                const success = (res,statusCode,status) => {
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
                    $(`#employee-${employeeId}`).parents('.col').hide('slow',function(){
                        $(this).remove();
                    });
                }

                // Close modal
                $('#close-delete-employee').click();
                
            }
            const data = {task: 'deleteEmployee',employeeId}
            baseControl.fetchData(inst.url,data,'Yes','#delete-employee',false,success);
        });
    }

    // Department
    departmentInit(){
        this.saveDepartment();
        this.searchDepartmentHead();
        this.deleteDepartment();
    }

    selectDepartmentHead(){
        $('.department-head-options').click(function(){
            $('.department-head-options').each(function(){
                $(this).removeAttr('id');
            });
            $(this).attr('id','selected-department-head');
        });
    }

    searchDepartmentHead(){
        const inst = this;
        $('#search-department-head').click(function(){
            const q = $('#search-input').val();
            if(q){
                baseControl.startLoading('#search-department-head');
                const success = (res,statusCode,status) => {
                    // Display search results
                    $('#search-results').empty();
                    if(res?.employees?.length === 0){
                        $('#search-results').html(
                            `<div class="col-12">
                                <p class="text-center text-muted lead">No Employee Has Been Selected.</p>
                            </div>`
                        );
                    }
                    else{
                        for(let employee of res?.employees){
                            $('#search-results').append(
                                `<div class="col-auto">
                                    <div data-department-head-id='${employee?.id}' class="department-head-options">${employee?.name}</div>
                                </div>`
                            );
                        }
                        // Add event
                        inst.toggleEventState('.department-head-options',inst.selectDepartmentHead)
                    }
                }
                const data = {
                    q,
                    task: 'searchDepartmentHead'
                }

                baseControl.fetchData(inst.url,data,'Search','#search-department-head',true,success)
            }
            else{
                $('#search-results').html(
                    `<div class="col-12">
                        <p class="text-center text-muted lead">No Employee Has Been Selected.</p>
                    </div>`
                );
            }
        });        
    }

    saveDepartment(){
        const inst = this;
        $('#save-department').click(function(){
            const departmentEdit =  $(this).data('departmentEdit');
            const departmentId = $(this).data('departmentId');
            const isValid = baseControl.validateFields(['#department']);
            const departmentHeadId = $('#selected-department-head').data('departmentHeadId');
            if(isValid && departmentHeadId){
                const department = baseControl.capitalize($('#department').val());
                const data = {
                    department,departmentHeadId,departmentId,
                    task: departmentEdit ? 'editDepartment' : 'saveDepartment'
                }
                const success = (res,statusCodes,status) => {
                    window.location.assign('./departments.php');
                    // console.log(res);
                }
                baseControl.fetchData(inst.url,data,'Save Department','#save-department',false,success);
            }
            else if(isValid && !departmentHeadId){
                baseControl.showToast("You must search and select a department head");
            }
        });
    }

    deleteDepartment(){
        const inst = this;
        $('.delete-department-attempt').click(function(){
            const departmentId = $(this).attr('id').split('-')[1];
            $('#delete-department').data('id',departmentId);
        });

        $('#delete-department').click(function(){
            const departmentId = $(this).data('id');
            const success = (res,statusCode,status) => {
                if($('#departments').children().length - 1 == 0){
                    $('#departments').hide('slow',function(){
                        $(this).before(
                            `   <!-- No Item -->
                                <section class='no-item'>
                                    No Department Has Been added.
                                    <div>
                                        <a href='department_form.php' class='btn btn-1 btn-md'> Add Department </a>
                                    </div>
                                </section>
                            `
                        );
                        $(this).remove();
                    });
                }
                else{
                    $(`#department-${departmentId}`).parents('.col').hide('slow',function(){
                        $(this).remove();
                    });
                }

                // Close modal
                $('#close-delete-department').click();
                
            }
            const data = {task: 'deleteDepartment',departmentId}
            baseControl.fetchData(inst.url,data,'Yes','#delete-department',false,success);
        });
    }

    // Unit
    unitInit(){
        this.saveUnit();
        this.deleteUnit();
    }

    saveUnit(){
        const inst = this;
        $('#save-unit').click(function(){
            const unitEdit = $(this).data('unitEdit');
            const unitId = $(this).data('unitId');
            const isValid = baseControl.validateFields(['#unit']);
            if(isValid){
                const unit = baseControl.capitalize($('#unit').val());
                const department = $('#department').val();
                const data = {
                    department,unit,unitId,
                    task: unitEdit ? 'editUnit' : 'saveUnit',
                }
                const success = (res,statusCode,status) => {
                    // console.log(res);
                    window.location.assign('./units.php');
                }

                baseControl.fetchData(inst.url,data,'Save Unit','#save-unit',false,success);
            }
        });
    }

    deleteUnit(){
        const inst = this;
        $('.delete-unit-attempt').click(function(){
            const unitId = $(this).attr('id').split('-')[1];
            $('#delete-unit').data('id',unitId);
        });

        $('#delete-unit').click(function(){
            const unitId = $(this).data('id');
            const success = (res,statusCode,status) => {
                if($('#units').children().length - 1 == 0){
                    $('#units').hide('slow',function(){
                        $(this).before(
                            `   <!-- No Item -->
                                <section class='no-item'>
                                    No Unit Has Been added.
                                    <div>
                                        <a href='unit_form.php' class='btn btn-1 btn-md'> Add Unit </a>
                                    </div>
                                </section>
                            `
                        );
                        $(this).remove();
                    });
                }
                else{
                    $(`#unit-${unitId}`).parents('.col').hide('slow',function(){
                        $(this).remove();
                    });
                }

                // Close modal
                $('#close-delete-unit').click();
                
            }
            const data = {task: 'deleteUnit',unitId}
            baseControl.fetchData(inst.url,data,'Yes','#delete-unit',false,success);
        });
    }

}

const adminControl = new AdminControl();