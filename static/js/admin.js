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


    toggleEventState(ele,action,event='click'){
        $(ele).off(event);
        action();
    }

    // Project
    projectInit(){
        this.assignedEmployees = new Set();
        this.saveProject();
        this.toggleProjectIsOpen();
        this.assignEmployee();
        this.searchProjectEmployees();
        this.deleteProject();
    }

    toggleProjectIsOpen(){
        $('#isOpen > .btn').click(function(){
            const value = $(this).text().toLowerCase().trim();
            $('#isOpen > .btn').attr('class','btn btn-4-outline btn-sm');
            $(this).attr('class','btn btn-4-solid btn-sm');
            $('#isOpen').data('value',value.toLowerCase()  == 'yes' ? 1 : 0)
        });
    }

    assignEmployee(){
        const inst = this;
        $('.employee-item').click(function(){ 
            const employeeId = $(this).data('employeeId');
            const name = $(this).text().trim();
            
            const countAssignedEmployees = $('.assigned-employee').length;
            if(countAssignedEmployees == 0){
                $('#assigned-employees').empty();
            }
            if(!inst.assignedEmployees.has(`${employeeId}`)){
                // Update a set of employees
                inst.assignedEmployees.add(`${employeeId}`);
                $('#assigned-employees').append(
                    `<div class="assigned-employee">
                        <p>${name}</p>
                        <span data-employee-id='${employeeId}'>x</span>
                    </div>`
                );
            }
            baseControl?.toggleEventState('.assigned-employee > span',() => inst.removeAssignedEmployee());
        });
    }

    removeAssignedEmployee(){
        const inst = this;
        $('.assigned-employee > span').click(function(){
            const employeeId  = $(this).data('employeeId');
            inst.assignedEmployees.delete(`${employeeId}`);
            $(this).parents('.assigned-employee').hide(function(){
                const countAssignedEmployees = $('.assigned-employee').length;
                if(countAssignedEmployees - 1 == 0){
                    $('#assigned-employees').append(
                        `<p class='text-center text-muted lead'>No Employee Has Been Assigned.</p>`
                    );
                }
                $(this).remove();
            });
        });
    }

    searchProjectEmployees(){
        const inst = this;
        $('.search-project-employee').click(function(){
            const employeeRole = $(this).data('employeeRole');
            const q = $(this).parents('.search').find('.search-project-employee-input').val();
            const data = {
                employeeRole,q,
                task: 'searchProjectEmployees'
            };
            const success = (res,statusCode,status) => {
                // console.log(res);
                const employees = res.employees;
                if(employees.length == 0){
                    $(this).parents('.search').parent().next().html(
                        `<p class='text-center text-muted lead'>No Employee Found</p>`
                    );
                }
                else{
                    $(this).parents('.search').parent().next().empty();
                    for(let employee of employees){
                        $(this).parents('.search').parent().next().append(
                            `<p class='employee-item' data-employee-id='${employee.id}'>${employee.name}</p>`
                        );
                    }
                    // Add event
                    baseControl.toggleEventState('.employee-item',() => inst.assignEmployee())
                }
                
            }
            baseControl.fetchData(inst.url,data,'Search','.search-project-employee',true,success);
        });
    }

    saveProject(){
        let inst = this;
        $('#save-project').click(function(){
            const isValidated = baseControl.validateFields(['#projectName','#projectDeadline']);
            if(isValidated && inst.assignedEmployees.size > 0){
                const projectName = baseControl.capitalize($('#projectName').val());
                const deadline = $('#projectDeadline').val();
                const isOpen = $('#isOpen').data('value');
                const data = {
                    projectName,deadline,isOpen,
                    assignedEmployees: Array(...inst.assignedEmployees).join(','),
                    task: 'saveProject'
                }
                const success = (res,statusCode,status) => {
                    // console.log(res);
                    window.location.assign('./index.php')
                }
                baseControl.fetchData(inst.url,data,'Save Project','#save-project',false,success);
            }
            else if(inst.assignedEmployees.size == 0){
                baseControl.showToast("You haven't assigned any employee to the project.");
            }
        });
    }

    deleteProject(){
        const inst = this;
        $('.delete-project-attempt').click(function(){
            const projectId = $(this).attr('id').split('-')[1];
            $('#delete-project').data('id',projectId);
        });

        $('#delete-project').click(function(){
            const projectId = $(this).data('id');
            const success = (res,statusCode,status) => {
                console.log(res);
                if($('#projects').children().length - 1 == 0){
                    $('#projects').hide('slow',function(){
                        $(this).before(
                            `   <!-- No Item -->
                                <section class='no-item'>
                                    No Project Has Been added.
                                    <div>
                                        <a href='project_form.php' class='btn btn-1 btn-md'> Add Project </a>
                                    </div>
                                </section>`
                        );
                        $(this).remove();

                    });
                }
                else{
                    $(`#project-${projectId}`).parents('.card').hide('slow',function(){
                        $(this).remove();
                    });
                }

                // Close modal
                $('#close-delete-project').click();
                
            }
            const data = {task: 'deleteProject',projectId}
            baseControl.fetchData(inst.url,data,'Yes','#delete-project',false,success);
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
            const isValidated = baseControl.validateFields(['#role','#name']);
            if(isValidated){
                const name = baseControl.capitalize($('#name').val());
                const role = $('#role').val();
                const data = {
                    role,
                    name,
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
        this.getUnitsForDepartment();
    }

    saveEmployee(){
        const inst = this;
        $('#save-employee').click(function(){
            const employeeEdit = $(this).data('employeeEdit');
            const employeeId = $(this).data('employeeId');
            const isValidated = baseControl.validateFields(['#surname','#otherNames','#phone','#location','#username']);
            const email = $('#email').val();
            const emailIsValid = baseControl.validateEmail('#email');
            if(isValidated && emailIsValid){
                const surname = baseControl.capitalize($('#surname').val());
                const otherNames = baseControl.capitalize($('#otherNames').val());
                const phone = $('#phone').val();
                const username = $('#username').val();
                const location = baseControl.capitalize($('#location').val());
                const role = $('#role').val();
                const unit = $('#unit').val();
                const department = $('#department').val();
                const data = {
                    surname,otherNames,phone,location,role,unit,department,email,username,employeeId,
                    task: employeeEdit ? 'editEmployee' :'saveEmployee',
                }
                const success = (res,statusCode,status) => {
                    window.location.assign('./employees.php');
                };
                const error  = (status) => {
                    status === 400 && baseControl.showToast("It seems the username and/or email has been taken by another user.");
                }
                baseControl.fetchData(inst.url,data,'Save Employee','#save-employee',false,success,error)
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

    getUnitsForDepartment(){
        const inst = this;
        $('#department').change(function(){
            const departmentId = $(this).val();
            $('#unit').empty(); // Clear units options
            // Default option
            $('#unit').html(
                `<option value=''>__</option>`
            );
            const success = (res,statusCode,status) => {
                for(let unit of res.units){
                    $('#unit').append(
                        `<option value='${unit.id}'>${unit.name}</option>`
                    );
                }
            }

            const data = {task: 'getUnitsForDepartment',departmentId};
            baseControl.fetchData(inst.url,data,'','',true,success);
        });
    }

    // Department
    departmentInit(){
        this.saveDepartment();
        this.searchDepartmentHead();
        this.deleteDepartment();
        this.selectDepartmentHead();
    }

    selectDepartmentHead(){
        $('.department-head-options').click(function(){
            const isSelected = $(this).attr('id');
            $('.department-head-options').each(function(){
                $(this).removeAttr('id');
            });
            isSelected 
            ? $(this).removeAttr('id') 
            : $(this).attr('id','selected-department-head');
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
                        baseControl.toggleEventState('.department-head-options',inst.selectDepartmentHead)
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
            if(isValid){
                const departmentHeadId = $('#selected-department-head').data('departmentHeadId');
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